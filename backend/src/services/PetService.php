<?php
// backend/src/services/PetService.php

namespace PawPath\services;

use PawPath\models\Pet;
use PawPath\models\PetTrait;
use PawPath\models\Shelter;
use RuntimeException;
use PawPath\config\database\DatabaseConfig;

class PetService {
    private $db;
    private Pet $petModel;
    private PetTrait $traitModel;
    private Shelter $shelterModel;
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
        $this->petModel = new Pet();
        $this->traitModel = new PetTrait();
        $this->shelterModel = new Shelter();
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
    
    public function getPet(int $id): array {
        $pet = $this->petModel->findById($id);
        if (!$pet) {
            throw new RuntimeException("Pet not found");
        }
        return $pet;
    }
    
    public function listPets(array $filters = []): array {
        try {
            // Debug logging
            error_log("PetService::listPets called with filters: " . print_r($filters, true));
            
            // Get total count without pagination
            $countQuery = "SELECT COUNT(DISTINCT p.pet_id) FROM Pet p";
            $whereConditions = [];
            $params = [];
            
            // Build basic query
            $query = "SELECT DISTINCT p.*, s.name as shelter_name 
                     FROM Pet p
                     LEFT JOIN Shelter s ON p.shelter_id = s.shelter_id";
            
            // Add search conditions if present
            if (!empty($filters['search'])) {
                $whereConditions[] = "(p.name LIKE ? OR p.breed LIKE ? OR p.description LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
            }
            
            // Add species filter if present
            if (!empty($filters['species'])) {
                $whereConditions[] = "p.species = ?";
                $params[] = $filters['species'];
            }
            
            // Add WHERE clause if conditions exist
            if (!empty($whereConditions)) {
                $whereClause = " WHERE " . implode(" AND ", $whereConditions);
                $countQuery .= $whereClause;
                $query .= $whereClause;
            }
            
            // Add ORDER BY
            $query .= " ORDER BY p.pet_id DESC";
            
            // Add LIMIT and OFFSET
            $limit = (int)($filters['limit'] ?? 12);
            $offset = (int)($filters['offset'] ?? 0);
            $query .= " LIMIT ? OFFSET ?";
            
            // Clone params for count query
            $countParams = $params;
            
            // Add limit and offset to params for main query
            $params[] = $limit;
            $params[] = $offset;
            
            // Get total count
            $stmt = $this->db->prepare($countQuery);
            $stmt->execute($countParams);
            $total = (int)$stmt->fetchColumn();
            
            // Debug logging
            error_log("Query: " . $query);
            error_log("Params: " . print_r($params, true));
            
            // Get paginated results
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $pets = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Fetch traits for each pet
            foreach ($pets as &$pet) {
                $traitQuery = "
                    SELECT t.trait_name, tc.name as category
                    FROM Pet_Trait_Relation ptr
                    JOIN Pet_Trait t ON ptr.trait_id = t.trait_id
                    LEFT JOIN Trait_Category tc ON t.category_id = tc.category_id
                    WHERE ptr.pet_id = ?
                ";
                
                $stmt = $this->db->prepare($traitQuery);
                $stmt->execute([$pet['pet_id']]);
                $traits = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                $pet['traits'] = $this->formatTraits($traits);
            }
            
            // Debug logging
            error_log("Found " . count($pets) . " pets");
            
            return [
                'pets' => $pets,
                'total' => $total
            ];
            
        } catch (\Exception $e) {
            error_log("Error in PetService::listPets: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            throw new \RuntimeException("Failed to fetch pets: " . $e->getMessage());
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
