<?php
// backend/src/models/PetTrait.php

namespace PawPath\models;

use PDO;
use PDOException;
use PawPath\config\database\DatabaseConfig;

class PetTrait {
    private PDO $db;
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
    }
    
    public function create(string $traitName): int {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO Pet_Trait (trait_name)
                VALUES (?)
            ");
            
            $stmt->execute([$traitName]);
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating pet trait: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function findAll(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT trait_id, trait_name
                FROM Pet_Trait
                ORDER BY trait_name
            ");
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error finding pet traits: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function findById(int $id): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT trait_id, trait_name
                FROM Pet_Trait
                WHERE trait_id = ?
            ");
            
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error finding pet trait: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function update(int $id, string $traitName): bool {
        try {
            $stmt = $this->db->prepare("
                UPDATE Pet_Trait
                SET trait_name = ?
                WHERE trait_id = ?
            ");
            
            return $stmt->execute([$traitName, $id]);
        } catch (PDOException $e) {
            error_log("Error updating pet trait: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function delete(int $id): bool {
        try {
            // Check if trait is in use
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM Pet_Trait_Relation
                WHERE trait_id = ?
            ");
            $stmt->execute([$id]);
            
            if ($stmt->fetchColumn() > 0) {
                throw new \RuntimeException("Cannot delete trait that is in use");
            }
            
            $stmt = $this->db->prepare("
                DELETE FROM Pet_Trait
                WHERE trait_id = ?
            ");
            
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error deleting pet trait: " . $e->getMessage());
            throw $e;
        }
    }
}
