<?php
// backend/src/middleware/AuthMiddleware.php

namespace PawPath\middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use PawPath\services\AuthService;  // Changed from PawPath\Services\AuthService

class AuthMiddleware
{
    private AuthService $authService;
    
    public function __construct()
    {
        $this->authService = new AuthService();
    }
    
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        error_log('Processing auth middleware');
        
        $token = $this->extractToken($request);
        
        if (!$token) {
            error_log('No token provided - returning 401');
            $response = new Response();
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'No token provided'
            ]));
            return $response
                ->withStatus(401)
                ->withHeader('Content-Type', 'application/json');
        }
        
        try {
            $payload = $this->authService->validateToken($token);
            if (!$payload) {
                throw new \Exception('Invalid token');
            }
            
            error_log('Token validated successfully for user: ' . ($payload['user_id'] ?? 'unknown'));
            $request = $request->withAttribute('user_id', $payload['user_id']);
            return $handler->handle($request);
            
        } catch (\Exception $e) {
            error_log('Token validation failed: ' . $e->getMessage());
            $response = new Response();
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Invalid token'
            ]));
            return $response
                ->withStatus(401)
                ->withHeader('Content-Type', 'application/json');
        }
    }
    
    private function extractToken(Request $request): ?string
    {
        $header = $request->getHeaderLine('Authorization');
        
        if (empty($header)) {
            error_log('No Authorization header found');
            return null;
        }
        
        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            error_log('Token extracted: ' . substr($matches[1], 0, 10) . '...');
            return $matches[1];
        }
        
        error_log('Invalid Authorization header format');
        return null;
    }
}
