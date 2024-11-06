<?php
// backend/src/api/PetController.php

namespace PawPath\api;

use PawPath\services\PetService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

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
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \RuntimeException('Invalid JSON data provided');
                }
            }
            
            $result = $this->petService->createPet($data);
            
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(201);
        } catch (\Exception $e) {
            error_log('Error creating pet: ' . $e->getMessage());
            
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(400);
        }
    }
    
    public function getPet(Request $request, Response $response, array $args): Response {
        try {
            $petId = (int) $args['id'];
            $result = $this->petService->getPet($petId);
            
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            error_log('Error getting pet: ' . $e->getMessage());
            
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(404);
        }
    }
    
    public function listPets(Request $request, Response $response): Response {
        try {
            $queryParams = $request->getQueryParams();
            $filters = [];
            
            // Handle search filters
            $validFilters = ['species', 'breed', 'age_min', 'age_max', 'gender', 'shelter_id', 'traits'];
            foreach ($validFilters as $filter) {
                if (isset($queryParams[$filter])) {
                    if ($filter === 'traits') {
                        // Handle array of traits
                        $filters[$filter] = explode(',', $queryParams[$filter]);
                    } else {
                        $filters[$filter] = $queryParams[$filter];
                    }
                }
            }
            
            $result = $this->petService->listPets($filters);
            
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            error_log('Error listing pets: ' . $e->getMessage());
            
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(400);
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
