<?php
// backend/src/models/AdoptionApplication.php

namespace PawPath\models;

use PDO;
use PDOException;
use PawPath\config\database\DatabaseConfig;

class AdoptionApplication {
    private PDO $db;
    
    const STATUS_PENDING = 'pending';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_WITHDRAWN = 'withdrawn';
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
    }
    
    public function create(array $data): int {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO Adoption_Application (
                    user_id,
                    pet_id,
                    application_date,
                    status
                ) VALUES (?, ?, CURDATE(), ?)
            ");
            
            $stmt->execute([
                $data['user_id'],
                $data['pet_id'],
                self::STATUS_PENDING
            ]);
            
            $applicationId = (int) $this->db->lastInsertId();
            error_log("Created adoption application with ID: " . $applicationId);
            
            return $applicationId;
        } catch (PDOException $e) {
            error_log("Error creating adoption application: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function findById(int $id): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    aa.*,
                    u.username as user_name,
                    p.name as pet_name,
                    p.species as pet_species,
                    p.breed as pet_breed,
                    s.name as shelter_name
                FROM Adoption_Application aa
                JOIN User u ON aa.user_id = u.user_id
                JOIN Pet p ON aa.pet_id = p.pet_id
                JOIN Shelter s ON p.shelter_id = s.shelter_id
                WHERE aa.application_id = ?
            ");
            
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error finding adoption application: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function findByUser(int $userId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    aa.*,
                    u.username as user_name,
                    p.name as pet_name,
                    p.species as pet_species,
                    p.breed as pet_breed,
                    s.name as shelter_name
                FROM Adoption_Application aa
                JOIN User u ON aa.user_id = u.user_id
                JOIN Pet p ON aa.pet_id = p.pet_id
                JOIN Shelter s ON p.shelter_id = s.shelter_id
                WHERE aa.user_id = ?
                ORDER BY aa.application_date DESC
            ");
            
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error finding user's adoption applications: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function findByShelter(int $shelterId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    aa.*,
                    u.username as user_name,
                    p.name as pet_name,
                    p.species as pet_species,
                    p.breed as pet_breed,
                    s.name as shelter_name
                FROM Adoption_Application aa
                JOIN User u ON aa.user_id = u.user_id
                JOIN Pet p ON aa.pet_id = p.pet_id
                JOIN Shelter s ON p.shelter_id = s.shelter_id
                WHERE p.shelter_id = ?
                ORDER BY aa.application_date DESC
            ");
            
            $stmt->execute([$shelterId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error finding shelter's adoption applications: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function updateStatus(int $id, string $status): bool {
        try {
            $stmt = $this->db->prepare("
                UPDATE Adoption_Application
                SET status = ?
                WHERE application_id = ?
            ");
            
            return $stmt->execute([$status, $id]);
        } catch (PDOException $e) {
            error_log("Error updating adoption application status: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function findByPet(int $petId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    aa.*,
                    u.username as user_name,
                    p.name as pet_name,
                    p.species as pet_species,
                    p.breed as pet_breed,
                    s.name as shelter_name
                FROM Adoption_Application aa
                JOIN User u ON aa.user_id = u.user_id
                JOIN Pet p ON aa.pet_id = p.pet_id
                JOIN Shelter s ON p.shelter_id = s.shelter_id
                WHERE aa.pet_id = ?
                ORDER BY aa.application_date DESC
            ");
            
            $stmt->execute([$petId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error finding pet's adoption applications: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function hasUserAppliedForPet(int $userId, int $petId): bool {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM Adoption_Application
                WHERE user_id = ? AND pet_id = ?
                AND status NOT IN (?, ?)
            ");
            
            $stmt->execute([$userId, $petId, self::STATUS_WITHDRAWN, self::STATUS_REJECTED]);
            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error checking user's pet application: " . $e->getMessage());
            throw $e;
        }
    }
}
