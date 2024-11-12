<?php
namespace PawPath\middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use PawPath\config\Permissions;

class RoleMiddleware
{
    private string $requiredRole;
    
    public function __construct(string $role)
    {
        $this->requiredRole = $role;
    }
    
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $user = $request->getAttribute('user');
        
        if (!$user || !isset($user['role'])) {
            $response = new Response();
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Authentication required'
            ]));
            return $response->withStatus(401)
                           ->withHeader('Content-Type', 'application/json');
        }
        
        // Get role hierarchy from permissions config
        $roleHierarchy = [
            'admin' => ['admin', 'shelter_staff', 'adopter'],
            'shelter_staff' => ['shelter_staff', 'adopter'],
            'adopter' => ['adopter']
        ];
        
        // Check if user's role has permission
        if (!in_array($this->requiredRole, $roleHierarchy[$user['role']] ?? [])) {
            $response = new Response();
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Insufficient permissions'
            ]));
            return $response->withStatus(403)
                           ->withHeader('Content-Type', 'application/json');
        }
        
        return $handler->handle($request);
    }
}
