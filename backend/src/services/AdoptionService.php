<?php
// backend/src/services/AdoptionService.php

namespace PawPath\services;

use PawPath\models\AdoptionApplication;
use PawPath\models\Pet;
use PawPath\models\User;
use RuntimeException;

class AdoptionService {
    private AdoptionApplication $applicationModel;
    private Pet $petModel;
    private User $userModel;
    
    public function __construct() {
        $this->applicationModel = new AdoptionApplication();
        $this->petModel = new Pet();
        $this->userModel = new User();
    }
    

    public function createApplication(array $data): array {
        try {
            // Verify required fields
            if (!isset($data['user_id']) || !isset($data['pet_id'])) {
                throw new RuntimeException("User ID and Pet ID are required");
            }

            // Verify user exists
            $user = $this->userModel->findById($data['user_id']);
            if (!$user) {
                throw new RuntimeException("User not found");
            }
            
            // Verify pet exists
            $pet = $this->petModel->findById($data['pet_id']);
            if (!$pet) {
                throw new RuntimeException("Pet not found");
            }
            
            // Check if user has already applied for this pet
            if ($this->applicationModel->hasUserAppliedForPet($data['user_id'], $data['pet_id'])) {
                throw new RuntimeException("You have already applied to adopt this pet");
            }
            
            // Create application with all provided fields
            $applicationData = [
                'user_id' => $data['user_id'],
                'pet_id' => $data['pet_id'],
                'status' => 'pending',
                'application_date' => date('Y-m-d'),
                'reason' => $data['reason'] ?? null,
                'experience' => $data['experience'] ?? null,
                'living_situation' => $data['living_situation'] ?? null,
                'has_other_pets' => $data['has_other_pets'] ?? false,
                'other_pets_details' => $data['other_pets_details'] ?? null,
                'daily_schedule' => $data['daily_schedule'] ?? null,
                'veterinarian' => $data['veterinarian'] ?? null,
                'status_history' => json_encode([
                    [
                        'status' => 'pending',
                        'date' => date('Y-m-d H:i:s'),
                        'note' => 'Application submitted'
                    ]
                ])
            ];
            
            // Create application
            $applicationId = $this->applicationModel->create($applicationData);
            
            // Get and format the created application
            $application = $this->applicationModel->findById($applicationId);
            if (!$application) {
                throw new RuntimeException("Failed to create application");
            }

            return [
                'success' => true,
                'data' => $application
            ];
            
        } catch (\Exception $e) {
            error_log("Error in createApplication: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function getUserApplications(int $userId): array {
        // Verify user exists
        $user = $this->userModel->findById($userId);
        if (!$user) {
            throw new RuntimeException("User not found");
        }
        
        return $this->applicationModel->findByUser($userId);
    }
    
    public function getShelterApplications(int $shelterId): array {
        return $this->applicationModel->findByShelter($shelterId);
    }
    
    public function getApplication(int $applicationId): ?array {
        $application = $this->applicationModel->findById($applicationId);
        if (!$application) {
            throw new RuntimeException("Application not found");
        }
        return $application;
    }
    
    public function updateApplicationStatus(int $applicationId, string $status): array {
        // Verify application exists
        $application = $this->applicationModel->findById($applicationId);
        if (!$application) {
            throw new RuntimeException("Application not found");
        }
        
        // Validate status
        $validStatuses = [
            AdoptionApplication::STATUS_PENDING,
            AdoptionApplication::STATUS_UNDER_REVIEW,
            AdoptionApplication::STATUS_APPROVED,
            AdoptionApplication::STATUS_REJECTED,
            AdoptionApplication::STATUS_WITHDRAWN
        ];
        
        if (!in_array($status, $validStatuses)) {
            throw new RuntimeException("Invalid application status");
        }
        
        // Update status
        $this->applicationModel->updateStatus($applicationId, $status);
        
        return $this->applicationModel->findById($applicationId);
    }
    
    public function getPetApplications(int $petId): array {
        // Verify pet exists
        $pet = $this->petModel->findById($petId);
        if (!$pet) {
            throw new RuntimeException("Pet not found");
        }
        
        return $this->applicationModel->findByPet($petId);
    }
}
