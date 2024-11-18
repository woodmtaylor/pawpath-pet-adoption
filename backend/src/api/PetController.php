<?php
namespace PawPath\api;

use PawPath\services\PetService;
use PawPath\services\ImageUploadService;
use PawPath\models\PetImage;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PawPath\utils\ResponseHelper;

class PetController {
    private PetService $petService;
    private ?ImageUploadService $imageService;
    private ?PetImage $petImageModel;
    
    public function __construct() {
        $this->petService = new PetService();
        
        try {
            $this->imageService = new ImageUploadService();
            $this->petImageModel = new PetImage();
        } catch (\Exception $e) {
            error_log("Failed to initialize image services: " . $e->getMessage());
            $this->imageService = null;
            $this->petImageModel = null;
        }
    }   
    public function createPet(Request $request, Response $response): Response {
        try {
            $data = $request->getParsedBody();
            error_log('Creating pet with data: ' . print_r($data, true));
            
            if (!is_array($data)) {
                $body = (string) $request->getBody();
                $data = json_decode($body, true);
                error_log('Parsed JSON data: ' . print_r($data, true));
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \RuntimeException('Invalid JSON data provided: ' . json_last_error_msg());
                }
            }
            
            if (empty($data)) {
                throw new \RuntimeException('No data provided');
            }
            
            $result = $this->petService->createPet($data);
            error_log('Pet creation result: ' . print_r($result, true));
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $result
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);
        } catch (\Exception $e) {
            error_log('Error creating pet: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString() // Remove in production
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    }
       

    public function getPet(Request $request, Response $response, array $args): Response {
        try {
            $petId = (int) $args['id'];
            $result = $this->petService->getPet($petId);
            return ResponseHelper::sendResponse($response, $result);
        } catch (\Exception $e) {
            return ResponseHelper::sendError($response, $e->getMessage(), 404);
        }
    }
    
    public function listPets(Request $request, Response $response): Response {
        try {
            $queryParams = $request->getQueryParams();
            error_log("Received request params: " . print_r($queryParams, true));
            
            $page = isset($queryParams['page']) ? (int)$queryParams['page'] : 1;
            $perPage = isset($queryParams['perPage']) ? (int)$queryParams['perPage'] : 12;
            $offset = ($page - 1) * $perPage;

            $queryParams['offset'] = $offset;
            $queryParams['limit'] = $perPage;
            
            $result = $this->petService->listPets($queryParams);
            
            return ResponseHelper::sendResponse($response, [
                'items' => $result['pets'],
                'total' => $result['total'],
                'page' => $page,
                'perPage' => $perPage
            ]);
                
        } catch (\Exception $e) {
            error_log("Error in listPets: " . $e->getMessage());
            return ResponseHelper::sendError($response, $e->getMessage());
        }
    }
    
    public function updatePet(Request $request, Response $response, array $args): Response {
        try {
            $petId = (int) $args['id'];
            $data = $request->getParsedBody();
            
            if (!is_array($data)) {
                $body = (string) $request->getBody();
                $data = json_decode($body, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \RuntimeException('Invalid JSON data provided');
                }
            }
            
            $result = $this->petService->updatePet($petId, $data);
            
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            error_log('Error updating pet: ' . $e->getMessage());
            
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(400);
        }
    }
    
    public function deletePet(Request $request, Response $response, array $args): Response {
        try {
            $petId = (int) $args['id'];
            $this->petService->deletePet($petId);
            
            return $response->withStatus(204);
        } catch (\Exception $e) {
            error_log('Error deleting pet: ' . $e->getMessage());
            
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(400);
        }
    }
    
    public function createTrait(Request $request, Response $response): Response {
        try {
            $data = $request->getParsedBody();
            
            if (!is_array($data) || !isset($data['trait_name'])) {
                throw new \RuntimeException('trait_name is required');
            }
            
            $result = $this->petService->addTrait($data['trait_name']);
            
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(201);
        } catch (\Exception $e) {
            error_log('Error creating trait: ' . $e->getMessage());
            
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(400);
        }
    }
    
    public function listTraits(Request $request, Response $response): Response {
        try {
            $traits = $this->petService->listTraits();
            
            // Transform the data to match the frontend's expectations
            $traitsData = array_map(function($trait) {
                return [
                    'trait_id' => $trait['trait_id'],
                    'trait_name' => $trait['trait_name']
                ];
            }, $traits);
            
            return ResponseHelper::sendResponse($response, [
                'traits' => $traitsData
            ]);
        } catch (\Exception $e) {
            error_log('Error listing traits: ' . $e->getMessage());
            return ResponseHelper::sendError($response, 'Failed to fetch traits', 500);
        }
    }

    public function uploadImages(Request $request, Response $response, array $args): Response {
        try {
            $petId = (int) $args['id'];
            $uploadedFiles = $request->getUploadedFiles();
            
            if (empty($uploadedFiles['images'])) {
                throw new \RuntimeException('No images uploaded');
            }
            
            $images = $uploadedFiles['images'];
            if (!is_array($images)) {
                $images = [$images];
            }
            
            $uploadedImages = [];
            foreach ($images as $index => $image) {
                $imageUrl = $this->imageService->uploadImage([
                    'tmp_name' => $image->getStream()->getMetadata('uri'),
                    'error' => $image->getError(),
                    'type' => $image->getClientMediaType()
                ]);
                
                $imageId = $this->petImageModel->create(
                    $petId, 
                    $imageUrl, 
                    $index === 0 && empty($this->petImageModel->findByPetId($petId))
                );
                
                $uploadedImages[] = [
                    'image_id' => $imageId,
                    'url' => $imageUrl
                ];
            }
            
            return ResponseHelper::sendResponse($response, $uploadedImages, 201);
        } catch (\Exception $e) {
            return ResponseHelper::sendError($response, $e->getMessage(), 400);
        }
    }
    
    public function deleteImage(Request $request, Response $response, array $args): Response {
        try {
            $petId = (int) $args['id'];
            $imageId = (int) $args['imageId'];
            
            $images = $this->petImageModel->findByPetId($petId);
            $imageToDelete = array_filter($images, fn($img) => $img['image_id'] === $imageId);
            
            if (empty($imageToDelete)) {
                throw new \RuntimeException('Image not found');
            }
            
            $image = reset($imageToDelete);
            $this->imageService->deleteImage($image['image_url']);
            $this->petImageModel->delete($imageId, $petId);
            
            return ResponseHelper::sendResponse($response, ['message' => 'Image deleted successfully']);
        } catch (\Exception $e) {
            return ResponseHelper::sendError($response, $e->getMessage(), 400);
        }
    }
    
    public function setPrimaryImage(Request $request, Response $response, array $args): Response {
        try {
            $petId = (int) $args['id'];
            $imageId = (int) $args['imageId'];
            
            $this->petImageModel->setPrimary($imageId, $petId);
            
            return ResponseHelper::sendResponse($response, ['message' => 'Primary image updated successfully']);
        } catch (\Exception $e) {
            return ResponseHelper::sendError($response, $e->getMessage(), 400);
        }
    }
}
