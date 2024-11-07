<?php
// backend/tests/test_pet_matching.php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use PawPath\models\Pet;

class TestHelper {
    private PDO $pdo;
    private Pet $petModel;
    
    public function __construct() {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();
        
        $this->connectToDatabase();
        $this->petModel = new Pet();
    }
    
    private function connectToDatabase(): void {
        $dsn = sprintf(
            "mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4",
            $_ENV['DB_HOST'],
            $_ENV['DB_PORT'] ?? '3306',
            $_ENV['DB_DATABASE']
        );
        
        $this->pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        echo "Database connection successful\n";
    }
    
    public function cleanupTestData(): void {
        $this->pdo->exec("DELETE FROM Pet_Trait_Relation WHERE pet_id IN (SELECT pet_id FROM Pet WHERE shelter_id IN (SELECT shelter_id FROM Shelter WHERE email = 'test@shelter.com'))");
        $this->pdo->exec("DELETE FROM Pet WHERE shelter_id IN (SELECT shelter_id FROM Shelter WHERE email = 'test@shelter.com')");
        $this->pdo->exec("DELETE FROM Shelter WHERE email = 'test@shelter.com'");
        echo "Cleaned up existing test data\n";
    }
    
    public function createTestShelter(): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO Shelter (name, address, phone, email, is_no_kill)
            VALUES ('Test Shelter', '123 Test St', '555-0123', 'test@shelter.com', 1)
        ");
        $stmt->execute();
        $shelterId = (int) $this->pdo->lastInsertId();
        echo "Created test shelter with ID: $shelterId\n\n";
        return $shelterId;
    }
    
    public function getAvailableTraits(): array {
        $stmt = $this->pdo->query("
            SELECT t.trait_id, t.trait_name, tc.name as category, t.value_type
            FROM Pet_Trait t
            JOIN Trait_Category tc ON t.category_id = tc.category_id
            ORDER BY tc.name, t.trait_name
        ");
        $traits = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Available traits by category:\n";
        $currentCategory = '';
        foreach ($traits as $trait) {
            if ($currentCategory !== $trait['category']) {
                $currentCategory = $trait['category'];
                echo "\n{$trait['category']}:\n";
            }
            echo "  - {$trait['trait_name']} (ID: {$trait['trait_id']}, Type: {$trait['value_type']})\n";
        }
        echo "\n";
        
        return $traits;
    }
    
    public function createTestPets(int $shelterId, array $traits): array {
        $highEnergyTrait = array_filter($traits, fn($t) => $t['trait_name'] === 'High Energy');
        $goodWithKidsTrait = array_filter($traits, fn($t) => $t['trait_name'] === 'Good with kids');
        $highEnergyTrait = reset($highEnergyTrait);
        $goodWithKidsTrait = reset($goodWithKidsTrait);
        
        if (!$highEnergyTrait || !$goodWithKidsTrait) {
            throw new Exception("Could not find required traits");
        }
        
        $testPets = [
            [
                'name' => 'Luna',
                'species' => 'dog',
                'breed' => 'Golden Retriever',
                'age' => 2,
                'gender' => 'female',
                'description' => 'Friendly and energetic Golden Retriever',
                'shelter_id' => $shelterId,
                'traits' => [$highEnergyTrait['trait_id'], $goodWithKidsTrait['trait_id']]
            ],
            [
                'name' => 'Max',
                'species' => 'dog',
                'breed' => 'German Shepherd',
                'age' => 3,
                'gender' => 'male',
                'description' => 'Intelligent and loyal German Shepherd',
                'shelter_id' => $shelterId,
                'traits' => [$goodWithKidsTrait['trait_id']]
            ]
        ];
        
        $petIds = [];
        foreach ($testPets as $petData) {
            try {
                $petIds[] = $this->petModel->create($petData);
                echo "Created {$petData['name']} successfully\n";
            } catch (Exception $e) {
                echo "Error creating {$petData['name']}: " . $e->getMessage() . "\n";
            }
        }
        
        return $petIds;
    }
        
    public function testTraitMatching(array $traits): void {
        echo "\nTesting trait matching functionality:\n";
        echo "────────────────────────────────\n";
        
        $filters = [
            'species' => 'dog',
            'traits' => [
                ['trait' => 'High Energy'],
                ['trait' => 'Good with kids']
            ]
        ];
        
        echo "Searching for dogs with traits: High Energy, Good with kids\n\n";
        
        $matchingPets = $this->petModel->findAllWithTraits($filters);
        echo "Found " . count($matchingPets) . " matching pets:\n\n";
        
        foreach ($matchingPets as $pet) {
            echo "╔══════════════════════════════════\n";
            echo "║ {$pet['name']} ({$pet['breed']})\n";
            echo "║ Matching traits: {$pet['matching_trait_count']}\n";
            echo "║ Traits by category:\n";
            
            if (!empty($pet['traits'])) {
                foreach ($pet['traits'] as $category => $traits) {
                    echo "║   • {$category}: " . implode(', ', $traits) . "\n";
                }
            } else {
                echo "║   No traits assigned\n";
            }
            
            echo "╚══════════════════════════════════\n\n";
        }
    }

    public function cleanup(array $petIds, int $shelterId): void {
        echo "\nCleaning up test data...\n";
        foreach ($petIds as $petId) {
            $this->petModel->delete($petId);
            echo "Deleted pet ID: $petId\n";
        }
        
        $stmt = $this->pdo->prepare("DELETE FROM Shelter WHERE shelter_id = ?");
        $stmt->execute([$shelterId]);
        echo "Deleted test shelter\n";
    }
}

// Run the tests
try {
    $tester = new TestHelper();
    
    // Setup
    $tester->cleanupTestData();
    $shelterId = $tester->createTestShelter();
    $traits = $tester->getAvailableTraits();
    
    // Create test pets
    $petIds = $tester->createTestPets($shelterId, $traits);
    
    // Test trait matching
    $tester->testTraitMatching($traits);
    
    // Cleanup
    $tester->cleanup($petIds, $shelterId);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
