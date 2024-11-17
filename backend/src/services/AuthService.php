<?php
namespace PawPath\services;

use PDO;
use RuntimeException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PawPath\config\database\DatabaseConfig;

class AuthService {
    private PDO $db;
    private string $jwtSecret;
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
        $this->jwtSecret = $_ENV['JWT_SECRET'] ?? throw new RuntimeException('JWT_SECRET not set');
    }
    
    public function register(array $data): array {
        try {
            // Validate required fields
            if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
                throw new RuntimeException('Missing required fields');
            }

            // Check if email already exists
            $stmt = $this->db->prepare("
                SELECT user_id FROM User WHERE email = ?
            ");
            $stmt->execute([$data['email']]);
            if ($stmt->fetch()) {
                throw new RuntimeException('Email already registered');
            }

            // Check if username already exists
            $stmt = $this->db->prepare("
                SELECT user_id FROM User WHERE username = ?
            ");
            $stmt->execute([$data['username']]);
            if ($stmt->fetch()) {
                throw new RuntimeException('Username already taken');
            }

            // Create user
            $stmt = $this->db->prepare("
                INSERT INTO User (
                    username, 
                    email, 
                    password_hash, 
                    registration_date,
                    role,
                    account_status,
                    email_verification_token,
                    email_token_expires_at
                ) VALUES (?, ?, ?, CURRENT_TIMESTAMP, 'adopter', 'pending', ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))
            ");

            $verificationToken = bin2hex(random_bytes(32));
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

            $stmt->execute([
                $data['username'],
                $data['email'],
                $passwordHash,
                $verificationToken
            ]);

            $userId = (int) $this->db->lastInsertId();

            // Get the created user
            $user = $this->getUser($userId);
            if (!$user) {
                throw new RuntimeException('Failed to create user');
            }

            // Generate JWT token
            $token = $this->generateToken($userId);

            // Return user data and token
            return [
                'user' => $user,
                'token' => $token
            ];

        } catch (\PDOException $e) {
            error_log("Database error during registration: " . $e->getMessage());
            throw new RuntimeException('Registration failed: Database error');
        } catch (\Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            throw $e;
        }
    }

    public function login(array $data): array {
        try {
            if (empty($data['email']) || empty($data['password'])) {
                throw new RuntimeException('Email and password are required');
            }

            // Find user
            $stmt = $this->db->prepare("
                SELECT 
                    user_id, 
                    username, 
                    email, 
                    password_hash, 
                    role, 
                    account_status
                FROM User 
                WHERE email = ?
            ");
            
            $stmt->execute([$data['email']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                throw new RuntimeException('Invalid credentials');
            }

            if (!password_verify($data['password'], $user['password_hash'])) {
                throw new RuntimeException('Invalid credentials');
            }
            
            // Remove sensitive data
            unset($user['password_hash']);
            
            // Generate token
            $token = $this->generateToken($user['user_id']);
            
            // Update last login
            $this->updateLastLogin($user['user_id']);
            
            return [
                'token' => $token,
                'user' => $user
            ];
        } catch (\Exception $e) {
            error_log('Login error in service: ' . $e->getMessage());
            throw $e;
        }
    }
    
    public function getUser(int $userId): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    user_id,
                    username,
                    email,
                    role,
                    account_status,
                    registration_date,
                    last_login
                FROM User 
                WHERE user_id = ?
            ");
            
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Database error while fetching user: " . $e->getMessage());
            throw new RuntimeException('Failed to fetch user data');
        }
    }

    public function createEmailVerificationToken(int $userId): string {
        try {
            $token = bin2hex(random_bytes(32));
            $stmt = $this->db->prepare("
                UPDATE User 
                SET email_verification_token = ?,
                    email_token_expires_at = DATE_ADD(NOW(), INTERVAL 24 HOUR)
                WHERE user_id = ?
            ");
            
            $stmt->execute([$token, $userId]);
            return $token;
        } catch (\Exception $e) {
            error_log("Error creating verification token: " . $e->getMessage());
            throw new RuntimeException('Failed to create verification token');
        }
    }

    public function verifyEmailToken(string $token): bool {
        try {
            $stmt = $this->db->prepare("
                UPDATE User 
                SET email_verified_at = CURRENT_TIMESTAMP,
                    account_status = 'active',
                    email_verification_token = NULL
                WHERE email_verification_token = ?
                AND email_token_expires_at > CURRENT_TIMESTAMP
                AND email_verified_at IS NULL
            ");
            
            $stmt->execute([$token]);
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            error_log("Database error during email verification: " . $e->getMessage());
            throw new RuntimeException('Failed to verify email');
        }
    }

    public function generateToken(int $userId): string {
        $payload = [
            'user_id' => $userId,
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // 24 hours
        ];
        
        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }
    
    public function validateToken(string $token): ?array {
        try {
            return (array) JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
        } catch (\Exception $e) {
            error_log('Token validation error: ' . $e->getMessage());
            return null;
        }
    }

    public function resendVerificationEmail(int $userId): bool {
        try {
            // Check if user exists and needs verification
            $stmt = $this->db->prepare("
                SELECT email, username, email_verified_at 
                FROM User 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                throw new RuntimeException('User not found');
            }

            if ($user['email_verified_at']) {
                throw new RuntimeException('Email already verified');
            }

            // Create new verification token
            $token = $this->createEmailVerificationToken($userId);

            // Send verification email
            $emailService = new EmailService();
            return $emailService->sendVerificationEmail(
                $user['email'],
                $user['username'],
                $token
            );
        } catch (\Exception $e) {
            error_log("Error resending verification email: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function updateLastLogin(int $userId): void {
        try {
            $stmt = $this->db->prepare("
                UPDATE User 
                SET last_login = CURRENT_TIMESTAMP 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
        } catch (\PDOException $e) {
            error_log("Error updating last login: " . $e->getMessage());
            // Non-critical error, don't throw
        }
    }
}
