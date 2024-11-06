<?php
// backend/src/services/ShelterService.php

namespace PawPath\services;

use PawPath\models\Shelter;
use RuntimeException;

class ShelterService {
    private Shelter $shelterModel;
    
    public function __construct() {
        $this->shelterModel = new Shelter();
    }
    
    public function createShelter(array $data): array {
        // Validate required fields
        $requiredFields = ['name', 'address', 'phone', 'email'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new RuntimeException("Missing required field: $field");
            }
        }
        
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException("Invalid email format");
        }
        
        // Validate phone number (basic validation)
        if (!preg_match("/^[0-9\-\(\)\/\+\s]*$/", $data['phone'])) {
            throw new RuntimeException("Invalid phone number format");
        }
        
        // Create shelter
        $shelterId = $this->shelterModel->create($data);
        
        // Return the created shelter
        return $this->shelterModel->findById($shelterId);
    }
    
    public function updateShelter(int $id, array $data): array {
        // Verify shelter exists
        $shelter = $this->shelterModel->findById($id);
        if (!$shelter) {
            throw new RuntimeException("Shelter not found");
        }
        
        // Validate email if provided
        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException("Invalid email format");
        }
        
        // Validate phone if provided
        if (isset($data['phone']) && !preg_match("/^[0-9\-\(\)\/\+\s]*$/", $data['phone'])) {
            throw new RuntimeException("Invalid phone number format");
        }
        
        // Update shelter
        $success = $this->shelterModel->update($id, $data);
        if (!$success) {
            throw new RuntimeException("Failed to update shelter");
        }
        
        // Return updated shelter
        return $this->shelterModel->findById($id);
    }
    
    public function deleteShelter(int $id): bool {
        // Verify shelter exists
        $shelter = $this->shelterModel->findById($id);
        if (!$shelter) {
            throw new RuntimeException("Shelter not found");
        }
        
        return $this->shelterModel->delete($id);
    }
    
    public function getShelter(int $id): ?array {
        $shelter = $this->shelterModel->findById($id);
        if (!$shelter) {
            throw new RuntimeException("Shelter not found");
        }
        return $shelter;
    }
    
    public function listShelters(array $filters = []): array {
        return $this->shelterModel->findAll($filters);
    }
}
