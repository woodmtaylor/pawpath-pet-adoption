<?php
// backend/src/services/AuthService.php

namespace PawPath\services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PawPath\models\User;
use RuntimeException;

class AuthService {
    private User $userModel;
    private string $jwtSecret;
    
    public function __construct() {
        $this->userModel = new User();
        $this->jwtSecret = $_ENV['JWT_SECRET'] ?? throw new RuntimeException('JWT_SECRET not set');
    }
    
    public function register(array $data): array {
        // Validate input
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            throw new RuntimeException('Missing required fields');
        }
        
        // Check if email already exists
        if ($this->userModel->findByEmail($data['email'])) {
            throw new RuntimeException('Email already registered');
        }
        
        // Create user
        $userId = $this->userModel->create($data);
        
        // Get user data
        $user = $this->userModel->findById($userId);
        
        // Generate token
        return [
            'user' => $user,
            'token' => $this->generateToken($userId)
        ];
    }

    public function login(array $data): array {
        error_log('Login attempt for email: ' . ($data['email'] ?? 'not provided'));
        
        // Validate input
        if (empty($data['email']) || empty($data['password'])) {
            throw new RuntimeException('Email and password are required');
        }
        
        // Find user
        $user = $this->userModel->findByEmail($data['email']);
        
        if (!$user) {
            error_log('User not found for email: ' . $data['email']);
            throw new RuntimeException('Invalid credentials');
        }
        
        // Verify password
        if (!password_verify($data['password'], $user['password_hash'])) {
            error_log('Invalid password for user: ' . $data['email']);
            throw new RuntimeException('Invalid credentials');
        }
        
        // Remove password hash from response
        unset($user['password_hash']);
        
        error_log('Successful login for user: ' . $data['email']);
        
        // Return user data and token
        return [
            'user' => $user,
            'token' => $this->generateToken($user['user_id'])
        ];
    }
    
    private function generateToken(int $userId): string {
        $payload = [
            'user_id' => $userId,
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // 24 hours
        ];
        
        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    public function validateToken(string $token): ?array {
        try {
            error_log("Attempting to validate token");
            
            $decoded = JWT::decode(
                $token, 
                new Key($this->jwtSecret, 'HS256')
            );
            
            $payload = (array) $decoded;
            error_log("Token validated successfully for user_id: " . ($payload['user_id'] ?? 'unknown'));
            
            return $payload;
        } catch (\Exception $e) {
            error_log("Token validation failed: " . $e->getMessage());
            return null;
        }
    }
}
