<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use PawPath\models\Pet;
use PawPath\api\QuizController;
use PawPath\models\StartingQuiz;
use PawPath\models\QuizResult;

class TestQuizHelper {
    private PDO $pdo;
    private $userId = 1; // We'll use this as our test user ID
    
    public function __construct() {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();
        
        $this->connectToDatabase();
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
        // Delete test pets and relations first
        $this->pdo->exec("
            DELETE ptr FROM Pet_Trait_Relation ptr
            INNER JOIN Pet p ON ptr.pet_id = p.pet_id
            INNER JOIN Shelter s ON p.shelter_id = s.shelter_id
            WHERE s.email = 'test@shelter.com'
        ");
        
        $this->pdo->exec("
            DELETE p FROM Pet p
            INNER JOIN Shelter s ON p.shelter_id = s.shelter_id
            WHERE s.email = 'test@shelter.com'
        ");
        
        // Delete test shelter
        $this->pdo->exec("DELETE FROM Shelter WHERE email = 'test@shelter.com'");
        
        // Clean up quiz data
        $this->pdo->exec("
            DELETE qr FROM Quiz_Result qr
            INNER JOIN Starting_Quiz sq ON qr.quiz_id = sq.quiz_id
            WHERE sq.user_id = 1
        ");
        $this->pdo->exec("DELETE FROM Starting_Quiz WHERE user_id = 1");
        
        echo "Cleaned up existing test data\n";
    }
    
    public function setupTestData(): array {
        // Create test shelter
        $stmt = $this->pdo->prepare("
            INSERT INTO Shelter (name, address, phone, email, is_no_kill)
            VALUES ('Test Shelter', '123 Test St', '555-0123', 'test@shelter.com', 1)
        ");
        $stmt->execute();
        $shelterId = $this->pdo->lastInsertId();
        
        echo "Created test shelter with ID: $shelterId\n";
        
        // Create test pets with traits
        $pets = [
            [
                'name' => 'Luna',
                'species' => 'dog',
                'breed' => 'Golden Retriever',
                'age' => 2,
                'gender' => 'female',
                'description' => 'Energetic and friendly Golden',
                'traits' => ['High Energy', 'Good with kids', 'Easily Trained']
            ],
            [
                'name' => 'Max',
                'species' => 'dog',
                'breed' => 'German Shepherd',
                'age' => 3,
                'gender' => 'male',
                'description' => 'Intelligent and active shepherd',
                'traits' => ['High Energy', 'Easily Trained']
            ]
        ];
        
        foreach ($pets as $petData) {
            // Insert pet
            $stmt = $this->pdo->prepare("
                INSERT INTO Pet (name, species, breed, age, gender, description, shelter_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $petData['name'],
                $petData['species'],
                $petData['breed'],
                $petData['age'],
                $petData['gender'],
                $petData['description'],
                $shelterId
            ]);
            
            $petId = $this->pdo->lastInsertId();
            echo "Created test pet {$petData['name']} with ID: $petId\n";
            
            // Add traits
            foreach ($petData['traits'] as $traitName) {
                // Get trait ID
                $stmt = $this->pdo->prepare("
                    SELECT trait_id FROM Pet_Trait WHERE trait_name = ?
                ");
                $stmt->execute([$traitName]);
                $traitId = $stmt->fetchColumn();
                
                if ($traitId) {
                    // Add trait relation
                    $stmt = $this->pdo->prepare("
                        INSERT INTO Pet_Trait_Relation (pet_id, trait_id)
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$petId, $traitId]);
                    echo "Added trait '$traitName' to pet {$petData['name']}\n";
                } else {
                    echo "Warning: Trait '$traitName' not found in database\n";
                }
            }
        }
        
        return ['shelter_id' => $shelterId];
    }

    private function createTestRequest(array $data = [], array $attributes = []): \Slim\Psr7\Request {
        $request = new \Slim\Psr7\Request(
            'POST',
            new \Slim\Psr7\Uri('http', 'localhost', 80, '/api/quiz/submit'),
            new \Slim\Psr7\Headers(['Content-Type' => 'application/json']),
            [],
            [],
            new \Slim\Psr7\Stream(fopen('php://temp', 'r+')),
            []
        );
        
        // Add body data
        if (!empty($data)) {
            $body = json_encode($data);
            if ($body === false) {
                throw new \RuntimeException("Failed to encode request data: " . json_last_error_msg());
            }
            
            $stream = fopen('php://temp', 'r+');
            if ($stream === false) {
                throw new \RuntimeException("Failed to open stream");
            }
            
            fwrite($stream, $body);
            rewind($stream);
            
            $request = $request->withBody(new \Slim\Psr7\Stream($stream));
        }
        
        // Add attributes (like user_id)
        foreach ($attributes as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }
        
        return $request;
    }
    
    private function createTestResponse(): \Slim\Psr7\Response {
        return new \Slim\Psr7\Response();
    }
    
    public function runTests(): void {
        echo "\nRunning Quiz Controller Tests\n";
        echo "═══════════════════════════\n\n";
        
        $this->testStartQuiz();
        $this->testSubmitQuiz();
        $this->testQuizHistory();
        $this->testSpecificQuizResult();
    }
    
    private function testStartQuiz(): void {
        echo "Test 1: Starting a New Quiz\n";
        echo "──────────────────────────\n";
        
        try {
            $controller = new QuizController();
            $response = $controller->startQuiz(
                $this->createTestRequest(),
                $this->createTestResponse()
            );
            
            $result = json_decode((string)$response->getBody(), true);
            
            if ($result['success']) {
                echo "✓ Successfully retrieved quiz questions\n";
                echo "✓ Found " . $result['data']['total_sections'] . " question sections\n";
                foreach ($result['data']['questions']['sections'] as $section) {
                    echo "  • {$section['title']}: " . count($section['questions']) . " questions\n";
                }
            } else {
                echo "✗ Failed to start quiz\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
    
    private function testSubmitQuiz(): void {
        echo "Test 2: Submitting Quiz Answers\n";
        echo "───────────────────────────────\n";
        
        try {
            $controller = new QuizController();
            
            $answers = [
                'answers' => [
                    'living_situation' => [
                        'living_space' => 'house_large',
                        'outdoor_access' => ['private_yard'],
                        'rental_restrictions' => ['no_restrictions']
                    ],
                    'lifestyle' => [
                        'activity_level' => 'very_active',
                        'time_available' => 'extensive',
                        'work_schedule' => 'regular_hours'
                    ],
                    'household' => [
                        'children' => ['no_children'],
                        'other_pets' => ['no_pets']
                    ],
                    'experience' => [
                        'pet_experience' => 'experienced',
                        'training_willingness' => 'definitely'
                    ],
                    'practical_considerations' => [
                        'budget' => 'flexible',
                        'grooming' => 'high',
                        'allergies' => ['no_allergies']
                    ],
                    'preferences' => [
                        'size_preference' => ['medium'],
                        'noise_tolerance' => 'moderate',
                        'exercise_commitment' => 'active'
                    ]
                ]
            ];
            
            echo "Submitting answers: " . json_encode($answers, JSON_PRETTY_PRINT) . "\n";
            
            $response = $controller->submitQuiz(
                $this->createTestRequest($answers, ['user_id' => $this->userId]),
                $this->createTestResponse()
            );
            
            $result = json_decode((string)$response->getBody(), true);
            
            if ($result['success']) {
                echo "✓ Successfully submitted quiz answers\n";
                echo "✓ Received recommendations:\n";
                echo "  • Recommended species: " . $result['data']['recommendations']['species'] . "\n";
                if (!empty($result['data']['recommendations']['breed'])) {
                    echo "  • Recommended breed: " . $result['data']['recommendations']['breed'] . "\n";
                }
                echo "  • Confidence score: " . $result['data']['confidence_score'] . "%\n";
                echo "  • Found " . count($result['data']['matching_pets']) . " matching pets\n";
                
                if (!empty($result['data']['matching_pets'])) {
                    echo "\nMatching pets:\n";
                    foreach ($result['data']['matching_pets'] as $pet) {
                        echo "  • {$pet['name']} ({$pet['breed']})\n";
                        if (!empty($pet['traits'])) {
                            echo "    Traits:\n";
                            foreach ($pet['traits'] as $category => $traits) {
                                echo "      - $category: " . implode(', ', $traits) . "\n";
                            }
                        }
                    }
                }
                
                $this->lastQuizId = $result['data']['quiz_id'];
            } else {
                echo "✗ Failed to submit quiz\n";
                echo "Error: " . ($result['message'] ?? 'Unknown error') . "\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
        }
        echo "\n";
    }
    
    private function testQuizHistory(): void {
        echo "Test 3: Retrieving Quiz History\n";
        echo "───────────────────────────────\n";
        
        try {
            $controller = new QuizController();
            $response = $controller->getQuizHistory(
                $this->createTestRequest([], ['user_id' => $this->userId]),
                $this->createTestResponse()
            );
            
            $result = json_decode((string)$response->getBody(), true);
            
            if ($result['success']) {
                echo "✓ Successfully retrieved quiz history\n";
                echo "✓ Found " . $result['data']['total_quizzes'] . " previous quizzes\n";
                foreach ($result['data']['history'] as $quiz) {
                    echo "  • Quiz {$quiz['quiz_id']} taken on {$quiz['date_taken']}\n";
                    echo "    Recommended: {$quiz['recommendations']['species']}\n";
                }
            } else {
                echo "✗ Failed to retrieve quiz history\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
    
    private function testSpecificQuizResult(): void {
        echo "Test 4: Retrieving Specific Quiz Result\n";
        echo "────────────────────────────────────────\n";
        
        if (!isset($this->lastQuizId)) {
            echo "✗ No quiz ID available for testing\n\n";
            return;
        }
        
        try {
            $controller = new QuizController();
            $response = $controller->getQuizResult(
                $this->createTestRequest([], ['user_id' => $this->userId]),
                $this->createTestResponse(),
                ['id' => $this->lastQuizId]
            );
            
            $result = json_decode((string)$response->getBody(), true);
            
            if ($result['success']) {
                echo "✓ Successfully retrieved quiz result\n";
                echo "✓ Quiz details:\n";
                echo "  • Date taken: " . $result['data']['date_taken'] . "\n";
                echo "  • Recommended species: " . $result['data']['recommendations']['species'] . "\n";
                if (!empty($result['data']['recommendations']['breed'])) {
                    echo "  • Recommended breed: " . $result['data']['recommendations']['breed'] . "\n";
                }
                echo "  • Trait preferences: " . count($result['data']['recommendations']['traits']) . " traits\n";
            } else {
                echo "✗ Failed to retrieve quiz result\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
}

// Run the tests
try {
    $tester = new TestQuizHelper();
    
    // Clean up any existing test data
    $tester->cleanupTestData();
    
    // Setup test data
    $testData = $tester->setupTestData();
    
    // Run all tests
    $tester->runTests();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
