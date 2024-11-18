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
        $userId = $request->getAttribute('user_id');
        
        // Get user from the database
        $db = \PawPath\config\database\DatabaseConfig::getConnection();
        $stmt = $db->prepare("SELECT role FROM User WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $response = new Response();
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Authentication required'
            ]));
            return $response->withStatus(401)
                           ->withHeader('Content-Type', 'application/json');
        }
        
        // Get role hierarchy
        $roleHierarchy = [
            'admin' => ['admin'],
            'shelter_staff' => ['admin', 'shelter_staff'],
            'adopter' => ['admin', 'shelter_staff', 'adopter']
        ];
        
        // Check if user's role has permission
        if (!in_array($user['role'], $roleHierarchy[$this->requiredRole] ?? [])) {
            $response = new Response();
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Insufficient permissions'
            ]));
            return $response->withStatus(403)
                           ->withHeader('Content-Type', 'application/json');
        }
        
        // Add user to request attributes
        $request = $request->withAttribute('user', [
            'user_id' => $userId,
            'role' => $user['role']
        ]);
        
        return $handler->handle($request);
    }
}
