<?php
namespace PawPath\services;

use PDO;
use PDOException;
use PawPath\config\database\DatabaseConfig;

class ShelterService {
    private PDO $db;
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
    }
    
    public function listShelters(array $filters = []): array {
        try {
            $query = "SELECT * FROM Shelter WHERE 1=1";
            $params = [];
            
            if (!empty($filters['search'])) {
                $query .= " AND (name LIKE ? OR address LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if (isset($filters['is_no_kill'])) {
                $query .= " AND is_no_kill = ?";
                $params[] = $filters['is_no_kill'];
            }
            
            $query .= " ORDER BY name ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in listShelters: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function getShelter(int $id): ?array {
        try {
            error_log("Fetching shelter with ID: $id");
            
            $stmt = $this->db->prepare("
                SELECT 
                    s.*
                FROM Shelter s
                WHERE s.shelter_id = ?
            ");
            
            $stmt->execute([$id]);
            $shelter = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($shelter) {
                error_log("Found shelter: " . json_encode($shelter));
                // Convert is_no_kill to boolean
                $shelter['is_no_kill'] = (bool) $shelter['is_no_kill'];
                return $shelter;
            }
            
            error_log("No shelter found with ID: $id");
            return null;
            
        } catch (PDOException $e) {
            error_log("Database error in getShelter: " . $e->getMessage());
            throw new \RuntimeException("Failed to fetch shelter details: " . $e->getMessage());
        }
    }

    public function getTotalPets(int $shelterId): int {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) 
                FROM Pet 
                WHERE shelter_id = ?
            ");
            
            $stmt->execute([$shelterId]);
            return (int) $stmt->fetchColumn();
            
        } catch (PDOException $e) {
            error_log("Error getting total pets: " . $e->getMessage());
            return 0;
        }
    }

    public function getActiveApplications(int $shelterId): int {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(DISTINCT aa.application_id) 
                FROM Adoption_Application aa
                JOIN Pet p ON aa.pet_id = p.pet_id
                WHERE p.shelter_id = ? 
                AND aa.status IN ('pending', 'under_review')
            ");
            
            $stmt->execute([$shelterId]);
            return (int) $stmt->fetchColumn();
            
        } catch (PDOException $e) {
            error_log("Error getting active applications: " . $e->getMessage());
            return 0;
        }
    }

    public function createShelter(array $data): array {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO Shelter (
                    name, 
                    address, 
                    phone, 
                    email, 
                    is_no_kill
                ) VALUES (
                    :name,
                    :address,
                    :phone,
                    :email,
                    :is_no_kill
                )
            ");

            $stmt->execute([
                ':name' => $data['name'],
                ':address' => $data['address'],
                ':phone' => $data['phone'],
                ':email' => $data['email'],
                ':is_no_kill' => $data['is_no_kill'] ?? false
            ]);

            $shelterId = $this->db->lastInsertId();
            $this->db->commit();

            return $this->getShelter($shelterId);
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Database error in createShelter: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateShelter(int $id, array $data): bool {
        try {
            $this->db->beginTransaction();

            $fields = [];
            $params = [];
            
            foreach (['name', 'address', 'phone', 'email', 'is_no_kill'] as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }
            
            if (empty($fields)) {
                return false;
            }

            $params[] = $id;
            $query = "UPDATE Shelter SET " . implode(', ', $fields) . " WHERE shelter_id = ?";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute($params);

            $this->db->commit();
            return $result;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Database error in updateShelter: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteShelter(int $id): bool {
        try {
            $this->db->beginTransaction();

            // First check if there are any pets
            $petCount = $this->getTotalPets($id);
            if ($petCount > 0) {
                throw new \RuntimeException("Cannot delete shelter with existing pets");
            }

            $stmt = $this->db->prepare("DELETE FROM Shelter WHERE shelter_id = ?");
            $result = $stmt->execute([$id]);

            $this->db->commit();
            return $result;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Database error in deleteShelter: " . $e->getMessage());
            throw $e;
        }
    }
}
