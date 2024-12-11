<?php
namespace PawPath\api;

use PawPath\services\ImageUploadService;
use PawPath\models\UserProfile;
use PawPath\utils\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserProfileController {
    private UserProfile $profileModel;
    private ImageUploadService $imageService;
    
    public function __construct() {
        $this->profileModel = new UserProfile();
        $this->imageService = new ImageUploadService();
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

    public function uploadProfileImage(Request $request, Response $response): Response {
        try {
            $userId = $request->getAttribute('user_id');
            $uploadedFiles = $request->getUploadedFiles();
            
            if (empty($uploadedFiles['profile_image'])) {
                throw new \RuntimeException('No image uploaded');
            }
            
            $uploadedFile = $uploadedFiles['profile_image'];
            $imageUrl = $this->imageService->uploadProfileImage([
                'tmp_name' => $uploadedFile->getStream()->getMetadata('uri'),
                'error' => $uploadedFile->getError(),
                'type' => $uploadedFile->getClientMediaType()
            ]);
            
            $this->profileModel->updateProfileImage($userId, $imageUrl);
            
            return ResponseHelper::sendResponse($response, [
                'profile_image' => $imageUrl
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::sendError($response, $e->getMessage(), 400);
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
