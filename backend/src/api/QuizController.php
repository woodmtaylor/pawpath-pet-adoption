<?php
namespace PawPath\api;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PawPath\services\QuizService;
use PawPath\models\StartingQuiz;

class QuizController {
    private QuizService $quizService;
    private StartingQuiz $startingQuiz;
    
    public function __construct() {
        $this->quizService = new QuizService();
        $this->startingQuiz = new StartingQuiz();
    }
    
    /**
     * Initialize a new quiz and return questions
     */
    public function startQuiz(Request $request, Response $response): Response {
        try {
            $questions = $this->quizService->getQuizQuestions();
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => [
                    'questions' => $questions,
                    'total_sections' => count($questions['sections']),
                    'estimated_time' => '5-10 minutes'
                ]
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Failed to initialize quiz',
                'message' => $e->getMessage()
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
    
    /**
     * Submit quiz answers and get recommendations
     */
    public function submitQuiz(Request $request, Response $response): Response {
        try {
            $userId = $request->getAttribute('user_id');
            $data = json_decode((string)$request->getBody(), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Invalid JSON data: ' . json_last_error_msg());
            }
            
            if (empty($data['answers'])) {
                throw new \InvalidArgumentException('Quiz answers are required');
            }
            
            // Process quiz and get recommendations
            $result = $this->quizService->processQuiz($userId, $data['answers']);
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => [
                    'quiz_id' => $result['quiz_id'],
                    'recommendations' => [
                        'species' => $result['recommendations']['recommended_species'],
                        'breed' => $result['recommendations']['recommended_breed'],
                        'traits' => $result['recommendations']['trait_preferences']
                    ],
                    'confidence_score' => $result['confidence_score'],
                    'matching_pets' => $result['matching_pets']
                ]
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Invalid quiz submission',
                'message' => $e->getMessage()
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        } catch (\Exception $e) {
            // Add error logging
            error_log("Quiz submission error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Failed to process quiz',
                'message' => $e->getMessage()
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
    
    /**
     * Get quiz history for a user
     */
    public function getQuizHistory(Request $request, Response $response): Response {
        try {
            $userId = $request->getAttribute('user_id');
            $history = $this->startingQuiz->findByUser($userId);
            
            // Process history to include more readable data
            $processedHistory = array_map(function($entry) {
                return [
                    'quiz_id' => $entry['quiz_id'],
                    'date_taken' => $entry['quiz_date'],
                    'recommendations' => [
                        'species' => $entry['recommended_species'],
                        'breed' => $entry['recommended_breed'],
                        'traits' => json_decode($entry['trait_preferences'] ?? '{}', true)
                    ]
                ];
            }, $history);
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => [
                    'history' => $processedHistory,
                    'total_quizzes' => count($history)
                ]
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Failed to retrieve quiz history',
                'message' => $e->getMessage()
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
    
    /**
     * Get specific quiz result
     */
    public function getQuizResult(Request $request, Response $response, array $args): Response {
        try {
            $quizId = (int) $args['id'];
            $userId = $request->getAttribute('user_id');
            
            // Get quiz and verify it belongs to the user
            $quiz = $this->startingQuiz->findById($quizId);
            
            if (!$quiz) {
                throw new \InvalidArgumentException('Quiz not found');
            }
            
            if ($quiz['user_id'] !== $userId) {
                throw new \InvalidArgumentException('Unauthorized access to quiz result');
            }
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => [
                    'quiz_id' => $quiz['quiz_id'],
                    'date_taken' => $quiz['quiz_date'],
                    'recommendations' => [
                        'species' => $quiz['recommended_species'],
                        'breed' => $quiz['recommended_breed'],
                        'traits' => json_decode($quiz['trait_preferences'] ?? '{}', true)
                    ]
                ]
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Invalid quiz request',
                'message' => $e->getMessage()
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Failed to retrieve quiz result',
                'message' => $e->getMessage()
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}
