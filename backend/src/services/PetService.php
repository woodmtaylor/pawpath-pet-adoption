<?php
namespace PawPath\services;

use PDO;
use PDOException;
use PawPath\config\database\DatabaseConfig as DbConfig;  // Use an alias to avoid conflict

class PetService {
    private PDO $db;
    
    public function __construct() {
        $this->db = DbConfig::getConnection();  // Use the alias here
    }

    public function getPet(int $id): array {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, s.name as shelter_name
                FROM Pet p
                LEFT JOIN Shelter s ON p.shelter_id = s.shelter_id
                WHERE p.pet_id = ?
            ");
            
            $stmt->execute([$id]);
            $pet = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$pet) {
                throw new \RuntimeException("Pet not found");
            }
            
            // Get images
            $stmt = $this->db->prepare("
                SELECT image_id, image_url as url, is_primary
                FROM Pet_Image
                WHERE pet_id = ?
                ORDER BY is_primary DESC, image_id ASC
            ");
            $stmt->execute([$id]);
            $pet['images'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Pet data with images: " . print_r($pet, true));
            
            // Get traits
            $stmt = $this->db->prepare("
                SELECT t.trait_name, tc.name as category
                FROM Pet_Trait_Relation ptr
                JOIN Pet_Trait t ON ptr.trait_id = t.trait_id
                LEFT JOIN Trait_Category tc ON t.category_id = tc.category_id
                WHERE ptr.pet_id = ?
            ");
            $stmt->execute([$id]);
            $traits = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $formattedTraits = [];
            foreach ($traits as $trait) {
                $category = $trait['category'] ?? 'General';
                if (!isset($formattedTraits[$category])) {
                    $formattedTraits[$category] = [];
                }
                $formattedTraits[$category][] = $trait['trait_name'];
            }
            
            $pet['traits'] = $formattedTraits;
            
            return $pet;
        } catch (PDOException $e) {
            error_log("Database error finding pet: " . $e->getMessage());
            throw new \RuntimeException("Failed to fetch pet data");
        }
    }
    
    public function createPet(array $data): array {
        try {
            error_log("Creating pet with data: " . json_encode($data));
            
            // Validate required fields
            $requiredFields = ['name', 'species', 'shelter_id'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new RuntimeException("Missing required field: $field");
                }
            }
            
            // Create pet
            $petId = $this->petModel->create($data);
            error_log("Created pet with ID: $petId");
            
            $pet = $this->petModel->findById($petId);
            if (!$pet) {
                throw new RuntimeException("Failed to retrieve created pet");
            }
            
            return $pet;
        } catch (\Exception $e) {
            error_log("Error in PetService::createPet: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function updatePet(int $id, array $data): array {
        // Verify pet exists
        $pet = $this->petModel->findById($id);
        if (!$pet) {
            throw new RuntimeException("Pet not found");
        }
        
        // Validate species if provided
        if (isset($data['species'])) {
            $validSpecies = ['Dog', 'Cat', 'Bird', 'Rabbit', 'Other'];
            if (!in_array($data['species'], $validSpecies)) {
                throw new RuntimeException("Invalid species");
            }
        }
        
        // Validate age if provided
        if (isset($data['age']) && ($data['age'] < 0 || $data['age'] > 30)) {
            throw new RuntimeException("Invalid age");
        }
        
        // Validate gender if provided
        if (isset($data['gender'])) {
            $validGenders = ['Male', 'Female'];
            if (!in_array($data['gender'], $validGenders)) {
                throw new RuntimeException("Invalid gender");
            }
        }
        
        // Validate shelter if provided
        if (isset($data['shelter_id'])) {
            if (!$this->shelterModel->findById($data['shelter_id'])) {
                throw new RuntimeException("Invalid shelter ID");
            }
        }
        
        // Validate traits if provided
        if (isset($data['traits'])) {
            foreach ($data['traits'] as $traitId) {
                if (!$this->traitModel->findById($traitId)) {
                    throw new RuntimeException("Invalid trait ID: $traitId");
                }
            }
        }
        
        // Update pet
        $this->petModel->update($id, $data);
        return $this->petModel->findById($id);
    }

    private function formatTraits(array $traits): array {
        $formatted = [];
        foreach ($traits as $trait) {
            $category = $trait['category'] ?? 'General';
            if (!isset($formatted[$category])) {
                $formatted[$category] = [];
            }
            if (!in_array($trait['trait_name'], $formatted[$category])) {
                $formatted[$category][] = $trait['trait_name'];
            }
        }
        return $formatted;
    }
    
    public function deletePet(int $id): bool {
        // Verify pet exists
        $pet = $this->petModel->findById($id);
        if (!$pet) {
            throw new RuntimeException("Pet not found");
        }
        
        // Check if pet has any pending adoption applications
        // This would be implemented when we add the adoption system
        
        return $this->petModel->delete($id);
    }
    
    public function listPets(array $filters = []): array {
        try {
            // First check if updated_at column exists
            $columns = $this->db->query("SHOW COLUMNS FROM Pet")->fetchAll(PDO::FETCH_COLUMN);
            $hasUpdatedAt = in_array('updated_at', $columns);
            $hasCreatedAt = in_array('created_at', $columns);

            $query = "
                SELECT p.*, s.name as shelter_name,
                COUNT(*) OVER() as total_count
                FROM Pet p
                LEFT JOIN Shelter s ON p.shelter_id = s.shelter_id
                WHERE 1=1
            ";
            $params = [];
            
            // Add filter conditions...
            if (!empty($filters['search'])) {
                $query .= " AND (p.name LIKE ? OR p.breed LIKE ? OR p.description LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
            }

            // Add sorting based on available columns
            if (!empty($filters['sort'])) {
                switch($filters['sort']) {
                    case 'updated_at DESC':
                        $query .= $hasUpdatedAt 
                            ? " ORDER BY p.updated_at DESC" 
                            : ($hasCreatedAt 
                                ? " ORDER BY p.created_at DESC" 
                                : " ORDER BY p.pet_id DESC");
                        break;
                    case 'updated_at ASC':
                        $query .= $hasUpdatedAt 
                            ? " ORDER BY p.updated_at ASC" 
                            : ($hasCreatedAt 
                                ? " ORDER BY p.created_at ASC" 
                                : " ORDER BY p.pet_id ASC");
                        break;
                    case 'name ASC':
                        $query .= " ORDER BY p.name ASC";
                        break;
                    case 'name DESC':
                        $query .= " ORDER BY p.name DESC";
                        break;
                    default:
                        $query .= $hasUpdatedAt 
                            ? " ORDER BY p.updated_at DESC" 
                            : ($hasCreatedAt 
                                ? " ORDER BY p.created_at DESC" 
                                : " ORDER BY p.pet_id DESC");
                }
            } else {
                // Default sorting
                $query .= $hasUpdatedAt 
                    ? " ORDER BY p.updated_at DESC" 
                    : ($hasCreatedAt 
                        ? " ORDER BY p.created_at DESC" 
                        : " ORDER BY p.pet_id DESC");
            }
            
            // Add pagination
            if (isset($filters['limit'])) {
                $query .= " LIMIT ?";
                $params[] = (int)$filters['limit'];
                
                if (isset($filters['offset'])) {
                    $query .= " OFFSET ?";
                    $params[] = (int)$filters['offset'];
                }
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $total = $pets[0]['total_count'] ?? 0;
            
            // Get images and traits for each pet
            foreach ($pets as &$pet) {
                // Get images
                $stmt = $this->db->prepare("
                    SELECT image_id, image_url as url, is_primary
                    FROM Pet_Image
                    WHERE pet_id = ?
                    ORDER BY is_primary DESC, image_id ASC
                ");
                $stmt->execute([$pet['pet_id']]);
                $pet['images'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Get traits
                $stmt = $this->db->prepare("
                    SELECT t.trait_name, tc.name as category
                    FROM Pet_Trait_Relation ptr
                    JOIN Pet_Trait t ON ptr.trait_id = t.trait_id
                    LEFT JOIN Trait_Category tc ON t.category_id = tc.category_id
                    WHERE ptr.pet_id = ?
                ");
                $stmt->execute([$pet['pet_id']]);
                $traits = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Format traits
                $pet['traits'] = $this->formatTraits($traits);
                
                // Remove the count from individual pets
                unset($pet['total_count']);
            }
            
            return [
                'pets' => $pets,
                'total' => $total
            ];
        } catch (PDOException $e) {
            error_log("Error in listPets: " . $e->getMessage());
            throw new \RuntimeException("Failed to fetch pets");
        }
    }

    public function addTrait(string $traitName): array {
        // Validate trait name
        if (empty(trim($traitName))) {
            throw new RuntimeException("Trait name cannot be empty");
        }
        
        $traitId = $this->traitModel->create($traitName);
        return $this->traitModel->findById($traitId);
    }
    
    public function listTraits(): array {
        return $this->traitModel->findAll();
    }
}
