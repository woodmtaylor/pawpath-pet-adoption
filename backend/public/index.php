<?php
// backend/public/index.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use DI\Container;
use PawPath\api\AuthController;
use PawPath\api\ShelterController;
use PawPath\api\PetController;
use PawPath\api\QuizController;
use PawPath\api\AdoptionController;
use PawPath\api\AdminController;
use PawPath\middleware\AuthMiddleware;
use PawPath\middleware\RoleMiddleware;
use PawPath\api\BlogController;
use PawPath\api\ProductController;
use PawPath\api\UserProfileController;

require __DIR__ . '/../vendor/autoload.php';

// Create Container
$container = new Container();
AppFactory::setContainer($container);

// Create App
$app = AppFactory::create();

$app->get('/api/test', function ($request, $response) {
    $response->getBody()->write(json_encode([
        'success' => true,
        'message' => 'API is working'
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

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

$app->post('/api/profile/image', [UserProfileController::class, 'uploadProfileImage'])
    ->add(new AuthMiddleware());

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

    // Admin routes
    $group->get('/admin/stats', function ($request, $response) {
        $controller = new AdminController();
        return $controller->getStats($request, $response);
    })->add(new RoleMiddleware('admin'));

    $group->get('/admin/users', function ($request, $response) {
        $controller = new AdminController();
        return $controller->listUsers($request, $response);
    })->add(new RoleMiddleware('admin'));

    $group->put('/admin/users/{id}/role', function ($request, $response, $args) {
        $controller = new AdminController();
        return $controller->updateUserRole($request, $response, $args);
    })->add(new RoleMiddleware('admin'));

    $group->put('/admin/users/{id}/status', function ($request, $response, $args) {
        $controller = new AdminController();
        return $controller->updateUserStatus($request, $response, $args);
    })->add(new RoleMiddleware('admin'));

    $group->post('/admin/users/{id}/resend-verification', function ($request, $response, $args) {
        $controller = new AdminController();
        return $controller->resendVerification($request, $response, $args);
    })->add(new RoleMiddleware('admin'));

        // Shelter routes with admin-specific functionality
    $group->group('/admin/shelters', function ($group) {
        $group->get('', function ($request, $response) {
            $controller = new AdminController();
            return $controller->listShelters($request, $response);
        });

        $group->post('', function ($request, $response) {
            $controller = new AdminController();
            return $controller->createShelter($request, $response);
        });

        $group->get('/{id}', function ($request, $response, $args) {
            $controller = new AdminController();
            return $controller->getShelter($request, $response, $args);
        });

        $group->put('/{id}', function ($request, $response, $args) {
            $controller = new AdminController();
            return $controller->updateShelter($request, $response, $args);
        });

        $group->delete('/{id}', function ($request, $response, $args) {
            $controller = new AdminController();
            return $controller->deleteShelter($request, $response, $args);
        });
    })->add(new RoleMiddleware('admin'));

    // Regular shelter routes
    $group->group('/shelters', function ($group) {
        // List all shelters
        $group->get('', function ($request, $response) {
            $controller = new ShelterController();
            return $controller->listShelters($request, $response);
        });

        $group->get('/shelter/stats', function ($request, $response) {
            $controller = new ShelterController();
            return $controller->getShelterStats($request, $response);
        });

        $group->get('/shelter/pets', function ($request, $response) {
            $controller = new ShelterController();
            return $controller->getShelterPets($request, $response);
        });

        // Create new shelter
        $group->post('', function ($request, $response) {
            $controller = new ShelterController();
            return $controller->createShelter($request, $response);
        });

        // Get specific shelter
        $group->get('/{id}', function ($request, $response, $args) {
            $controller = new ShelterController();
            return $controller->getShelter($request, $response, $args);
        });

        // Update shelter
        $group->put('/{id}', function ($request, $response, $args) {
            $controller = new ShelterController();
            return $controller->updateShelter($request, $response, $args);
        });

        // Delete shelter
        $group->delete('/{id}', function ($request, $response, $args) {
            $controller = new ShelterController();
            return $controller->deleteShelter($request, $response, $args);
        });
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

    $group->post('/pets/submit', function ($request, $response) {
        $controller = new PetController();
        return $controller->submitPetForAdoption($request, $response);
    });

    // Add inside admin routes group
    $group->get('/admin/pet-submissions', function ($request, $response) {
        $controller = new PetController();
        return $controller->getPetSubmissions($request, $response);
    });

    $group->put('/admin/pet-submissions/{id}', function ($request, $response, $args) {
        $controller = new PetController();
        return $controller->reviewPetSubmission($request, $response, $args);
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

    $group->post('/pets/{id}/images', function ($request, $response, $args) {
        $controller = new PetController();
        return $controller->uploadImages($request, $response, $args);
    });

    $group->delete('/pets/{id}/images/{imageId}', function ($request, $response, $args) {
        $controller = new PetController();
        return $controller->deleteImage($request, $response, $args);
    });

    $group->put('/pets/{id}/images/{imageId}/primary', function ($request, $response, $args) {
        $controller = new PetController();
        return $controller->setPrimaryImage($request, $response, $args);
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

    $group->group('/blog', function($group) {
        $group->get('', [\PawPath\api\BlogController::class, 'listPosts']);  // <-- This route handles /api/blog
        $group->get('/posts', [\PawPath\api\BlogController::class, 'listPosts']);
        $group->get('/posts/{id}', [\PawPath\api\BlogController::class, 'getPost']);
    });

})->add(new AuthMiddleware());

// Run the app
$app->run();
