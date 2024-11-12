<?php
// backend/src/api/PetController.php

namespace PawPath\api;

use PawPath\services\PetService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PawPath\utils\ResponseHelper;

class PetController {
    private PetService $petService;
    
    public function __construct() {
        $this->petService = new PetService();
    }
    
    public function createPet(Request $request, Response $response): Response {
        try {
            $data = $request->getParsedBody();
            error_log('Creating pet with data: ' . print_r($data, true));
            
            if (!is_array($data)) {
                $body = (string) $request->getBody();
                $data = json_decode($body, true);
                error_log('Parsed JSON data: ' . print_r($data, true));
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \RuntimeException('Invalid JSON data provided: ' . json_last_error_msg());
                }
            }
            
            if (empty($data)) {
                throw new \RuntimeException('No data provided');
            }
            
            $result = $this->petService->createPet($data);
            error_log('Pet creation result: ' . print_r($result, true));
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $result
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);
        } catch (\Exception $e) {
            error_log('Error creating pet: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString() // Remove in production
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    }
       

    public function getPet(Request $request, Response $response, array $args): Response {
        try {
            $petId = (int) $args['id'];
            $result = $this->petService->getPet($petId);
            return ResponseHelper::sendResponse($response, $result);
        } catch (\Exception $e) {
            return ResponseHelper::sendError($response, $e->getMessage(), 404);
        }
    }
    
    public function listPets(Request $request, Response $response): Response {
        try {
            $queryParams = $request->getQueryParams();
            error_log("Received request params: " . print_r($queryParams, true));
            
            $result = $this->petService->listPets($queryParams);
            error_log("Query result: " . print_r($result, true));
            
            $responseData = [
                'success' => true,
                'data' => [
                    'items' => $result['pets'],
                    'total' => $result['total'],
                    'page' => (int)($queryParams['page'] ?? 1),
                    'perPage' => (int)($queryParams['perPage'] ?? 12)
                ]
            ];
            
            error_log("Sending response: " . print_r($responseData, true));
            
            $response->getBody()->write(json_encode($responseData));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
                
        } catch (\Exception $e) {
            error_log("Error in listPets: " . $e->getMessage());
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
    
    public function updatePet(Request $request, Response $response, array $args): Response {
        try {
            $petId = (int) $args['id'];
            $data = $request->getParsedBody();
            
            if (!is_array($data)) {
                $body = (string) $request->getBody();
                $data = json_decode($body, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \RuntimeException('Invalid JSON data provided');
                }
            }
            
            $result = $this->petService->updatePet($petId, $data);
            
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            error_log('Error updating pet: ' . $e->getMessage());
            
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(400);
        }
    }
    
    public function deletePet(Request $request, Response $response, array $args): Response {
        try {
            $petId = (int) $args['id'];
            $this->petService->deletePet($petId);
            
            return $response->withStatus(204);
        } catch (\Exception $e) {
            error_log('Error deleting pet: ' . $e->getMessage());
            
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(400);
        }
    }
    
    public function createTrait(Request $request, Response $response): Response {
        try {
            $data = $request->getParsedBody();
            
            if (!is_array($data) || !isset($data['trait_name'])) {
                throw new \RuntimeException('trait_name is required');
            }
            
            $result = $this->petService->addTrait($data['trait_name']);
            
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(201);
        } catch (\Exception $e) {
            error_log('Error creating trait: ' . $e->getMessage());
            
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(400);
        }
    }
    
    public function listTraits(Request $request, Response $response): Response {
        try {
            $result = $this->petService->listTraits();
            
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            error_log('Error listing traits: ' . $e->getMessage());
            
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(500);
        }
    }
}
