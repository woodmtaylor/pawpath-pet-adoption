<?php
namespace PawPath\api;

use PawPath\services\ShelterService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PawPath\utils\ResponseHelper;

class ShelterController {
    private ShelterService $shelterService;
    
    public function __construct() {
        try {
            $this->shelterService = new ShelterService();
        } catch (\Exception $e) {
            error_log("Error initializing ShelterController: " . $e->getMessage());
            throw $e;
        }
    }

    public function getShelter(Request $request, Response $response, array $args): Response {
        try {
            error_log("Getting shelter with ID: " . $args['id']);
            
            $shelterId = (int) $args['id'];
            $shelter = $this->shelterService->getShelter($shelterId);
            
            if (!$shelter) {
                error_log("Shelter not found: " . $shelterId);
                return ResponseHelper::sendError(
                    $response,
                    "Shelter not found",
                    404
                );
            }
            
            // Add additional shelter information
            $shelter['total_pets'] = $this->shelterService->getTotalPets($shelterId);
            $shelter['active_applications'] = $this->shelterService->getActiveApplications($shelterId);
            
            error_log("Found shelter: " . json_encode($shelter));
            return ResponseHelper::sendResponse($response, $shelter);
            
        } catch (\Exception $e) {
            error_log("Error in getShelter: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return ResponseHelper::sendError($response, $e->getMessage());
        }
    }
    
    public function listShelters(Request $request, Response $response): Response {
        try {
            error_log("Listing shelters");
            
            $queryParams = $request->getQueryParams();
            error_log("Query params: " . print_r($queryParams, true));
            
            $filters = [
                'search' => $queryParams['search'] ?? null,
                'is_no_kill' => isset($queryParams['is_no_kill']) ? 
                    filter_var($queryParams['is_no_kill'], FILTER_VALIDATE_BOOLEAN) : null
            ];
            
            $shelters = $this->shelterService->listShelters($filters);
            
            // Add additional information for each shelter
            foreach ($shelters as &$shelter) {
                $shelter['total_pets'] = $this->shelterService->getTotalPets($shelter['shelter_id']);
                $shelter['active_applications'] = $this->shelterService->getActiveApplications($shelter['shelter_id']);
            }
            
            return ResponseHelper::sendResponse($response, $shelters);
        } catch (\Exception $e) {
            error_log("Error in listShelters: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return ResponseHelper::sendError($response, $e->getMessage());
        }
    }
}
