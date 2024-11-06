<?php
// backend/tests/test_pet_service.php

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use PawPath\services\PetService;

$petService = new PetService();

function printSection($title) {
    echo "\n" . str_repeat("=", 50) . "\n";
    echo $title . "\n";
    echo str_repeat("=", 50) . "\n";
}

try {
    // Test trait management
    printSection("Testing Trait Management");
    
    echo "Adding new traits...\n";
    $playful = $petService->addTrait("Playful");
    echo "Added trait: ";
    print_r($playful);
    
    $quiet = $petService->addTrait("Quiet");
    echo "Added trait: ";
    print_r($quiet);
    
    echo "\nListing all traits:\n";
    $allTraits = $petService->listTraits();
    print_r($allTraits);
    
    // Test pet creation
    printSection("Testing Pet Creation");
    
    // Test validation - should fail
    echo "\nTesting validation with missing required fields...\n";
    try {
        $petService->createPet([
            'name' => 'Buddy'
            // Missing required fields
        ]);
    } catch (RuntimeException $e) {
        echo "Caught expected error: " . $e->getMessage() . "\n";
    }
    
    // Create valid pet
    echo "\nCreating valid pet...\n";
    $petData = [
        'name' => 'Buddy',
        'species' => 'Dog',
        'breed' => 'Labrador',
        'age' => 2,
        'gender' => 'Male',
        'description' => 'A friendly lab who loves to play',
        'shelter_id' => 3, // Use your actual shelter ID
        'traits' => [$playful['trait_id'], $quiet['trait_id']]
    ];
    
    $newPet = $petService->createPet($petData);
    echo "Created new pet:\n";
    print_r($newPet);
    
    // Test pet retrieval
    printSection("Testing Pet Retrieval");
    
    $retrievedPet = $petService->getPet($newPet['pet_id']);
    echo "Retrieved pet:\n";
    print_r($retrievedPet);
    
    // Test pet update
    printSection("Testing Pet Update");
    
    $updateData = [
        'age' => 3,
        'description' => 'A friendly lab who loves to play and is great with kids'
    ];
    
    $updatedPet = $petService->updatePet($newPet['pet_id'], $updateData);
    echo "Updated pet:\n";
    print_r($updatedPet);
    
    // Test invalid update
    echo "\nTesting invalid update (invalid age)...\n";
    try {
        $petService->updatePet($newPet['pet_id'], ['age' => -1]);
    } catch (RuntimeException $e) {
        echo "Caught expected error: " . $e->getMessage() . "\n";
    }
    
    // Test pet listing with filters
    printSection("Testing Pet Listing with Filters");
    
    echo "\nListing all dogs:\n";
    $dogs = $petService->listPets(['species' => 'Dog']);
    print_r($dogs);
    
    echo "\nTesting invalid species filter...\n";
    try {
        $petService->listPets(['species' => 'Dragon']);
    } catch (RuntimeException $e) {
        echo "Caught expected error: " . $e->getMessage() . "\n";
    }
    
    // Test deletion (optional)
    /*
    printSection("Testing Pet Deletion");
    
    $deleted = $petService->deletePet($newPet['pet_id']);
    echo "Pet deleted: " . ($deleted ? "true" : "false") . "\n";
    
    // Verify deletion
    try {
        $petService->getPet($newPet['pet_id']);
    } catch (RuntimeException $e) {
        echo "Caught expected error: " . $e->getMessage() . "\n";
    }
    */
    
} catch (Exception $e) {
    echo "\nUnexpected error occurred: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
