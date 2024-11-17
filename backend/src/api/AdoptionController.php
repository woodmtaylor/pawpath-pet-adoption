<?php
// backend/src/api/AdoptionController.php

namespace PawPath\api;

use PawPath\utils\ResponseHelper;
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
            $userId = $request->getAttribute('user_id');
            $data = $request->getParsedBody();
            
            if (!isset($data['pet_id'])) {
                throw new \RuntimeException('pet_id is required');
            }
            
            // Add application details to the database
            $result = $this->adoptionService->createApplication([
                'user_id' => $userId,
                'pet_id' => (int) $data['pet_id'],
                'reason' => $data['reason'] ?? null,
                'experience' => $data['experience'] ?? null,
                'living_situation' => $data['living_situation'] ?? null,
                'has_other_pets' => $data['has_other_pets'] ?? false,
                'other_pets_details' => $data['other_pets_details'] ?? null,
                'daily_schedule' => $data['daily_schedule'] ?? null,
                'veterinarian' => $data['veterinarian'] ?? null,
                'status' => 'pending'
            ]);
            
            return ResponseHelper::sendResponse($response, $result, 201);
        } catch (\Exception $e) {
            return ResponseHelper::sendError(
                $response, 
                $e->getMessage(),
                400
            );
        }
    }
    
    public function getUserApplications(Request $request, Response $response): Response {
        try {
            $userId = $request->getAttribute('user_id');
            $applications = $this->adoptionService->getUserApplications($userId);
            
            return ResponseHelper::sendResponse($response, $applications);
        } catch (\Exception $e) {
            error_log('Error getting user applications: ' . $e->getMessage());
            return ResponseHelper::sendError($response, $e->getMessage());
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
