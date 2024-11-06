<?php
// backend/src/api/ShelterController.php

namespace PawPath\api;

use PawPath\services\ShelterService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ShelterController {
    private ShelterService $shelterService;
    
    public function __construct() {
        $this->shelterService = new ShelterService();
    }
    
    public function createShelter(Request $request, Response $response): Response {
        try {
            $data = $request->getParsedBody();
            error_log('Creating shelter with data: ' . print_r($data, true));
            
            if (!is_array($data)) {
                $body = (string) $request->getBody();
                $data = json_decode($body, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \RuntimeException('Invalid JSON data provided');
                }
            }
            
            $result = $this->shelterService->createShelter($data);
            
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(201);
        } catch (\Exception $e) {
            error_log('Error creating shelter: ' . $e->getMessage());
            
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(400);
        }
    }
    
    public function getShelter(Request $request, Response $response, array $args): Response {
        try {
            $shelterId = (int) $args['id'];
            $result = $this->shelterService->getShelter($shelterId);
            
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            error_log('Error getting shelter: ' . $e->getMessage());
            
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(404);
        }
    }
    
    public function listShelters(Request $request, Response $response): Response {
        try {
            $queryParams = $request->getQueryParams();
            $filters = [];
            
            // Handle search parameter
            if (!empty($queryParams['search'])) {
                $filters['search'] = $queryParams['search'];
            }
            
            // Handle no-kill filter
            if (isset($queryParams['is_no_kill'])) {
                $filters['is_no_kill'] = (bool) $queryParams['is_no_kill'];
            }
            
            $result = $this->shelterService->listShelters($filters);
            
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            error_log('Error listing shelters: ' . $e->getMessage());
            
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(500);
        }
    }
    
    public function updateShelter(Request $request, Response $response, array $args): Response {
        try {
            $shelterId = (int) $args['id'];
            $data = $request->getParsedBody();
            
            if (!is_array($data)) {
                $body = (string) $request->getBody();
                $data = json_decode($body, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \RuntimeException('Invalid JSON data provided');
                }
            }
            
            $result = $this->shelterService->updateShelter($shelterId, $data);
            
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            error_log('Error updating shelter: ' . $e->getMessage());
            
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(400);
        }
    }
    
    public function deleteShelter(Request $request, Response $response, array $args): Response {
        try {
            $shelterId = (int) $args['id'];
            $this->shelterService->deleteShelter($shelterId);
            
            return $response->withStatus(204);
        } catch (\Exception $e) {
            error_log('Error deleting shelter: ' . $e->getMessage());
            
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(400);
        }
    }
}
