<?php
// backend/src/api/AuthController.php

namespace PawPath\api;

use PawPath\services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PawPath\utils\ResponseHelper;
use PawPath\services\EmailService;

class AuthController {
    private AuthService $authService;
    
    public function __construct() {
        $this->authService = new AuthService();
    }
    
    public function register(Request $request, Response $response): Response {
        try {
            $data = $request->getParsedBody();
            error_log('Registration attempt: ' . print_r($data, true));
            
            if (empty($data['email']) || empty($data['password']) || empty($data['username'])) {
                return ResponseHelper::sendError($response, 'Missing required fields', 400);
            }
            
            $result = $this->authService->register($data);
            return ResponseHelper::sendResponse($response, $result, 201);
            
        } catch (\Exception $e) {
            error_log('Registration error: ' . $e->getMessage());
            return ResponseHelper::sendError($response, $e->getMessage(), 400);
        }
    }
    
    public function login(Request $request, Response $response): Response {
        try {
            $data = $request->getParsedBody();
            error_log('Login attempt for email: ' . ($data['email'] ?? 'not provided'));
            
            if (empty($data['email']) || empty($data['password'])) {
                return ResponseHelper::sendError($response, 'Email and password are required', 400);
            }
            
            $result = $this->authService->login($data);
            return ResponseHelper::sendResponse($response, $result);
            
        } catch (\Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            return ResponseHelper::sendError($response, 'Invalid credentials', 401);
        }
    }

    public function getCurrentUser(Request $request, Response $response): Response {
        try {
            $userId = $request->getAttribute('user_id');
            $user = $this->userModel->findById($userId);
            
            if (!$user) {
                throw new RuntimeException('User not found');
            }
            
            // Remove sensitive data
            unset($user['password_hash']);
            
            $response->getBody()->write(json_encode([
                'user' => $user
            ]));
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }
    }

    public function verifyEmail(Request $request, Response $response): Response {
        try {
            $data = $request->getParsedBody();
            
            if (empty($data['token'])) {
                return ResponseHelper::sendError($response, 'Verification token is required', 400);
            }
            
            $user = $this->authService->verifyEmailToken($data['token']);
            
            if (!$user) {
                return ResponseHelper::sendError($response, 'Invalid or expired verification token', 400);
            }
            
            return ResponseHelper::sendResponse($response, [
                'message' => 'Email verified successfully'
            ]);
            
        } catch (\Exception $e) {
            return ResponseHelper::sendError($response, $e->getMessage(), 400);
        }
    }

    public function resendVerification(Request $request, Response $response): Response {
        try {
            $userId = $request->getAttribute('user_id');
            $user = $this->authService->getUser($userId);
            
            if (!$user) {
                return ResponseHelper::sendError($response, 'User not found', 404);
            }
            
            if ($user['email_verified_at']) {
                return ResponseHelper::sendError($response, 'Email already verified', 400);
            }
            
            $token = $this->authService->createEmailVerificationToken($userId);
            
            $emailService = new EmailService();
            $emailSent = $emailService->sendVerificationEmail(
                $user['email'],
                $user['username'],
                $token
            );
            
            if (!$emailSent) {
                return ResponseHelper::sendError($response, 'Failed to send verification email', 500);
            }
            
            return ResponseHelper::sendResponse($response, [
                'message' => 'Verification email sent successfully'
            ]);
            
        } catch (\Exception $e) {
            return ResponseHelper::sendError($response, $e->getMessage(), 500);
        }

    }
}
