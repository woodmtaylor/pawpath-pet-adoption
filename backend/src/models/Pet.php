<?php
// backend/src/models/Pet.php

namespace PawPath\models;

use PDO;
use PDOException;
use PawPath\config\database\DatabaseConfig;

class Pet {
    private PDO $db;
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
    }
    
    public function create(array $data): int {
        try {
            $this->db->beginTransaction();
            
            error_log("Creating new pet: " . $data['name']);
            
            $stmt = $this->db->prepare("
                INSERT INTO Pet (name, species, breed, age, gender, description, shelter_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['name'],
                $data['species'],
                $data['breed'] ?? null,
                $data['age'] ?? null,
                $data['gender'] ?? null,
                $data['description'] ?? null,
                $data['shelter_id']
            ]);
            
            $petId = (int) $this->db->lastInsertId();
            
            // Add traits if provided
            if (!empty($data['traits']) && is_array($data['traits'])) {
                foreach ($data['traits'] as $traitId) {
                    $stmt = $this->db->prepare("
                        INSERT INTO Pet_Trait_Relation (pet_id, trait_id)
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$petId, $traitId]);
                }
            }
            
            $this->db->commit();
            error_log("Created pet with ID: " . $petId);
            
            return $petId;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error creating pet: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function findById(int $id): ?array {
        try {
            // Get pet basic information
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
            
            // Get pet traits
            $stmt = $this->db->prepare("
                SELECT t.trait_id, t.trait_name
                FROM Pet_Trait_Relation ptr
                JOIN Pet_Trait t ON ptr.trait_id = t.trait_id
                WHERE ptr.pet_id = ?
            ");
            
            $stmt->execute([$id]);
            $pet['traits'] = $stmt->fetchAll();
            
            return $pet;
        } catch (PDOException $e) {
            error_log("Error finding pet: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function findAll(array $filters = []): array {
        try {
            $query = "
                SELECT DISTINCT p.*, s.name as shelter_name
                FROM Pet p
                LEFT JOIN Shelter s ON p.shelter_id = s.shelter_id
                LEFT JOIN Pet_Trait_Relation ptr ON p.pet_id = ptr.pet_id
                LEFT JOIN Pet_Trait t ON ptr.trait_id = t.trait_id
                WHERE 1=1
            ";
            
            $params = [];
            
            // Add filters
            if (!empty($filters['species'])) {
                $query .= " AND p.species = ?";
                $params[] = $filters['species'];
            }
            
            if (!empty($filters['breed'])) {
                $query .= " AND p.breed LIKE ?";
                $params[] = '%' . $filters['breed'] . '%';
            }
            
            if (isset($filters['shelter_id'])) {
                $query .= " AND p.shelter_id = ?";
                $params[] = $filters['shelter_id'];
            }
            
            if (!empty($filters['traits']) && is_array($filters['traits'])) {
                $placeholders = str_repeat('?,', count($filters['traits']) - 1) . '?';
                $query .= " AND t.trait_id IN ($placeholders)";
                $params = array_merge($params, $filters['traits']);
            }
            
            if (isset($filters['age_min'])) {
                $query .= " AND p.age >= ?";
                $params[] = $filters['age_min'];
            }
            
            if (isset($filters['age_max'])) {
                $query .= " AND p.age <= ?";
                $params[] = $filters['age_max'];
            }
            
            if (!empty($filters['gender'])) {
                $query .= " AND p.gender = ?";
                $params[] = $filters['gender'];
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $pets = $stmt->fetchAll();
            
            // Get traits for each pet
            foreach ($pets as &$pet) {
                $stmt = $this->db->prepare("
                    SELECT t.trait_id, t.trait_name
                    FROM Pet_Trait_Relation ptr
                    JOIN Pet_Trait t ON ptr.trait_id = t.trait_id
                    WHERE ptr.pet_id = ?
                ");
                $stmt->execute([$pet['pet_id']]);
                $pet['traits'] = $stmt->fetchAll();
            }
            
            return $pets;
        } catch (PDOException $e) {
            error_log("Error finding pets: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function update(int $id, array $data): bool {
        try {
            $this->db->beginTransaction();
            
            $fields = [];
            $params = [];
            
            // Build update fields dynamically
            foreach (['name', 'species', 'breed', 'age', 'gender', 'description', 'shelter_id'] as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = ?";
                    $params[] = $data[$field];
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
                foreach ($data['traits'] as $traitId) {
                    $stmt = $this->db->prepare("
                        INSERT INTO Pet_Trait_Relation (pet_id, trait_id)
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$id, $traitId]);
                }
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
}
