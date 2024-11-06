<?php
// backend/src/models/Pet.php

namespace PawPath\Models;

use PDO;
use PawPath\Config\Database\DatabaseConfig;

class Pet {
    private PDO $db;
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
    }
    
    public function findAll(array $filters = []): array {
        $query = "SELECT * FROM Pet WHERE 1=1";
        $params = [];
        
        if (isset($filters['species'])) {
            $query .= " AND species = ?";
            $params[] = $filters['species'];
        }
        
        if (isset($filters['shelter_id'])) {
            $query .= " AND shelter_id = ?";
            $params[] = $filters['shelter_id'];
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM Pet WHERE pet_id = ?");
        $stmt->execute([$id]);
        
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }
    
    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO Pet (name, species, breed, age, gender, description, shelter_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['name'],
            $data['species'],
            $data['breed'] ?? null,
            $data['age'] ?? null,
            $data['gender'] ?? null,
            $data['description'] ?? null,
            $data['shelter_id']
        ]);
        
        return (int) $this->db->lastInsertId();
    }
    
    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, ['name', 'species', 'breed', 'age', 'gender', 'description', 'shelter_id'])) {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $query = "UPDATE Pet SET " . implode(', ', $fields) . " WHERE pet_id = ?";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }
    
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM Pet WHERE pet_id = ?");
        return $stmt->execute([$id]);
    }
}

<?php
