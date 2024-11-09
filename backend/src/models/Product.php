<?php

namespace PawPath\models;

use PDOException;
use PDO;
use PawPath\config\database\DatabaseConfig;

class Product {
    private PDO $db;
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
    }
    
    public function create(array $data): int {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO Product (
                    name,
                    description,
                    price,
                    affiliate_link
                ) VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['name'],
                $data['description'] ?? null,
                $data['price'],
                $data['affiliate_link'] ?? null
            ]);
            
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating product: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function findById(int $id): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM Product WHERE product_id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Error finding product: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function findAll(array $filters = []): array {
        try {
            $query = "SELECT * FROM Product WHERE 1=1";
            $params = [];
            
            if (!empty($filters['search'])) {
                $query .= " AND (name LIKE ? OR description LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if (isset($filters['price_min'])) {
                $query .= " AND price >= ?";
                $params[] = $filters['price_min'];
            }
            
            if (isset($filters['price_max'])) {
                $query .= " AND price <= ?";
                $params[] = $filters['price_max'];
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error finding products: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function update(int $id, array $data): bool {
        try {
            $fields = [];
            $params = [];
            
            foreach (['name', 'description', 'price', 'affiliate_link'] as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $params[] = $id;
            $query = "UPDATE Product SET " . implode(', ', $fields) . " WHERE product_id = ?";
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error updating product: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function delete(int $id): bool {
        try {
            $this->db->beginTransaction();
            
            // Delete blog relations first
            $stmt = $this->db->prepare("DELETE FROM Blog_Product_Relation WHERE product_id = ?");
            $stmt->execute([$id]);
            
            // Delete product
            $stmt = $this->db->prepare("DELETE FROM Product WHERE product_id = ?");
            $result = $stmt->execute([$id]);
            
            $this->db->commit();
            return $result;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error deleting product: " . $e->getMessage());
            throw $e;
        }
    }
}
