<?php
// backend/public/index.php

use Slim\Factory\AppFactory;
use DI\Container;
use PawPath\api\AuthController;
use PawPath\api\ShelterController;
use PawPath\api\PetController;
use PawPath\api\QuizController;
use PawPath\api\AdoptionController;
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

// Protected routes group
$app->group('/api', function ($group) {
    // Quiz routes
    $group->get('/quiz/start', function ($request, $response) {
        $controller = new QuizController();
        return $controller->startQuiz($request, $response);
    });
    
    $group->post('/quiz/submit', function ($request, $response) {
        $controller = new QuizController();
        return $controller->submitQuiz($request, $response);
    });
    
    $group->get('/quiz/history', function ($request, $response) {
        $controller = new QuizController();
        return $controller->getQuizHistory($request, $response);
    });
    
    $group->get('/quiz/{id}', function ($request, $response, $args) {
        $controller = new QuizController();
        return $controller->getQuizResult($request, $response, $args);
    });
    
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
    
    // Pet routes
    $group->post('/pets', function ($request, $response) {
        $controller = new PetController();
        return $controller->createPet($request, $response);
    });
    
    $group->get('/pets', function ($request, $response) {
        $controller = new PetController();
        return $controller->listPets($request, $response);
    });
    
    $group->get('/pets/{id}', function ($request, $response, $args) {
        $controller = new PetController();
        return $controller->getPet($request, $response, $args);
    });
    
    $group->put('/pets/{id}', function ($request, $response, $args) {
        $controller = new PetController();
        return $controller->updatePet($request, $response, $args);
    });
    
    $group->delete('/pets/{id}', function ($request, $response, $args) {
        $controller = new PetController();
        return $controller->deletePet($request, $response, $args);
    });
    
    // Pet trait routes
    $group->post('/pet-traits', function ($request, $response) {
        $controller = new PetController();
        return $controller->createTrait($request, $response);
    });
    
    $group->get('/pet-traits', function ($request, $response) {
        $controller = new PetController();
        return $controller->listTraits($request, $response);
    });
    
    // Adoption Application routes
    $group->post('/adoptions', function ($request, $response) {
        $controller = new AdoptionController();
        return $controller->submitApplication($request, $response);
    });
    
    $group->get('/adoptions/user', function ($request, $response) {
        $controller = new AdoptionController();
        return $controller->getUserApplications($request, $response);
    });
    
    $group->get('/adoptions/shelter/{shelter_id}', function ($request, $response, $args) {
        $controller = new AdoptionController();
        return $controller->getShelterApplications($request, $response, $args);
    });
    
    $group->get('/adoptions/pet/{pet_id}', function ($request, $response, $args) {
        $controller = new AdoptionController();
        return $controller->getPetApplications($request, $response, $args);
    });
    
    $group->get('/adoptions/{id}', function ($request, $response, $args) {
        $controller = new AdoptionController();
        return $controller->getApplication($request, $response, $args);
    });
    
    $group->put('/adoptions/{id}/status', function ($request, $response, $args) {
        $controller = new AdoptionController();
        return $controller->updateApplicationStatus($request, $response, $args);
    });
})->add(new AuthMiddleware());

// Run the app
$app->run();
