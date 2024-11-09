<?php
// src/api/ProductController.php
namespace PawPath\api;

use PawPath\services\ProductService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductController {
    private ProductService $productService;
    
    public function __construct() {
        $this->productService = new ProductService();
    }
    
    public function createProduct(Request $request, Response $response): Response {
        try {
            $data = $request->getParsedBody();
            
            $result = $this->productService->createProduct($data);
            
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
    
    public function getProduct(Request $request, Response $response, array $args): Response {
        try {
            $productId = (int) $args['id'];
            $result = $this->productService->getProduct($productId);
            
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
    
    public function listProducts(Request $request, Response $response): Response {
        try {
            $filters = $request->getQueryParams();
            $result = $this->productService->listProducts($filters);
            
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
    
    public function updateProduct(Request $request, Response $response, array $args): Response {
        try {
            $productId = (int) $args['id'];
            $data = $request->getParsedBody();
            
            $result = $this->productService->updateProduct($productId, $data);
            
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
    
    public function deleteProduct(Request $request, Response $response, array $args): Response {
        try {
            $productId = (int) $args['id'];
            $this->productService->deleteProduct($productId);
            
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
