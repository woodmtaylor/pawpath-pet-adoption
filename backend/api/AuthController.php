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
        $data = $request->getParsedBody();
        
        // Debug output
        file_put_contents('php://stderr', "Registration request received with data: " . print_r($data, true) . "\n");
        
        try {
            // Debug before registration
            file_put_contents('php://stderr', "Attempting registration...\n");
            
            $result = $this->authService->register($data);
            
            // Debug after registration
            file_put_contents('php://stderr', "Registration result: " . print_r($result, true) . "\n");
            
            $response->getBody()->write(json_encode($result));
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            // Debug exception
            file_put_contents('php://stderr', "Registration failed with error: " . $e->getMessage() . "\n");
            file_put_contents('php://stderr', "Stack trace: " . $e->getTraceAsString() . "\n");
            
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }
}
