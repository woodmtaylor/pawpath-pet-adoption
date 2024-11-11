<?php
namespace PawPath\api;

use PawPath\models\UserProfile;
use PawPath\utils\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserProfileController {
    private UserProfile $profileModel;
    
    public function __construct() {
        $this->profileModel = new UserProfile();
    }
    
    public function getProfile(Request $request, Response $response): Response {
        try {
            $userId = $request->getAttribute('user_id');
            $profile = $this->profileModel->findByUserId($userId);
            
            if (!$profile) {
                return ResponseHelper::sendError($response, "Profile not found", 404);
            }
            
            return ResponseHelper::sendResponse($response, $profile);
        } catch (\Exception $e) {
            return ResponseHelper::sendError($response, $e->getMessage());
        }
    }
    
    public function updateProfile(Request $request, Response $response): Response {
        try {
            $userId = $request->getAttribute('user_id');
            $data = $request->getParsedBody();
            
            // Validate required fields
            $requiredFields = ['first_name', 'last_name', 'phone'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return ResponseHelper::sendError(
                        $response, 
                        "Missing required field: $field",
                        400
                    );
                }
            }
            
            // Check if profile exists
            $existingProfile = $this->profileModel->findByUserId($userId);
            
            if ($existingProfile) {
                $success = $this->profileModel->update($userId, $data);
            } else {
                $data['user_id'] = $userId;
                $profileId = $this->profileModel->create($data);
                $success = $profileId > 0;
            }
            
            if (!$success) {
                return ResponseHelper::sendError(
                    $response,
                    "Failed to update profile",
                    400
                );
            }
            
            $updatedProfile = $this->profileModel->findByUserId($userId);
            return ResponseHelper::sendResponse($response, $updatedProfile);
        } catch (\Exception $e) {
            return ResponseHelper::sendError($response, $e->getMessage());
        }
    }
}
