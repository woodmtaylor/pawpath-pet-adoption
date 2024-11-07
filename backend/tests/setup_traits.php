<?php
// backend/tests/setup_traits.php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Update the path to look in the backend directory
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Add debug output
echo "Environment variables loaded:\n";
echo "DB_HOST: " . $_ENV['DB_HOST'] . "\n";
echo "DB_DATABASE: " . $_ENV['DB_DATABASE'] . "\n";
echo "DB_USERNAME: " . $_ENV['DB_USERNAME'] . "\n";
echo "DB_PASSWORD is set: " . (isset($_ENV['DB_PASSWORD']) ? "Yes" : "No") . "\n";

try {
    $dsn = "mysql:host=" . $_ENV['DB_HOST'] . ";port=" . ($_ENV['DB_PORT'] ?? "3306") . ";dbname=" . $_ENV['DB_DATABASE'] . ";charset=utf8mb4";
    echo "Attempting to connect with DSN: $dsn\n";
    
    $pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "Database connection successful!\n\n";

    // First, create categories if they don't exist
    $categories = [
        ['name' => 'energy_level', 'description' => 'Activity and exercise needs'],
        ['name' => 'social', 'description' => 'Social characteristics'],
        ['name' => 'training', 'description' => 'Training characteristics']
    ];

    foreach ($categories as $category) {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO Trait_Category (name, description)
            VALUES (?, ?)
        ");
        $stmt->execute([$category['name'], $category['description']]);
        echo "Processed category: {$category['name']}\n";
    }

    // Then create basic traits if they don't exist
    $traits = [
        ['name' => 'High Energy', 'category' => 'energy_level', 'type' => 'binary'],
        ['name' => 'Good with Kids', 'category' => 'social', 'type' => 'scale'],
        ['name' => 'Easily Trained', 'category' => 'training', 'type' => 'binary']
    ];

    foreach ($traits as $trait) {
        // Get category ID
        $stmt = $pdo->prepare("SELECT category_id FROM Trait_Category WHERE name = ?");
        $stmt->execute([$trait['category']]);
        $categoryId = $stmt->fetchColumn();
        
        if ($categoryId) {
            $stmt = $pdo->prepare("
                INSERT IGNORE INTO Pet_Trait (trait_name, category_id, value_type)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$trait['name'], $categoryId, $trait['type']]);
            echo "Processed trait: {$trait['name']}\n";
        } else {
            echo "Warning: Category {$trait['category']} not found for trait {$trait['name']}\n";
        }
    }

    echo "\nTrait setup complete!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
