<?php
// backend/src/services/QuizService.php

namespace PawPath\Services;

use PawPath\Models\StartingQuiz;
use PawPath\Models\QuizResult;
use PawPath\Models\Pet;

class QuizService {
    private StartingQuiz $startingQuiz;
    private QuizResult $quizResult;
    private Pet $petModel;
    
    public function __construct() {
        $this->startingQuiz = new StartingQuiz();
        $this->quizResult = new QuizResult();
        $this->petModel = new Pet();
    }
    
    public function processQuiz(int $userId, array $answers): array {
        // Create new quiz entry
        $quizId = $this->startingQuiz->create($userId);
        
        // Process quiz answers to determine recommendations
        $recommendation = $this->analyzeAnswers($answers);
        
        // Save quiz results
        $resultData = [
            'quiz_id' => $quizId,
            'recommended_species' => $recommendation['species'],
            'recommended_breed' => $recommendation['breed']
        ];
        
        $this->quizResult->create($resultData);
        
        // Find matching pets
        $matchingPets = $this->findMatchingPets($recommendation);
        
        return [
            'quiz_id' => $quizId,
            'recommendations' => $recommendation,
            'matching_pets' => $matchingPets
        ];
    }
    
    private function analyzeAnswers(array $answers): array {
        // Define point system for different species and breeds
        $speciesPoints = [
            'dog' => 0,
            'cat' => 0,
            'bird' => 0,
            'other' => 0
        ];
        
        $breedTraits = [
            'energy_level' => 0,
            'space_needed' => 0,
            'maintenance' => 0,
            'social_needs' => 0
        ];
        
        // Process living situation
        if (isset($answers['living_space'])) {
            switch ($answers['living_space']) {
                case 'apartment':
                    $speciesPoints['cat'] += 2;
                    $breedTraits['space_needed'] = -1;
                    break;
                case 'house':
                    $speciesPoints['dog'] += 2;
                    $breedTraits['space_needed'] = 1;
                    break;
            }
        }
        
        // Process activity level
        if (isset($answers['activity_level'])) {
            switch ($answers['activity_level']) {
                case 'very_active':
                    $speciesPoints['dog'] += 2;
                    $breedTraits['energy_level'] = 2;
                    break;
                case 'moderate':
                    $speciesPoints['dog'] += 1;
                    $speciesPoints['cat'] += 1;
                    $breedTraits['energy_level'] = 1;
                    break;
                case 'sedentary':
                    $speciesPoints['cat'] += 2;
                    $breedTraits['energy_level'] = 0;
                    break;
            }
        }
        
        // Process time availability
        if (isset($answers['time_available'])) {
            switch ($answers['time_available']) {
                case 'very_limited':
                    $speciesPoints['cat'] += 2;
                    $breedTraits['maintenance'] = -1;
                    break;
                case 'moderate':
                    $speciesPoints['dog'] += 1;
                    $breedTraits['maintenance'] = 0;
                    break;
                case 'lots':
                    $speciesPoints['dog'] += 2;
                    $breedTraits['maintenance'] = 1;
                    break;
            }
        }
        
        // Determine recommended species
        $recommendedSpecies = array_search(max($speciesPoints), $speciesPoints);
        
        // Determine recommended breed characteristics
        $recommendedBreed = $this->getBreedRecommendation($recommendedSpecies, $breedTraits);
        
        return [
            'species' => $recommendedSpecies,
            'breed' => $recommendedBreed
        ];
    }
    
    private function getBreedRecommendation(string $species, array $traits): ?string {
        // Example breed recommendations based on species and traits
        $breedRecommendations = [
            'dog' => [
                'high_energy_large_space' => 'Border Collie',
                'high_energy_medium_space' => 'Australian Shepherd',
                'medium_energy_medium_space' => 'Golden Retriever',
                'low_energy_small_space' => 'French Bulldog'
            ],
            'cat' => [
                'high_energy' => 'Abyssinian',
                'medium_energy' => 'American Shorthair',
                'low_energy' => 'Persian'
            ]
        ];
        
        // Logic to select breed based on traits
        if ($species === 'dog') {
            if ($traits['energy_level'] > 1 && $traits['space_needed'] > 0) {
                return $breedRecommendations['dog']['high_energy_large_space'];
            } elseif ($traits['energy_level'] > 1) {
                return $breedRecommendations['dog']['high_energy_medium_space'];
            } elseif ($traits['energy_level'] > 0) {
                return $breedRecommendations['dog']['medium_energy_medium_space'];
            } else {
                return $breedRecommendations['dog']['low_energy_small_space'];
            }
        }
        
        if ($species === 'cat') {
            if ($traits['energy_level'] > 1) {
                return $breedRecommendations['cat']['high_energy'];
            } elseif ($traits['energy_level'] > 0) {
                return $breedRecommendations['cat']['medium_energy'];
            } else {
                return $breedRecommendations['cat']['low_energy'];
            }
        }
        
        return null;
    }
    
    private function findMatchingPets(array $recommendation): array {
        $filters = [
            'species' => $recommendation['species']
        ];
        
        if ($recommendation['breed']) {
            $filters['breed'] = $recommendation['breed'];
        }
        
        return $this->petModel->findAll($filters);
    }
    
    public function getUserQuizHistory(int $userId): array {
        return $this->startingQuiz->findByUser($userId);
    }
}
