<?php
// backend/public/index.php

// Basic error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
echo "Autoloader included successfully\n";

use Slim\Factory\AppFactory;
use DI\Container;
use PawPath\api\AuthController;
use PawPath\api\PetController;
use PawPath\api\QuizController;
use PawPath\middleware\AuthMiddleware;

// Create Container
$container = new Container();
AppFactory::setContainer($container);

// Create App
$app = AppFactory::create();

// Add error middleware with detailed error display
$app->addErrorMiddleware(true, true, true);

// Test route
$app->get('/test', function ($request, $response) {
    $response->getBody()->write('Test route works!');
    return $response;
});

// Debug route with proper response handling
$app->post('/debug-register', function ($request, $response) {
    $data = $request->getParsedBody();
    var_dump($data);
    
    $response->getBody()->write(json_encode([
        'received' => $data,
        'debug' => 'Data received and parsed successfully'
    ]));
    
    return $response->withHeader('Content-Type', 'application/json');
});

// Registration route with debug output
$app->post('/api/auth/register', function ($request, $response) {
    try {
        // Debug output
        file_put_contents('php://stderr', "Register route hit!\n");
        
        // Debug request data
        $data = $request->getParsedBody();
        file_put_contents('php://stderr', "Received data: " . print_r($data, true) . "\n");
        
        // Try creating controller
        file_put_contents('php://stderr', "Creating AuthController...\n");
        $controller = new AuthController();
        
        // Try registration
        file_put_contents('php://stderr', "Calling register method...\n");
        return $controller->register($request, $response);
    } catch (\Exception $e) {
        // Log any errors
        file_put_contents('php://stderr', "Error occurred: " . $e->getMessage() . "\n");
        file_put_contents('php://stderr', "Stack trace: " . $e->getTraceAsString() . "\n");
        
        $response->getBody()->write(json_encode([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

$app->run();
