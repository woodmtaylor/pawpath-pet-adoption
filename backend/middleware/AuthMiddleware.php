<?php
// src/middleware/AuthMiddleware.php

namespace PawPath\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use PawPath\Services\AuthService;

class AuthMiddleware {
    private AuthService $authService;
    
    public function __construct() {
        $this->authService = new AuthService();
    }
    
    public function __invoke(Request $request, RequestHandler $handler): Response {
        $token = $this->extractToken($request);
        
        if (!$token) {
            $response = new Response();
            $response->getBody()->write(json_encode(['error' => 'No token provided']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
        
        $payload = $this->authService->validateToken($token);
        
        if (!$payload) {
            $response = new Response();
            $response->getBody()->write(json_encode(['error' => 'Invalid token']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
        
        // Add user to request attributes
        $request = $request->withAttribute('user_id', $payload['user_id']);
        
        return $handler->handle($request);
    }
    
    private function extractToken(Request $request): ?string {
        $header = $request->getHeaderLine('Authorization');
        
        if (empty($header)) {
            return null;
        }
        
        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
}
