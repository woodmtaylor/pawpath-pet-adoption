<?php
// backend/src/services/AuthService.php

namespace PawPath\services;

use Firebase\JWT\JWT;
use PawPath\models\User;
use RuntimeException;

class AuthService {
    private User $userModel;
    
    public function __construct() {
        file_put_contents('php://stderr', "Initializing AuthService\n");
        $this->userModel = new User();
    }
    
    public function register(array $data): array {
        file_put_contents('php://stderr', "Starting registration process in AuthService\n");
        
        // Validate input
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            file_put_contents('php://stderr', "Missing required fields\n");
            throw new RuntimeException('Missing required fields');
        }
        
        try {
            file_put_contents('php://stderr', "Creating user in database\n");
            
            // Create user
            $userId = $this->userModel->create($data);
            
            file_put_contents('php://stderr', "User created with ID: " . $userId . "\n");
            
            // Get user data
            $user = $this->userModel->findById($userId);
            
            file_put_contents('php://stderr', "Retrieved user data: " . print_r($user, true) . "\n");
            
            // Generate token
            $token = $this->generateToken($userId);
            
            return [
                'user' => $user,
                'token' => $token
            ];
        } catch (\Exception $e) {
            file_put_contents('php://stderr', "Error in registration: " . $e->getMessage() . "\n");
            throw $e;
        }
    }
    
    private function generateToken(int $userId): string {
        $jwtSecret = $_ENV['JWT_SECRET'] ?? throw new RuntimeException('JWT_SECRET not set');
        
        $payload = [
            'user_id' => $userId,
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // 24 hours
        ];
        
        return JWT::encode($payload, $jwtSecret, 'HS256');
    }
}
