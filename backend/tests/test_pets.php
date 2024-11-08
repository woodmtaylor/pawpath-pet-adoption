<?php
// backend/tests/test_pets.php

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use PawPath\models\Pet;
use PawPath\models\PetTrait;

// Create instances
$petModel = new Pet();
$traitModel = new PetTrait();

// Get existing traits or create new ones
try {
    $traits = $traitModel->findAll();
    if (empty($traits)) {
        echo "Testing Pet Trait Model...\n";
        
        // Create some traits
        $friendlyId = $traitModel->create("Friendly");
        echo "Created trait 'Friendly' with ID: $friendlyId\n";
        
        $gentleId = $traitModel->create("Gentle");
        echo "Created trait 'Gentle' with ID: $gentleId\n";
        
        // List all traits
        $traits = $traitModel->findAll();
        echo "All traits:\n";
        print_r($traits);
    } else {
        $friendlyId = $traits[0]['trait_id'];
        $gentleId = $traits[1]['trait_id'];
    }
    
    echo "\nTesting Pet Model...\n";
    
    // Create a pet - Use the shelter_id from your actual shelter
    $shelter_id = 3; // Replace with your actual shelter ID from the GET shelters request
    
    try {
        $shelterModel = new \PawPath\models\Shelter();
        $shelter = $shelterModel->findById($shelter_id);
        if (!$shelter) {
            throw new \RuntimeException("Shelter with ID $shelter_id not found");
        }
    } catch (Exception $e) {
        echo "Error checking shelter: " . $e->getMessage() . "\n";
        exit(1);
    }

    echo "Using shelter: " . $shelter['name'] . "\n";

    $petData = [
        'name' => 'Max',
        'species' => 'dog',
        'breed' => 'Golden Retriever',
        'age' => 3,
        'gender' => 'Male',
        'description' => 'A very good boy',
        'shelter_id' => $shelter_id,
        'traits' => [$friendlyId, $gentleId]
    ];
    
    echo "Creating pet with shelter_id: $shelter_id\n";
    $petId = $petModel->create($petData);
    echo "Created pet with ID: $petId\n";
    
    // Retrieve the pet
    $pet = $petModel->findById($petId);
    echo "Retrieved pet:\n";
    print_r($pet);
    
    // Update the pet
    $updateData = [
        'age' => 4,
        'description' => 'A very good and friendly boy'
    ];
    $petModel->update($petId, $updateData);
    
    // Retrieve updated pet
    $updatedPet = $petModel->findById($petId);
    echo "\nUpdated pet:\n";
    print_r($updatedPet);
    
    // Test search functionality
    $searchResults = $petModel->findAll(['species' => 'dog']);
    echo "\nSearch results for dogs:\n";
    print_r($searchResults);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
