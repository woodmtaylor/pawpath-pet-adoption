<?php
namespace PawPath\models;

use PDO;
use PDOException;
use PawPath\config\database\DatabaseConfig;

class UserProfile {
    private PDO $db;
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
    }
    
    public function create(array $data): int {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO UserProfile (
                    user_id, first_name, last_name, phone, 
                    address, city, state, zip_code,
                    housing_type, has_yard, other_pets, household_members
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['user_id'],
                $data['first_name'] ?? null,
                $data['last_name'] ?? null,
                $data['phone'] ?? null,
                $data['address'] ?? null,
                $data['city'] ?? null,
                $data['state'] ?? null,
                $data['zip_code'] ?? null,
                $data['housing_type'] ?? null,
                $data['has_yard'] ?? null,
                $data['other_pets'] ?? null,
                $data['household_members'] ?? null
            ]);
            
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating user profile: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function findByUserId(int $userId): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT up.*, u.email, u.username, u.role, u.account_status
                FROM UserProfile up
                JOIN User u ON up.user_id = u.user_id
                WHERE up.user_id = ?
            ");
            
            $stmt->execute([$userId]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Error finding user profile: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function update(int $userId, array $data): bool {
        try {
            $fields = [];
            $params = [];
            
            foreach ($data as $key => $value) {
                if (in_array($key, [
                    'first_name', 'last_name', 'phone', 'address', 'city',
                    'state', 'zip_code', 'housing_type', 'has_yard',
                    'other_pets', 'household_members'
                ])) {
                    $fields[] = "$key = ?";
                    $params[] = $value;
                }
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $params[] = $userId;
            $query = "UPDATE UserProfile SET " . implode(', ', $fields) . " WHERE user_id = ?";
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error updating user profile: " . $e->getMessage());
            throw $e;
        }
    }
}
