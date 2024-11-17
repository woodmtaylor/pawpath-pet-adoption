<?php
namespace PawPath\services;

use PDO;
use RuntimeException;
use PawPath\config\database\DatabaseConfig;

class FavoriteService {
    private PDO $db;
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
    }
    
    public function addFavorite(int $userId, int $petId): array {
        try {
            // Check if favorite already exists
            $stmt = $this->db->prepare("
                SELECT favorite_id FROM Pet_Favorite
                WHERE user_id = ? AND pet_id = ?
            ");
            $stmt->execute([$userId, $petId]);
            
            if ($stmt->fetch()) {
                throw new RuntimeException('Pet is already favorited');
            }
            
            // Add favorite
            $stmt = $this->db->prepare("
                INSERT INTO Pet_Favorite (user_id, pet_id)
                VALUES (?, ?)
            ");
            
            $stmt->execute([$userId, $petId]);
            
            return [
                'favorite_id' => $this->db->lastInsertId(),
                'user_id' => $userId,
                'pet_id' => $petId
            ];
        } catch (\PDOException $e) {
            throw new RuntimeException('Failed to add favorite: ' . $e->getMessage());
        }
    }
    
    public function removeFavorite(int $userId, int $petId): bool {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM Pet_Favorite
                WHERE user_id = ? AND pet_id = ?
            ");
            
            $stmt->execute([$userId, $petId]);
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            throw new RuntimeException('Failed to remove favorite: ' . $e->getMessage());
        }
    }
    
    public function getUserFavorites(int $userId): array {
        try {
            // First get the basic pet information
            $stmt = $this->db->prepare("
                SELECT DISTINCT 
                    p.*,
                    s.name as shelter_name,
                    pf.created_at as favorited_at
                FROM Pet_Favorite pf
                JOIN Pet p ON pf.pet_id = p.pet_id
                JOIN Shelter s ON p.shelter_id = s.shelter_id
                WHERE pf.user_id = ?
                ORDER BY pf.created_at DESC
            ");
            
            $stmt->execute([$userId]);
            $pets = $stmt->fetchAll();
            
            // For each pet, get its traits
            foreach ($pets as &$pet) {
                $stmt = $this->db->prepare("
                    SELECT t.trait_name, tc.name as category
                    FROM Pet_Trait_Relation ptr
                    JOIN Pet_Trait t ON ptr.trait_id = t.trait_id
                    LEFT JOIN Trait_Category tc ON t.category_id = tc.category_id
                    WHERE ptr.pet_id = ?
                ");
                
                $stmt->execute([$pet['pet_id']]);
                $traits = $stmt->fetchAll();
                
                // Format traits by category
                $formattedTraits = [];
                foreach ($traits as $trait) {
                    $category = $trait['category'] ?? 'General';
                    if (!isset($formattedTraits[$category])) {
                        $formattedTraits[$category] = [];
                    }
                    $formattedTraits[$category][] = $trait['trait_name'];
                }
                
                $pet['traits'] = $formattedTraits;
            }
            
            return $pets;
        } catch (\PDOException $e) {
            throw new RuntimeException('Failed to get favorites: ' . $e->getMessage());
        }
    }
    
    public function isFavorited(int $userId, int $petId): bool {
        try {
            $stmt = $this->db->prepare("
                SELECT 1 FROM Pet_Favorite
                WHERE user_id = ? AND pet_id = ?
            ");
            
            $stmt->execute([$userId, $petId]);
            return (bool) $stmt->fetch();
        } catch (\PDOException $e) {
            throw new RuntimeException('Failed to check favorite status: ' . $e->getMessage());
        }
    }
}
