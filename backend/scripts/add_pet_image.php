<?php
require __DIR__ . '/../vendor/autoload.php';

use PawPath\models\PetImage;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Function to add image to pet
function addImageToPet(int $petId, string $imageName, bool $isPrimary = true) {
    try {
        $petImage = new PetImage();
        $imageUrl = '/uploads/images/' . $imageName;
        
        $imageId = $petImage->create($petId, $imageUrl, $isPrimary);
        echo "Successfully added image to pet. Image ID: " . $imageId . "\n";
        return true;
    } catch (Exception $e) {
        echo "Error adding image: " . $e->getMessage() . "\n";
        return false;
    }
}

// Get command line arguments
if ($argc < 3) {
    echo "Usage: php add_pet_image.php <pet_id> <image_filename>\n";
    exit(1);
}

$petId = (int)$argv[1];
$imageName = $argv[2];

// Add the image
addImageToPet($petId, $imageName);
