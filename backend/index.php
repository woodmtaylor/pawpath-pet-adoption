<?php
// backend/public/index.php

use Slim\Factory\AppFactory;
use DI\Container;
use PawPath\api\AuthController;
use PawPath\api\PetController;
use PawPath\api\QuizController;
use PawPath\middleware\AuthMiddleware;

require __DIR__ . '/../vendor/autoload.php';

// Create Container
$container = new Container();
AppFactory::setContainer($container);

// Create App
$app = AppFactory::create();

// Add body parsing middleware
$app->addBodyParsingMiddleware();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Test route
$app->get('/test', function ($request, $response) {
    $response->getBody()->write('Test route works!');
    return $response;
});

// Registration route with debug output
$app->post('/api/auth/register', function ($request, $response) {
    try {
        error_log("Register route hit!");
        
        $controller = new AuthController();
        return $controller->register($request, $response);
    } catch (\Exception $e) {
        error_log("Error in registration route: " . $e->getMessage());
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

$app->run();
