<?php
// backend/src/models/Shelter.php

namespace PawPath\models;

use PDO;
use PDOException;
use PawPath\config\database\DatabaseConfig;

class Shelter {
    private PDO $db;
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
    }
    
    public function create(array $data): int {
        try {
            error_log("Creating new shelter: " . $data['name']);
            
            $stmt = $this->db->prepare("
                INSERT INTO Shelter (name, address, phone, email, is_no_kill)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $success = $stmt->execute([
                $data['name'],
                $data['address'],
                $data['phone'],
                $data['email'],
                $data['is_no_kill'] ?? false
            ]);
            
            if (!$success) {
                error_log("Failed to create shelter: " . print_r($stmt->errorInfo(), true));
                throw new PDOException("Failed to create shelter");
            }
            
            $shelterId = (int) $this->db->lastInsertId();
            error_log("Created shelter with ID: " . $shelterId);
            
            return $shelterId;
        } catch (PDOException $e) {
            error_log("Error creating shelter: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function findById(int $id): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT shelter_id, name, address, phone, email, is_no_kill
                FROM Shelter
                WHERE shelter_id = ?
            ");
            
            $stmt->execute([$id]);
            $shelter = $stmt->fetch();
            
            return $shelter ?: null;
        } catch (PDOException $e) {
            error_log("Error finding shelter: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function findAll(array $filters = []): array {
        try {
            $query = "SELECT shelter_id, name, address, phone, email, is_no_kill FROM Shelter WHERE 1=1";
            $params = [];
            
            // Add filter for no-kill shelters
            if (isset($filters['is_no_kill'])) {
                $query .= " AND is_no_kill = ?";
                $params[] = $filters['is_no_kill'];
            }
            
            // Add search by name
            if (!empty($filters['search'])) {
                $query .= " AND name LIKE ?";
                $params[] = '%' . $filters['search'] . '%';
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error finding shelters: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function update(int $id, array $data): bool {
        try {
            $fields = [];
            $params = [];
            
            // Build update fields dynamically
            foreach (['name', 'address', 'phone', 'email', 'is_no_kill'] as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $params[] = $id;
            $query = "UPDATE Shelter SET " . implode(', ', $fields) . " WHERE shelter_id = ?";
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error updating shelter: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function delete(int $id): bool {
        try {
            // First check if there are any pets associated with this shelter
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM Pet WHERE shelter_id = ?");
            $stmt->execute([$id]);
            $petCount = $stmt->fetchColumn();
            
            if ($petCount > 0) {
                throw new \RuntimeException("Cannot delete shelter with existing pets");
            }
            
            $stmt = $this->db->prepare("DELETE FROM Shelter WHERE shelter_id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error deleting shelter: " . $e->getMessage());
            throw $e;
        }
    }
}
