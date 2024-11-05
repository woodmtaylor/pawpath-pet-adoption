<?php
// src/api/PetController.php

namespace PawPath\Api;

use PawPath\Models\Pet;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PetController {
    private Pet $petModel;
    
    public function __construct() {
        $this->petModel = new Pet();
    }
    
    public function getAllPets(Request $request, Response $response): Response {
        $filters = $request->getQueryParams();
        $pets = $this->petModel->findAll($filters);
        
        $response->getBody()->write(json_encode($pets));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function getPet(Request $request, Response $response, array $args): Response {
        $id = (int) $args['id'];
        $pet = $this->petModel->findById($id);
        
        if (!$pet) {
            $response->getBody()->write(json_encode(['error' => 'Pet not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        $response->getBody()->write(json_encode($pet));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function createPet(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        
        try {
            $id = $this->petModel->create($data);
            $pet = $this->petModel->findById($id);
            
            $response->getBody()->write(json_encode($pet));
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }
}
