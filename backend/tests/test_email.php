<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use PawPath\services\EmailService;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

class EmailTester {
    private EmailService $emailService;
    
    public function __construct() {
        $this->emailService = new EmailService();
    }
    
    public function testVerificationEmail(): void {
        echo "Testing Verification Email...\n";
        
        $result = $this->emailService->sendVerificationEmail(
            'test@example.com',
            'Test User',
            'test-token-123'
        );
        
        $this->printResult('Verification Email', $result);
    }
    
    public function testPasswordResetEmail(): void {
        echo "\nTesting Password Reset Email...\n";
        
        $result = $this->emailService->sendPasswordResetEmail(
            'test@example.com',
            'Test User',
            'reset-token-123'
        );
        
        $this->printResult('Password Reset Email', $result);
    }
    
    public function testWelcomeEmail(): void {
        echo "\nTesting Welcome Email...\n";
        
        $result = $this->emailService->sendWelcomeEmail(
            'test@example.com',
            'Test User'
        );
        
        $this->printResult('Welcome Email', $result);
    }
    
    private function printResult(string $test, bool $result): void {
        if ($result) {
            echo "\033[32m✓ {$test} sent successfully\033[0m\n";
        } else {
            echo "\033[31m✗ {$test} failed to send\033[0m\n";
        }
    }
}

// Run tests
try {
    $tester = new EmailTester();
    $tester->testVerificationEmail();
    $tester->testPasswordResetEmail();
    $tester->testWelcomeEmail();
} catch (Exception $e) {
    echo "\033[31mError: " . $e->getMessage() . "\033[0m\n";
}
