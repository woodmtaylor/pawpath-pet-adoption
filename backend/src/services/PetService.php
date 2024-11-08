<?php
// backend/src/services/PetService.php

namespace PawPath\services;

use PawPath\models\Pet;
use PawPath\models\PetTrait;
use PawPath\models\Shelter;
use RuntimeException;

class PetService {
    private Pet $petModel;
    private PetTrait $traitModel;
    private Shelter $shelterModel;
    
    public function __construct() {
        $this->petModel = new Pet();
        $this->traitModel = new PetTrait();
        $this->shelterModel = new Shelter();
    }
    
    public function createPet(array $data): array {
        try {
            error_log("Creating pet with data: " . json_encode($data));
            
            // Validate required fields
            $requiredFields = ['name', 'species', 'shelter_id'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new RuntimeException("Missing required field: $field");
                }
            }
            
            // Create pet
            $petId = $this->petModel->create($data);
            error_log("Created pet with ID: $petId");
            
            $pet = $this->petModel->findById($petId);
            if (!$pet) {
                throw new RuntimeException("Failed to retrieve created pet");
            }
            
            return $pet;
        } catch (\Exception $e) {
            error_log("Error in PetService::createPet: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function updatePet(int $id, array $data): array {
        // Verify pet exists
        $pet = $this->petModel->findById($id);
        if (!$pet) {
            throw new RuntimeException("Pet not found");
        }
        
        // Validate species if provided
        if (isset($data['species'])) {
            $validSpecies = ['Dog', 'Cat', 'Bird', 'Rabbit', 'Other'];
            if (!in_array($data['species'], $validSpecies)) {
                throw new RuntimeException("Invalid species");
            }
        }
        
        // Validate age if provided
        if (isset($data['age']) && ($data['age'] < 0 || $data['age'] > 30)) {
            throw new RuntimeException("Invalid age");
        }
        
        // Validate gender if provided
        if (isset($data['gender'])) {
            $validGenders = ['Male', 'Female'];
            if (!in_array($data['gender'], $validGenders)) {
                throw new RuntimeException("Invalid gender");
            }
        }
        
        // Validate shelter if provided
        if (isset($data['shelter_id'])) {
            if (!$this->shelterModel->findById($data['shelter_id'])) {
                throw new RuntimeException("Invalid shelter ID");
            }
        }
        
        // Validate traits if provided
        if (isset($data['traits'])) {
            foreach ($data['traits'] as $traitId) {
                if (!$this->traitModel->findById($traitId)) {
                    throw new RuntimeException("Invalid trait ID: $traitId");
                }
            }
        }
        
        // Update pet
        $this->petModel->update($id, $data);
        return $this->petModel->findById($id);
    }
    
    public function deletePet(int $id): bool {
        // Verify pet exists
        $pet = $this->petModel->findById($id);
        if (!$pet) {
            throw new RuntimeException("Pet not found");
        }
        
        // Check if pet has any pending adoption applications
        // This would be implemented when we add the adoption system
        
        return $this->petModel->delete($id);
    }
    
    public function getPet(int $id): array {
        $pet = $this->petModel->findById($id);
        if (!$pet) {
            throw new RuntimeException("Pet not found");
        }
        return $pet;
    }
    
    public function listPets(array $filters = []): array {
        // Validate filters
        if (isset($filters['age_min']) && $filters['age_min'] < 0) {
            throw new RuntimeException("Minimum age cannot be negative");
        }
        
        if (isset($filters['age_max']) && $filters['age_max'] > 30) {
            throw new RuntimeException("Maximum age cannot exceed 30");
        }
        
        if (isset($filters['species'])) {
            $validSpecies = ['Dog', 'Cat', 'Bird', 'Rabbit', 'Other'];
            if (!in_array($filters['species'], $validSpecies)) {
                throw new RuntimeException("Invalid species filter");
            }
        }
        
        return $this->petModel->findAll($filters);
    }
    
    public function addTrait(string $traitName): array {
        // Validate trait name
        if (empty(trim($traitName))) {
            throw new RuntimeException("Trait name cannot be empty");
        }
        
        $traitId = $this->traitModel->create($traitName);
        return $this->traitModel->findById($traitId);
    }
    
    public function listTraits(): array {
        return $this->traitModel->findAll();
    }
}
