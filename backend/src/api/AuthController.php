<?php
namespace PawPath\api;

use PawPath\services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PawPath\utils\ResponseHelper;

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
            
            // Try to send verification email
            try {
                $emailService = new EmailService();
                $emailService->sendVerificationEmail(
                    $data['email'],
                    $data['username'],
                    $result['user']['email_verification_token']
                );
            } catch (\Exception $e) {
                // Log email error but don't fail registration
                error_log('Failed to send verification email: ' . $e->getMessage());
            }
            
            return ResponseHelper::sendResponse($response, $result, 201);
            
        } catch (\Exception $e) {
            error_log('Registration error: ' . $e->getMessage());
            return ResponseHelper::sendError(
                $response, 
                $e->getMessage(), 
                $e instanceof RuntimeException ? 400 : 500
            );
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
            error_log('Stack trace: ' . $e->getTraceAsString());
            return ResponseHelper::sendError(
                $response, 
                'An error occurred during login. Please try again.', 
                500
            );
        }
    }

    public function getCurrentUser(Request $request, Response $response): Response {
        try {
            $userId = $request->getAttribute('user_id');
            $user = $this->authService->getUser($userId);
            
            if (!$user) {
                throw new \RuntimeException('User not found');
            }
            
            return ResponseHelper::sendResponse($response, [
                'user' => $user
            ]);
        } catch (\Exception $e) {
            error_log('Error getting current user: ' . $e->getMessage());
            return ResponseHelper::sendError(
                $response, 
                'Failed to get user information', 
                500
            );
        }
    }

    public function verifyEmail(Request $request, Response $response): Response {
        try {
            $data = $request->getParsedBody();
            
            if (empty($data['token'])) {
                return ResponseHelper::sendError($response, 'Verification token is required', 400);
            }
            
            $result = $this->authService->verifyEmailToken($data['token']);
            return ResponseHelper::sendResponse($response, [
                'message' => 'Email verified successfully'
            ]);
        } catch (\Exception $e) {
            error_log('Email verification error: ' . $e->getMessage());
            return ResponseHelper::sendError($response, 'Failed to verify email', 500);
        }
    }

    public function resendVerification(Request $request, Response $response): Response {
        try {
            $userId = $request->getAttribute('user_id');
            $result = $this->authService->resendVerificationEmail($userId);
            
            return ResponseHelper::sendResponse($response, [
                'message' => 'Verification email sent successfully'
            ]);
        } catch (\Exception $e) {
            error_log('Resend verification error: ' . $e->getMessage());
            return ResponseHelper::sendError(
                $response, 
                'Failed to resend verification email', 
                500
            );
        }
    }
}
