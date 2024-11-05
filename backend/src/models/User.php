<?php
// src/models/User.php

namespace PawPath\Models;

use PDO;
use PawPath\Config\Database\DatabaseConfig;

class User {
    private PDO $db;
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
    }
    
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT user_id, username, email, registration_date FROM User WHERE user_id = ?");
        $stmt->execute([$id]);
        
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }
    
    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM User WHERE email = ?");
        $stmt->execute([$email]);
        
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }
    
    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO User (username, email, password_hash, registration_date)
            VALUES (?, ?, ?, CURDATE())
        ");
        
        $stmt->execute([
            $data['username'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT)
        ]);
        
        return (int) $this->db->lastInsertId();
    }
    
    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [];
        
        if (isset($data['username'])) {
            $fields[] = "username = ?";
            $params[] = $data['username'];
        }
        
        if (isset($data['email'])) {
            $fields[] = "email = ?";
            $params[] = $data['email'];
        }
        
        if (isset($data['password'])) {
            $fields[] = "password_hash = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $query = "UPDATE User SET " . implode(', ', $fields) . " WHERE user_id = ?";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }
}
