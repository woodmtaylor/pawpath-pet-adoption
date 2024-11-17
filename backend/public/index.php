<?php
// backend/public/index.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

use Slim\Factory\AppFactory;
use DI\Container;
use PawPath\api\AuthController;
use PawPath\api\ShelterController;
use PawPath\api\PetController;
use PawPath\api\QuizController;
use PawPath\api\AdoptionController;
use PawPath\middleware\AuthMiddleware;
use PawPath\api\BlogController;
use PawPath\api\ProductController;
use PawPath\api\UserProfileController;

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

// Email verification routes
$app->post('/api/auth/verify-email', function ($request, $response) {
    $controller = new AuthController();
    return $controller->verifyEmail($request, $response);
});

$app->post('/api/auth/resend-verification', function ($request, $response) {
    $controller = new AuthController();
    return $controller->resendVerification($request, $response);
})->add(new AuthMiddleware());

// Protected routes group
$app->group('/api', function ($group) {

    $group->get('/auth/me', function ($request, $response) {
        $controller = new AuthController();
        return $controller->getCurrentUser($request, $response);
    });

    // User Profile routes
    $group->get('/profile', function ($request, $response) {
        $controller = new UserProfileController();
        return $controller->getProfile($request, $response);
    });

    $group->put('/profile', function ($request, $response) {
        $controller = new UserProfileController();
        return $controller->updateProfile($request, $response);
    });

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

    $group->get('/pets/{id}/favorite', function ($request, $response, $args) {
        $userId = $request->getAttribute('user_id');
        $petId = (int) $args['id'];
        $service = new \PawPath\services\FavoriteService();
        
        try {
            $isFavorited = $service->isFavorited($userId, $petId);
            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => ['is_favorited' => $isFavorited]
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(400);
        }
    });

    // Add a pet to favorites
    $group->post('/pets/{id}/favorite', function ($request, $response, $args) {
        $userId = $request->getAttribute('user_id');
        $petId = (int) $args['id'];
        $service = new \PawPath\services\FavoriteService();
        
        try {
            $result = $service->addFavorite($userId, $petId);
            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $result
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(400);
        }
    });

    // Remove a pet from favorites
    $group->delete('/pets/{id}/favorite', function ($request, $response, $args) {
        $userId = $request->getAttribute('user_id');
        $petId = (int) $args['id'];
        $service = new \PawPath\services\FavoriteService();
        
        try {
            $result = $service->removeFavorite($userId, $petId);
            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => ['removed' => $result]
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(400);
        }
    });

    // Get user's favorited pets
    $group->get('/favorites', function ($request, $response) {
        $userId = $request->getAttribute('user_id');
        $service = new \PawPath\services\FavoriteService();
        
        try {
            $favorites = $service->getUserFavorites($userId);
            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $favorites
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(400);
        }
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

    // Blog routes
    $group->post('/blog/posts', function ($request, $response) {
        $controller = new BlogController();
        return $controller->createPost($request, $response);
    });

    $group->get('/blog/posts', function ($request, $response) {
        $controller = new BlogController();
        return $controller->listPosts($request, $response);
    });

    $group->get('/blog/posts/{id}', function ($request, $response, $args) {
        $controller = new BlogController();
        return $controller->getPost($request, $response, $args);
    });

    $group->put('/blog/posts/{id}', function ($request, $response, $args) {
        $controller = new BlogController();
        return $controller->updatePost($request, $response, $args);
    });

    $group->delete('/blog/posts/{id}', function ($request, $response, $args) {
        $controller = new BlogController();
        return $controller->deletePost($request, $response, $args);
    });

    // Product routes
    $group->post('/products', function ($request, $response) {
        $controller = new ProductController();
        return $controller->createProduct($request, $response);
    });

    $group->get('/products', function ($request, $response) {
        $controller = new ProductController();
        return $controller->listProducts($request, $response);
    });

    $group->get('/products/{id}', function ($request, $response, $args) {
        $controller = new ProductController();
        return $controller->getProduct($request, $response, $args);
    });

    $group->put('/products/{id}', function ($request, $response, $args) {
        $controller = new ProductController();
        return $controller->updateProduct($request, $response, $args);
    });

    $group->delete('/products/{id}', function ($request, $response, $args) {
        $controller = new ProductController();
        return $controller->deleteProduct($request, $response, $args);
    });

})->add(new AuthMiddleware());

// Run the app
$app->run();
