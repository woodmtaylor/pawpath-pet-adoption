<?php
// backend/src/models/StartingQuiz.php

namespace PawPath\Models;

use PDO;
use PawPath\Config\Database\DatabaseConfig;

class StartingQuiz {
    private PDO $db;
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
    }
    
    public function create(int $userId): int {
        $stmt = $this->db->prepare("
            INSERT INTO Starting_Quiz (user_id, quiz_date)
            VALUES (?, CURDATE())
        ");
        
        $stmt->execute([$userId]);
        return (int) $this->db->lastInsertId();
    }
    
    public function findByUser(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT sq.*, qr.recommended_species, qr.recommended_breed
            FROM Starting_Quiz sq
            LEFT JOIN Quiz_Result qr ON sq.quiz_id = qr.quiz_id
            WHERE sq.user_id = ?
            ORDER BY sq.quiz_date DESC
        ");
        
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
