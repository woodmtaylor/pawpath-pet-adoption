<?php
namespace PawPath\services;

use PDO;
use PDOException;
use PawPath\config\database\DatabaseConfig;

class AdoptionService {
    private PDO $db;
    
    public function __construct() {
        try {
            $this->db = DatabaseConfig::getConnection();
        } catch (\Exception $e) {
            error_log("Error initializing AdoptionService: " . $e->getMessage());
            throw $e;
        }
    }

    public function createApplication(array $data): array {
        try {
            $this->db->beginTransaction();
            
            $stmt = $this->db->prepare("
                INSERT INTO Adoption_Application (
                    user_id, 
                    pet_id, 
                    status,
                    reason,
                    experience,
                    living_situation,
                    has_other_pets,
                    other_pets_details,
                    daily_schedule,
                    veterinarian
                ) VALUES (
                    :user_id,
                    :pet_id,
                    'pending',
                    :reason,
                    :experience,
                    :living_situation,
                    :has_other_pets,
                    :other_pets_details,
                    :daily_schedule,
                    :veterinarian
                )
            ");

            $result = $stmt->execute([
                ':user_id' => $data['user_id'],
                ':pet_id' => $data['pet_id'],
                ':reason' => $data['reason'] ?? null,
                ':experience' => $data['experience'] ?? null,
                ':living_situation' => $data['living_situation'] ?? null,
                ':has_other_pets' => $data['has_other_pets'] ?? false,
                ':other_pets_details' => $data['other_pets_details'] ?? null,
                ':daily_schedule' => $data['daily_schedule'] ?? null,
                ':veterinarian' => $data['veterinarian'] ?? null
            ]);

            if (!$result) {
                throw new \Exception("Failed to create application");
            }

            $applicationId = $this->db->lastInsertId();
            $this->db->commit();

            return $this->getApplication($applicationId);
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Error in createApplication: " . $e->getMessage());
            throw $e;
        }
    }

    public function getApplication(int $applicationId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    aa.*,
                    p.name as pet_name,
                    p.species as pet_species,
                    p.breed as pet_breed,
                    s.name as shelter_name
                FROM Adoption_Application aa
                JOIN Pet p ON aa.pet_id = p.pet_id
                JOIN Shelter s ON p.shelter_id = s.shelter_id
                WHERE aa.application_id = ?
            ");
            
            $stmt->execute([$applicationId]);
            $application = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$application) {
                throw new \Exception("Application not found");
            }
            
            return $application;
        } catch (\Exception $e) {
            error_log("Error in getApplication: " . $e->getMessage());
            throw $e;
        }
    }

    public function getUserApplications(int $userId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    aa.*,
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
        } catch (\Exception $e) {
            error_log("Error in getUserApplications: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateApplicationStatus(int $applicationId, string $status): bool {
        try {
            $validStatuses = ['pending', 'under_review', 'approved', 'rejected', 'withdrawn'];
            if (!in_array($status, $validStatuses)) {
                throw new \Exception("Invalid status");
            }

            $stmt = $this->db->prepare("
                UPDATE Adoption_Application 
                SET status = :status,
                    last_updated = CURRENT_TIMESTAMP
                WHERE application_id = :application_id
            ");

            return $stmt->execute([
                ':status' => $status,
                ':application_id' => $applicationId
            ]);
        } catch (\Exception $e) {
            error_log("Error in updateApplicationStatus: " . $e->getMessage());
            throw $e;
        }
    }
}
