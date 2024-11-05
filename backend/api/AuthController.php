<?php
// src/api/AuthController.php

namespace PawPath\Api;

use PawPath\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController {
    private AuthService $authService;
    
    public function __construct() {
        $this->authService = new AuthService();
    }
    
    public function register(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        
        try {
            $result = $this->authService->register($data);
            $response->getBody()->write(json_encode($result));
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }
    
    public function login(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        
        try {
            $result = $this->authService->login($data['email'], $data['password']);
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
    }
}
