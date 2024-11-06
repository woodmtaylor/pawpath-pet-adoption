<?php
// backend/src/api/AuthController.php

namespace PawPath\api;

use PawPath\services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController {
    private AuthService $authService;
    
    public function __construct() {
        $this->authService = new AuthService();
    }
    
    public function register(Request $request, Response $response): Response {
        try {
            $data = $request->getParsedBody();
            error_log('Received registration data: ' . print_r($data, true));
            
            if (!is_array($data)) {
                $body = (string) $request->getBody();
                $data = json_decode($body, true);
                error_log('Manually parsed JSON data: ' . print_r($data, true));
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \RuntimeException('Invalid JSON data provided');
                }
            }
            
            if (empty($data)) {
                throw new \RuntimeException('No data provided');
            }
            
            $result = $this->authService->register($data);
            
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
            
        } catch (\Exception $e) {
            error_log('Registration error: ' . $e->getMessage());
            
            $errorResponse = [
                'error' => $e->getMessage()
            ];
            
            $response->getBody()->write(json_encode($errorResponse));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
    
    public function login(Request $request, Response $response): Response {
        try {
            $data = $request->getParsedBody();
            error_log('Received login data: ' . print_r($data, true));
            
            if (!is_array($data)) {
                $body = (string) $request->getBody();
                $data = json_decode($body, true);
                error_log('Manually parsed JSON data: ' . print_r($data, true));
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \RuntimeException('Invalid JSON data provided');
                }
            }
            
            if (empty($data)) {
                throw new \RuntimeException('No data provided');
            }
            
            $result = $this->authService->login($data);
            
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            
            $errorResponse = [
                'error' => $e->getMessage()
            ];
            
            $response->getBody()->write(json_encode($errorResponse));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
    }
}
