<?php
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

    public function getApplication(Request $request, Response $response, array $args): Response {
        try {
            $applicationId = (int) $args['id'];
            $userId = $request->getAttribute('user_id');
            
            error_log("Fetching application ID: $applicationId for user: $userId");
            
            $application = $this->adoptionService->getApplication($applicationId);
            
            // Check if user has permission to view this application
            if ($application['user_id'] !== $userId) {
                return ResponseHelper::sendError(
                    $response,
                    "Unauthorized to view this application",
                    403
                );
            }

            return ResponseHelper::sendResponse($response, $application);
        } catch (\Exception $e) {
            error_log("Error in getApplication: " . $e->getMessage());
            return ResponseHelper::sendError($response, $e->getMessage(), 500);
        }
    }

    public function getUserApplications(Request $request, Response $response): Response {
        try {
            $userId = $request->getAttribute('user_id');
            $applications = $this->adoptionService->getUserApplications($userId);
            
            return ResponseHelper::sendResponse($response, $applications);
        } catch (\Exception $e) {
            error_log("Error in getUserApplications: " . $e->getMessage());
            return ResponseHelper::sendError($response, $e->getMessage(), 500);
        }
    }
}
