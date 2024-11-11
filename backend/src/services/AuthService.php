<?php
namespace PawPath\services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PawPath\models\User;
use RuntimeException;
use PDO;
use PDOException;
use PawPath\config\database\DatabaseConfig;

class AuthService {
    private User $userModel;
    private string $jwtSecret;
    private PDO $db;
    
    public function __construct() {
        $this->userModel = new User();
        $this->db = DatabaseConfig::getConnection();
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
        
        // Create verification token
        $token = $this->createEmailVerificationToken($userId);
        
        // TODO: Send verification email once email service is configured
        // $this->emailService->sendVerificationEmail($data['email'], $data['username'], $token);
        
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
        
        // Update last login
        $this->updateLastLogin($user['user_id']);
        
        // Remove password hash from response
        unset($user['password_hash']);
        
        error_log('Successful login for user: ' . $data['email']);
        
        // Return user data and token
        return [
            'user' => $user,
            'token' => $this->generateToken($user['user_id'])
        ];
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
    
    public function getUser(int $userId): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT user_id, username, email, role, account_status, 
                       email_verified_at, registration_date
                FROM User 
                WHERE user_id = ?
            ");
            
            $stmt->execute([$userId]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Error finding user: " . $e->getMessage());
            throw $e;
        }
    }

    public function createEmailVerificationToken(int $userId): string {
        try {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            $stmt = $this->db->prepare("
                UPDATE User 
                SET email_verification_token = ?,
                    email_token_expires_at = ?
                WHERE user_id = ?
            ");
            
            $stmt->execute([$token, $expires, $userId]);
            return $token;
        } catch (\Exception $e) {
            error_log("Error creating email verification token: " . $e->getMessage());
            throw $e;
        }
    }

    public function verifyEmailToken(string $token): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT user_id 
                FROM User 
                WHERE email_verification_token = ? 
                AND email_token_expires_at > NOW()
                AND email_verified_at IS NULL
            ");
            
            $stmt->execute([$token]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return null;
            }
            
            // Update user verification status
            $stmt = $this->db->prepare("
                UPDATE User 
                SET email_verified_at = NOW(),
                    email_verification_token = NULL,
                    account_status = 'active'
                WHERE user_id = ?
            ");
            
            $stmt->execute([$user['user_id']]);
            
            return $this->getUser($user['user_id']);
        } catch (PDOException $e) {
            error_log("Error in verifyEmailToken: " . $e->getMessage());
            throw $e;
        }
    }

    private function generateToken(int $userId): string {
        $payload = [
            'user_id' => $userId,
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // 24 hours
        ];
        
        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }
    
    private function updateLastLogin(int $userId): void {
        try {
            $stmt = $this->db->prepare("
                UPDATE User 
                SET last_login = NOW() 
                WHERE user_id = ?
            ");
            
            $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Error updating last login: " . $e->getMessage());
            // Non-critical error, don't throw
        }
    }
}
