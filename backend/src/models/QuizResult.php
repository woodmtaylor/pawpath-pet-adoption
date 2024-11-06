<?php

namespace PawPath\Models;

class QuizResult {
    private PDO $db;
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
    }
    
    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO Quiz_Result (quiz_id, recommended_species, recommended_breed)
            VALUES (?, ?, ?)
        ");
        
        $stmt->execute([
            $data['quiz_id'],
            $data['recommended_species'] ?? null,
            $data['recommended_breed'] ?? null
        ]);
        
        return (int) $this->db->lastInsertId();
    }
    
    public function findByQuizId(int $quizId): ?array {
        $stmt = $this->db->prepare("
            SELECT * FROM Quiz_Result WHERE quiz_id = ?
        ");
        
        $stmt->execute([$quizId]);
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }
}
