<?php
// backend/public/index.php

use Slim\Factory\AppFactory;
use DI\Container;
use PawPath\api\AuthController;
use PawPath\api\ShelterController;
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

// Public routes
$app->post('/api/auth/register', function ($request, $response) {
    $controller = new AuthController();
    return $controller->register($request, $response);
});

$app->post('/api/auth/login', function ($request, $response) {
    $controller = new AuthController();
    return $controller->login($request, $response);
});

// Protected routes
$app->group('/api', function ($group) {
    // Shelter routes
    $group->post('/shelters', function ($request, $response) {
        $controller = new ShelterController();
        return $controller->createShelter($request, $response);
    });
    
    $group->get('/shelters', function ($request, $response) {
        $controller = new ShelterController();
        return $controller->listShelters($request, $response);
    });
    
    $group->get('/shelters/{id}', function ($request, $response, $args) {
        $controller = new ShelterController();
        return $controller->getShelter($request, $response, $args);
    });
    
    $group->put('/shelters/{id}', function ($request, $response, $args) {
        $controller = new ShelterController();
        return $controller->updateShelter($request, $response, $args);
    });
    
    $group->delete('/shelters/{id}', function ($request, $response, $args) {
        $controller = new ShelterController();
        return $controller->deleteShelter($request, $response, $args);
    });
    
    // Profile route (keep existing route)
    $group->get('/profile', function ($request, $response) {
        $userId = $request->getAttribute('user_id');
        $response->getBody()->write(json_encode([
            'message' => 'You are authenticated!',
            'user_id' => $userId
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });
})->add(new AuthMiddleware());

$app->run();
