<?php
// src/models/StartingQuiz.php
namespace PawPath\models;

use PDO;
use PDOException;
use PawPath\config\database\DatabaseConfig;

class StartingQuiz {
    private PDO $db;
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
    }
    
    public function create(int $userId): int {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO Starting_Quiz (user_id, quiz_date)
                VALUES (?, CURRENT_TIMESTAMP)
            ");
            
            $stmt->execute([$userId]);
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating quiz: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function findByUser(int $userId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT sq.*, qr.recommended_species, qr.recommended_breed,
                       qr.trait_preferences
                FROM Starting_Quiz sq
                LEFT JOIN Quiz_Result qr ON sq.quiz_id = qr.quiz_id
                WHERE sq.user_id = ?
                ORDER BY sq.quiz_date DESC
            ");
            
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error finding user quizzes: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function findById(int $quizId): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT sq.*, qr.recommended_species, qr.recommended_breed,
                       qr.trait_preferences
                FROM Starting_Quiz sq
                LEFT JOIN Quiz_Result qr ON sq.quiz_id = qr.quiz_id
                WHERE sq.quiz_id = ?
            ");
            
            $stmt->execute([$quizId]);
            $result = $stmt->fetch();
            
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error finding quiz: " . $e->getMessage());
            throw $e;
        }
    }
}

// src/models/QuizResult.php
class QuizResult {
    private PDO $db;
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
    }
    
    public function create(array $data): int {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO Quiz_Result (
                    quiz_id, 
                    recommended_species, 
                    recommended_breed,
                    trait_preferences,
                    confidence_score
                ) VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['quiz_id'],
                $data['recommended_species'],
                $data['recommended_breed'],
                json_encode($data['trait_preferences'] ?? []),
                $data['confidence_score'] ?? 0
            ]);
            
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating quiz result: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function findByQuizId(int $quizId): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM Quiz_Result WHERE quiz_id = ?
            ");
            
            $stmt->execute([$quizId]);
            $result = $stmt->fetch();
            
            if ($result && isset($result['trait_preferences'])) {
                $result['trait_preferences'] = json_decode($result['trait_preferences'], true);
            }
            
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error finding quiz result: " . $e->getMessage());
            throw $e;
        }
    }
}
