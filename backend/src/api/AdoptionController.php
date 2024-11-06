<?php
// backend/src/api/AdoptionController.php

namespace PawPath\api;

use PawPath\services\AdoptionService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdoptionController {
    private AdoptionService $adoptionService;
    
    public function __construct() {
        $this->adoptionService = new AdoptionService();
    }
    
    public function submitApplication(Request $request, Response $response): Response {
        try {
            $userId = $request->getAttribute('user_id'); // From JWT token
            $data = $request->getParsedBody();
            
            if (!isset($data['pet_id'])) {
                throw new \RuntimeException('pet_id is required');
            }
            
            $result = $this->adoptionService->createApplication(
                $userId,
                (int) $data['pet_id']
            );
            
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(201);
        } catch (\Exception $e) {
            error_log('Error submitting adoption application: ' . $e->getMessage());
            
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(400);
        }
    }
    
    public function getUserApplications(Request $request, Response $response): Response {
        try {
            $userId = $request->getAttribute('user_id'); // From JWT token
            $result = $this->adoptionService->getUserApplications($userId);
            
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            error_log('Error getting user applications: ' . $e->getMessage());
            
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(400);
        }
    }
    
    public function getShelterApplications(Request $request, Response $response, array $args): Response {
        try {
            $shelterId = (int) $args['shelter_id'];
            $result = $this->adoptionService->getShelterApplications($shelterId);
            
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            error_log('Error getting shelter applications: ' . $e->getMessage());
            
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(400);
        }
    }
    
    public function getApplication(Request $request, Response $response, array $args): Response {
        try {
            $applicationId = (int) $args['id'];
            $result = $this->adoptionService->getApplication($applicationId);
            
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            error_log('Error getting application: ' . $e->getMessage());
            
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(404);
        }
    }
    
    public function updateApplicationStatus(Request $request, Response $response, array $args): Response {
        try {
            $applicationId = (int) $args['id'];
            $data = $request->getParsedBody();
            
            if (!isset($data['status'])) {
                throw new \RuntimeException('status is required');
            }
            
            $result = $this->adoptionService->updateApplicationStatus(
                $applicationId,
                $data['status']
            );
            
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            error_log('Error updating application status: ' . $e->getMessage());
            
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(400);
        }
    }
    
    public function getPetApplications(Request $request, Response $response, array $args): Response {
        try {
            $petId = (int) $args['pet_id'];
            $result = $this->adoptionService->getPetApplications($petId);
            
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            error_log('Error getting pet applications: ' . $e->getMessage());
            
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(400);
        }
    }
}
