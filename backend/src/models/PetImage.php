<?php
namespace PawPath\models;

use PDO;
use PDOException;
use PawPath\config\database\DatabaseConfig;

class PetImage {
    private PDO $db;
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
    }
    
    public function create(int $petId, string $imageUrl, bool $isPrimary = false): int {
        try {
            // If this is a primary image, unset any existing primary images
            if ($isPrimary) {
                $stmt = $this->db->prepare("
                    UPDATE Pet_Image 
                    SET is_primary = FALSE 
                    WHERE pet_id = ?
                ");
                $stmt->execute([$petId]);
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO Pet_Image (pet_id, image_url, is_primary)
                VALUES (?, ?, ?)
            ");
            
            $stmt->execute([$petId, $imageUrl, $isPrimary]);
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating pet image: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function findByPetId(int $petId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM Pet_Image 
                WHERE pet_id = ? 
                ORDER BY is_primary DESC, created_at ASC
            ");
            
            $stmt->execute([$petId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error finding pet images: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function setPrimary(int $imageId, int $petId): bool {
        try {
            $this->db->beginTransaction();
            
            // Unset existing primary image
            $stmt = $this->db->prepare("
                UPDATE Pet_Image 
                SET is_primary = FALSE 
                WHERE pet_id = ?
            ");
            $stmt->execute([$petId]);
            
            // Set new primary image
            $stmt = $this->db->prepare("
                UPDATE Pet_Image 
                SET is_primary = TRUE 
                WHERE image_id = ? AND pet_id = ?
            ");
            $result = $stmt->execute([$imageId, $petId]);
            
            $this->db->commit();
            return $result;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error setting primary image: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function delete(int $imageId, int $petId): bool {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM Pet_Image 
                WHERE image_id = ? AND pet_id = ?
            ");
            return $stmt->execute([$imageId, $petId]);
        } catch (PDOException $e) {
            error_log("Error deleting pet image: " . $e->getMessage());
            throw $e;
        }
    }
}
