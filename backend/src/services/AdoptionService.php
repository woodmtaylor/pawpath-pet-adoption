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
    
    public function createApplication(int $userId, int $petId): array {
        // Verify user exists
        $user = $this->userModel->findById($userId);
        if (!$user) {
            throw new RuntimeException("User not found");
        }
        
        // Verify pet exists
        $pet = $this->petModel->findById($petId);
        if (!$pet) {
            throw new RuntimeException("Pet not found");
        }
        
        // Check if user has already applied for this pet
        if ($this->applicationModel->hasUserAppliedForPet($userId, $petId)) {
            throw new RuntimeException("You have already applied to adopt this pet");
        }
        
        // Create application
        $applicationId = $this->applicationModel->create([
            'user_id' => $userId,
            'pet_id' => $petId
        ]);
        
        return $this->applicationModel->findById($applicationId);
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
