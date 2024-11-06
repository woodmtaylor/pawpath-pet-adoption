<?php
// backend/src/models/User.php

namespace PawPath\models;

use PDO;
use PDOException;
use PawPath\config\database\DatabaseConfig;

class User {
    private PDO $db;
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
    }
    
    public function create(array $data): int {
        try {
            error_log("Attempting to create user with username: " . $data['username']);
            
            $stmt = $this->db->prepare("
                INSERT INTO User (username, email, password_hash, registration_date)
                VALUES (?, ?, ?, CURDATE())
            ");
            
            $success = $stmt->execute([
                $data['username'],
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT)
            ]);
            
            if (!$success) {
                error_log("Failed to execute user creation query");
                error_log(print_r($stmt->errorInfo(), true));
                throw new PDOException("Failed to create user");
            }
            
            $userId = (int) $this->db->lastInsertId();
            error_log("Successfully created user with ID: " . $userId);
            
            return $userId;
        } catch (PDOException $e) {
            error_log("Error creating user: " . $e->getMessage());
            throw $e;
        }
    }

    public function findById(int $id): ?array {
        try {
            error_log("Looking up user with ID: " . $id);
            
            $stmt = $this->db->prepare("
                SELECT user_id, username, email, registration_date 
                FROM User 
                WHERE user_id = ?
            ");
            
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            
            if ($user === false) {
                error_log("No user found with ID: " . $id);
                return null;
            }
            
            error_log("Found user: " . print_r($user, true));
            return $user;
        } catch (PDOException $e) {
            error_log("Error finding user: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function findByEmail(string $email): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT user_id, username, email, password_hash, registration_date 
                FROM User 
                WHERE email = ?
            ");
            
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            return $user === false ? null : $user;
        } catch (PDOException $e) {
            error_log("Error finding user by email: " . $e->getMessage());
            throw $e;
        }
    }
}
