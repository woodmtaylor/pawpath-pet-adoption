<?php
namespace PawPath\models;

use PDO;
use PDOException;
use PawPath\config\database\DatabaseConfig;

class Pet {
    private PDO $db;
    private static array $validSpecies = ['Dog', 'Cat', 'Bird', 'Rabbit', 'Other'];

    private function formatTraits(array $traits): array {
        $formatted = [];
        foreach ($traits as $trait) {
            $category = $trait['category'] ?? 'General';
            if (!isset($formatted[$category])) {
                $formatted[$category] = [];
            }
            $formatted[$category][] = $trait['trait_name'];
        }
        return $formatted;
    }

    private function validatePetData(array $data): void {
        $requiredFields = ['name', 'species', 'shelter_id'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Missing required field: $field");
            }
        }
        
        if (!in_array(ucfirst(strtolower($data['species'])), self::$validSpecies)) {
            throw new \InvalidArgumentException("Invalid species. Must be one of: " . implode(', ', self::$validSpecies));
        }
    }
 
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
    }
    
    public function findAll(array $filters = []): array {
        try {
            $query = "
                SELECT p.*, s.name as shelter_name 
                FROM Pet p
                LEFT JOIN Shelter s ON p.shelter_id = s.shelter_id
                WHERE 1=1
            ";
            $params = [];
            
            if (isset($filters['species'])) {
                $query .= " AND p.species = ?";
                $params[] = $filters['species'];
            }
            
            if (isset($filters['shelter_id'])) {
                $query .= " AND p.shelter_id = ?";
                $params[] = $filters['shelter_id'];
            }

            if (isset($filters['breed'])) {
                $query .= " AND p.breed LIKE ?";
                $params[] = '%' . $filters['breed'] . '%';
            }

            if (isset($filters['age_min'])) {
                $query .= " AND p.age >= ?";
                $params[] = $filters['age_min'];
            }

            if (isset($filters['age_max'])) {
                $query .= " AND p.age <= ?";
                $params[] = $filters['age_max'];
            }

            if (isset($filters['gender'])) {
                $query .= " AND p.gender = ?";
                $params[] = $filters['gender'];
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $pets = $stmt->fetchAll();
        
        foreach ($pets as &$pet) {
            $stmt = $this->db->prepare("
                SELECT t.trait_id, t.trait_name, tc.name as category
                FROM Pet_Trait_Relation ptr
                JOIN Pet_Trait t ON ptr.trait_id = t.trait_id
                LEFT JOIN Trait_Category tc ON t.category_id = tc.category_id
                WHERE ptr.pet_id = ?
            ");
            $stmt->execute([$pet['pet_id']]);
            $traits = $stmt->fetchAll();
            $pet['traits'] = $this->formatTraits($traits);
            }
            
            return $pets;
        } catch (PDOException $e) {
            error_log("Error finding pets: " . $e->getMessage());
            throw $e;
        }
    }

    public function findById(int $id): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, s.name as shelter_name
                FROM Pet p
                LEFT JOIN Shelter s ON p.shelter_id = s.shelter_id
                WHERE p.pet_id = ?
            ");
            
            $stmt->execute([$id]);
            $pet = $stmt->fetch();
            
            if (!$pet) {
                return null;
            }
            
            // Get pet traits with categories
            $stmt = $this->db->prepare("
                SELECT t.trait_id, t.trait_name, tc.name as category
                FROM Pet_Trait_Relation ptr
                JOIN Pet_Trait t ON ptr.trait_id = t.trait_id
                LEFT JOIN Trait_Category tc ON t.category_id = tc.category_id
                WHERE ptr.pet_id = ?
            ");
            $stmt->execute([$id]);
            $traits = $stmt->fetchAll();
            
            $pet['traits'] = $this->formatTraits($traits);
            
            return $pet;
        } catch (PDOException $e) {
            error_log("Error finding pet by ID: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function create(array $data): int {
        try {
            $this->db->beginTransaction();
            
            error_log("Starting pet creation with data: " . print_r($data, true));
            
            // Validate required fields
            $this->validatePetData($data);
            
            $stmt = $this->db->prepare("
                INSERT INTO Pet (name, species, breed, age, gender, description, shelter_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $data['name'],
                $data['species'],
                $data['breed'] ?? null,
                $data['age'] ?? null,
                $data['gender'] ?? null,
                $data['description'] ?? null,
                $data['shelter_id']
            ]);
            
            if (!$result) {
                error_log("SQL Error: " . print_r($stmt->errorInfo(), true));
                throw new \PDOException("Failed to insert pet");
            }
            
            $petId = (int) $this->db->lastInsertId();
            error_log("Created pet with ID: $petId");
            
            // Add traits if provided
            if (!empty($data['traits']) && is_array($data['traits'])) {
                $this->addTraitsToPet($petId, $data['traits']);
            }
            
            $this->db->commit();
            return $petId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Error in Pet::create: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            throw $e;
        }
    }
    
    public function update(int $id, array $data): bool {
        try {
            $this->db->beginTransaction();
            
            $fields = [];
            $params = [];
            
            foreach ($data as $key => $value) {
                if (in_array($key, ['name', 'species', 'breed', 'age', 'gender', 'description', 'shelter_id'])) {
                    $fields[] = "$key = ?";
                    $params[] = $value;
                }
            }
            
            if (!empty($fields)) {
                $params[] = $id;
                $query = "UPDATE Pet SET " . implode(', ', $fields) . " WHERE pet_id = ?";
                
                $stmt = $this->db->prepare($query);
                $stmt->execute($params);
            }
            
            // Update traits if provided
            if (isset($data['traits']) && is_array($data['traits'])) {
                // Remove existing traits
                $stmt = $this->db->prepare("DELETE FROM Pet_Trait_Relation WHERE pet_id = ?");
                $stmt->execute([$id]);
                
                // Add new traits
                $this->addTraitsToPet($id, $data['traits']);
            }
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error updating pet: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function delete(int $id): bool {
        try {
            $this->db->beginTransaction();
            
            // Delete trait relations first
            $stmt = $this->db->prepare("DELETE FROM Pet_Trait_Relation WHERE pet_id = ?");
            $stmt->execute([$id]);
            
            // Delete pet
            $stmt = $this->db->prepare("DELETE FROM Pet WHERE pet_id = ?");
            $result = $stmt->execute([$id]);
            
            $this->db->commit();
            return $result;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error deleting pet: " . $e->getMessage());
            throw $e;
        }
    }

    public function findAllWithTraits(array $filters = []): array {
        try {
            error_log("Finding pets with filters: " . json_encode($filters, JSON_PRETTY_PRINT));
            
            // First, let's verify what traits exist in the database
            $stmt = $this->db->query("
                SELECT t.trait_id, t.trait_name, tc.name as category 
                FROM Pet_Trait t 
                LEFT JOIN Trait_Category tc ON t.category_id = tc.category_id
            ");
            error_log("Available traits: " . json_encode($stmt->fetchAll(), JSON_PRETTY_PRINT));
            
            // Then verify what pets and their traits exist
            $stmt = $this->db->query("
                SELECT p.pet_id, p.name, t.trait_name 
                FROM Pet p 
                LEFT JOIN Pet_Trait_Relation ptr ON p.pet_id = ptr.pet_id 
                LEFT JOIN Pet_Trait t ON ptr.trait_id = t.trait_id
            ");
            error_log("Existing pets and traits: " . json_encode($stmt->fetchAll(), JSON_PRETTY_PRINT));
            
            $query = "
                SELECT 
                    p.*,
                    s.name as shelter_name,
                    GROUP_CONCAT(DISTINCT t.trait_name) as trait_names,
                    COUNT(DISTINCT CASE 
                        WHEN t.trait_name IN (" . $this->buildTraitNameList($filters) . ") 
                        THEN t.trait_id 
                    END) as matching_trait_count
                FROM Pet p
                LEFT JOIN Shelter s ON p.shelter_id = s.shelter_id
                LEFT JOIN Pet_Trait_Relation ptr ON p.pet_id = ptr.pet_id
                LEFT JOIN Pet_Trait t ON ptr.trait_id = t.trait_id
                WHERE 1=1
            ";
            
            $params = [];
            
            if (!empty($filters['species'])) {
                $query .= " AND p.species = ?";
                $params[] = $filters['species'];
            }
            
            $query .= " GROUP BY p.pet_id";
            
            if (!empty($filters['traits'])) {
                $query .= " HAVING matching_trait_count > 0";
            }
            
            $query .= " ORDER BY matching_trait_count DESC, p.name";
            
            error_log("Executing query: " . $query);
            error_log("With params: " . json_encode($params));
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $pets = $stmt->fetchAll();
            
            error_log("Found pets: " . json_encode($pets, JSON_PRETTY_PRINT));
            
            foreach ($pets as &$pet) {
                $stmt = $this->db->prepare("
                    SELECT t.trait_name, tc.name as category
                    FROM Pet_Trait_Relation ptr
                    JOIN Pet_Trait t ON ptr.trait_id = t.trait_id
                    LEFT JOIN Trait_Category tc ON t.category_id = tc.category_id
                    WHERE ptr.pet_id = ?
                ");
                $stmt->execute([$pet['pet_id']]);
                $traits = $stmt->fetchAll();
                
                $pet['traits'] = [];
                foreach ($traits as $trait) {
                    $category = $trait['category'] ?? 'Uncategorized';
                    if (!isset($pet['traits'][$category])) {
                        $pet['traits'][$category] = [];
                    }
                    $pet['traits'][$category][] = $trait['trait_name'];
                }
            }
            
            return $pets;
        } catch (PDOException $e) {
            error_log("Error finding pets with traits: " . $e->getMessage());
            throw $e;
        }
    }

    private function addTraitsToPet(int $petId, array $traitIds): void {
        // First verify all traits exist
        $placeholders = str_repeat('?,', count($traitIds) - 1) . '?';
        $stmt = $this->db->prepare("
            SELECT trait_id FROM Pet_Trait 
            WHERE trait_id IN ($placeholders)
        ");
        $stmt->execute($traitIds);
        $validTraits = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        error_log("Valid traits found for pet $petId: " . implode(', ', $validTraits));
        
        if (count($validTraits) !== count($traitIds)) {
            throw new \InvalidArgumentException("Some trait IDs are invalid");
        }
        
        // Insert valid traits
        $stmt = $this->db->prepare("
            INSERT INTO Pet_Trait_Relation (pet_id, trait_id)
            VALUES (?, ?)
        ");
        
        foreach ($validTraits as $traitId) {
            $stmt->execute([$petId, $traitId]);
            error_log("Added trait $traitId to pet $petId");
        }
    }

    private function buildTraitNameList(array $filters): string {
        if (empty($filters['traits'])) {
            return "''";
        }
        
        $traitNames = array_map(function($trait) {
            return $this->db->quote($trait['trait']);
        }, $filters['traits']);
        
        // Add debugging
        error_log("Building trait list from: " . json_encode($filters['traits']));
        error_log("Generated trait list: " . implode(',', $traitNames));
        
        return implode(',', $traitNames);
    }

    private function processTraits(?string $traitsJson): array {
        if (empty($traitsJson)) {
            return [];
        }
        
        $formatted = [];
        $traits = array_filter(explode('},{', trim($traitsJson, '[]')));
        
        foreach ($traits as $trait) {
            if (!str_ends_with($trait, '}')) $trait .= '}';
            if (!str_starts_with($trait, '{')) $trait = '{' . $trait;
            
            $traitData = json_decode($trait, true);
            if ($traitData) {
                $category = $traitData['category'];
                if (!isset($formatted[$category])) {
                    $formatted[$category] = [];
                }
                $formatted[$category][] = $traitData['name'];
            }
        }
        
        return $formatted;
    }
}
