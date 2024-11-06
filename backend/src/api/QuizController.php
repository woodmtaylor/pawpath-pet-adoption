<?php
// backend/src/api/QuizController.php

namespace PawPath\Api;

use PawPath\Services\QuizService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class QuizController {
    private QuizService $quizService;
    
    public function __construct() {
        $this->quizService = new QuizService();
    }
    
    public function submitQuiz(Request $request, Response $response): Response {
        $userId = $request->getAttribute('user_id');
        $answers = $request->getParsedBody();
        
        try {
            $result = $this->quizService->processQuiz($userId, $answers);
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }
    
    public function getQuizHistory(Request $request, Response $response): Response {
        $userId = $request->getAttribute('user_id');
        
        try {
            $history = $this->quizService->getUserQuizHistory($userId);
            $response->getBody()->write(json_encode($history));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }
}
