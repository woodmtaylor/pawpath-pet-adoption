<?php
namespace PawPath\models;

use PDO;
use PDOException;
use PawPath\config\database\DatabaseConfig;

class AdoptionApplication {
    private PDO $db;
    
    public const STATUS_PENDING = 'pending';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_WITHDRAWN = 'withdrawn';
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
    }
    
    public function create(array $data): int {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO Adoption_Application (
                    user_id, pet_id, application_date, status,
                    status_history, reason, experience, living_situation,
                    has_other_pets, other_pets_details, daily_schedule,
                    veterinarian
                ) VALUES (
                    :user_id, :pet_id, :application_date, :status,
                    :status_history, :reason, :experience, :living_situation,
                    :has_other_pets, :other_pets_details, :daily_schedule,
                    :veterinarian
                )
            ");
            
            $stmt->execute([
                'user_id' => $data['user_id'],
                'pet_id' => $data['pet_id'],
                'application_date' => $data['application_date'],
                'status' => $data['status'],
                'status_history' => $data['status_history'],
                'reason' => $data['reason'] ?? null,
                'experience' => $data['experience'] ?? null,
                'living_situation' => $data['living_situation'] ?? null,
                'has_other_pets' => $data['has_other_pets'] ? 1 : 0,
                'other_pets_details' => $data['other_pets_details'] ?? null,
                'daily_schedule' => $data['daily_schedule'] ?? null,
                'veterinarian' => $data['veterinarian'] ?? null
            ]);
            
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating adoption application: " . $e->getMessage());
            throw $e;
        }
    }

    public function findById(int $id): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT aa.*, p.name as pet_name, p.species as pet_species,
                       p.breed as pet_breed, s.name as shelter_name
                FROM Adoption_Application aa
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
                    aa.application_id,
                    aa.pet_id,
                    aa.status,
                    aa.application_date,
                    p.name as pet_name,
                    p.species as pet_species,
                    p.breed as pet_breed,
                    s.name as shelter_name
                FROM Adoption_Application aa
                JOIN Pet p ON aa.pet_id = p.pet_id
                JOIN Shelter s ON p.shelter_id = s.shelter_id
                WHERE aa.user_id = ?
                ORDER BY aa.application_date DESC
            ");
            
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding user's applications: " . $e->getMessage());
            throw $e;
        }
    }

    public function findByShelter(int $shelterId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT aa.*, p.name as pet_name,
                       u.username as applicant_name
                FROM Adoption_Application aa
                JOIN Pet p ON aa.pet_id = p.pet_id
                JOIN User u ON aa.user_id = u.user_id
                WHERE p.shelter_id = ?
                ORDER BY aa.application_date DESC
            ");
            
            $stmt->execute([$shelterId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error finding shelter's applications: " . $e->getMessage());
            throw $e;
        }
    }

    public function findByPet(int $petId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT aa.*, u.username as applicant_name
                FROM Adoption_Application aa
                JOIN User u ON aa.user_id = u.user_id
                WHERE aa.pet_id = ?
                ORDER BY aa.application_date DESC
            ");
            
            $stmt->execute([$petId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error finding pet's applications: " . $e->getMessage());
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
            
            $stmt->execute([
                $userId, 
                $petId, 
                self::STATUS_WITHDRAWN, 
                self::STATUS_REJECTED
            ]);
            
            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error checking user's pet application: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateStatus(int $id, string $status): bool {
        try {
            $stmt = $this->db->prepare("
                UPDATE Adoption_Application
                SET status = ?,
                    last_updated = CURRENT_TIMESTAMP
                WHERE application_id = ?
            ");
            
            return $stmt->execute([$status, $id]);
        } catch (PDOException $e) {
            error_log("Error updating application status: " . $e->getMessage());
            throw $e;
        }
    }
}
