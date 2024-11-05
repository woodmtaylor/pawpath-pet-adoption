<?php
// src/services/AuthService.php

namespace PawPath\Services;

use Firebase\JWT\JWT;
use PawPath\Models\User;
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
        
        // Generate token
        return [
            'user' => $this->userModel->findById($userId),
            'token' => $this->generateToken($userId)
        ];
    }
    
    public function login(string $email, string $password): array {
        // Find user
        $user = $this->userModel->findByEmail($email);
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new RuntimeException('Invalid credentials');
        }
        
        // Remove password hash from response
        unset($user['password_hash']);
        
        // Generate token
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
            $decoded = JWT::decode($token, $this->jwtSecret, ['HS256']);
            return (array) $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }
}
