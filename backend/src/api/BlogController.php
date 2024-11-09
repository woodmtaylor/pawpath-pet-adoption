<?php
// src/api/BlogController.php
namespace PawPath\api;

use PawPath\services\BlogService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class BlogController {
    private BlogService $blogService;
    
    public function __construct() {
        $this->blogService = new BlogService();
    }
    
    public function createPost(Request $request, Response $response): Response {
        try {
            $data = $request->getParsedBody();
            $data['author_id'] = $request->getAttribute('user_id');
            
            $result = $this->blogService->createPost($data);
            
            $response->getBody()->write(json_encode($result));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    }
    
    public function getPost(Request $request, Response $response, array $args): Response {
        try {
            $postId = (int) $args['id'];
            $result = $this->blogService->getPost($postId);
            
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
    }
    
    public function listPosts(Request $request, Response $response): Response {
        try {
            $filters = $request->getQueryParams();
            $result = $this->blogService->listPosts($filters);
            
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    }
    
    public function updatePost(Request $request, Response $response, array $args): Response {
        try {
            $postId = (int) $args['id'];
            $data = $request->getParsedBody();
            
            // Verify user is the author
            $userId = $request->getAttribute('user_id');
            $result = $this->blogService->updatePost($postId, $data, $userId);
            
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    }
    
    public function deletePost(Request $request, Response $response, array $args): Response {
        try {
            $postId = (int) $args['id'];
            $userId = $request->getAttribute('user_id');
            
            $this->blogService->deletePost($postId, $userId);
            
            return $response->withStatus(204);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    }
}
