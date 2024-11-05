<?php
// public/index.php

use Slim\Factory\AppFactory;
use DI\Container;
use PawPath\Api\PetController;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Create Container
$container = new Container();
AppFactory::setContainer($container);

// Create App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add routes
$app->get('/api/pets', [PetController::class, 'getAllPets']);
$app->get('/api/pets/{id}', [PetController::class, 'getPet']);
$app->post('/api/pets', [PetController::class, 'createPet']);

$app->run();

