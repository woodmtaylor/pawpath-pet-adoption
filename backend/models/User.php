<?php
// backend/src/models/User.php

namespace PawPath\models;

use PDO;
use PDOException;
use PawPath\config\database\DatabaseConfig;

class User {
    private PDO $db;
    
    public function __construct() {
        try {
            $this->db = DatabaseConfig::getConnection();
            error_log("Database connection established in User model");
        } catch (PDOException $e) {
            error_log("Failed to connect to database in User model: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function create(array $data): int {
        try {
            error_log("Attempting to create user with data: " . print_r($data, true));
            
            $query = "
                INSERT INTO User (username, email, password_hash, registration_date)
                VALUES (?, ?, ?, CURDATE())
            ";
            
            error_log("Preparing query: " . $query);
            
            $stmt = $this->db->prepare($query);
            
            $params = [
                $data['username'],
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT)
            ];
            
            error_log("Executing query with params: " . print_r($params, true));
            
            $success = $stmt->execute($params);
            
            if (!$success) {
                error_log("Query execution failed. Error info: " . print_r($stmt->errorInfo(), true));
                throw new PDOException("Failed to create user");
            }
            
            $userId = (int) $this->db->lastInsertId();
            error_log("User created successfully with ID: " . $userId);
            
            return $userId;
        } catch (PDOException $e) {
            error_log("Error in create user: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
}
