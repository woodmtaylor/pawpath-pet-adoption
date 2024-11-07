<?php
// src/services/QuizService.php
namespace PawPath\services;

use PawPath\models\StartingQuiz;
use PawPath\models\QuizResult;
use PawPath\models\Pet;

class QuizService {
    private StartingQuiz $startingQuiz;
    private QuizResult $quizResult;
    private Pet $petModel;
    
    // Question weights for scoring
    private const QUESTION_WEIGHTS = [
        'living_space' => 2.0,    // High impact on pet suitability
        'activity_level' => 1.5,  // Important for energy matching
        'time_available' => 1.5,  // Critical for care requirements
        'experience' => 1.0,      // Influences training needs
        'children' => 1.8,        // Important for safety
        'other_pets' => 1.0,      // Affects compatibility
        'budget' => 1.3,          // Practical consideration
        'allergies' => 2.0,       // Critical health factor
        'noise_tolerance' => 1.2  // Environmental factor
    ];
    
    public function __construct() {
        $this->startingQuiz = new StartingQuiz();
        $this->quizResult = new QuizResult();
        $this->petModel = new Pet();
    }
    
    public function getQuizQuestions(): array {
        return [
            'sections' => [
                [
                    'id' => 'living_situation',
                    'title' => 'Living Situation',
                    'questions' => [
                        [
                            'id' => 'living_space',
                            'text' => 'What type of home do you live in?',
                            'type' => 'single_choice',
                            'options' => [
                                'apartment_small' => 'Small Apartment',
                                'apartment_large' => 'Large Apartment',
                                'house_small' => 'Small House',
                                'house_large' => 'Large House with Yard'
                            ],
                            'weight' => self::QUESTION_WEIGHTS['living_space']
                        ],
                        [
                            'id' => 'outdoor_access',
                            'text' => 'Do you have access to outdoor space?',
                            'type' => 'multiple_choice',
                            'options' => [
                                'private_yard' => 'Private Yard',
                                'shared_yard' => 'Shared Yard/Garden',
                                'nearby_park' => 'Nearby Park',
                                'no_outdoor' => 'Limited Outdoor Access'
                            ]
                        ],
                        [
                            'id' => 'rental_restrictions',
                            'text' => 'Do you have any pet restrictions where you live?',
                            'type' => 'multiple_choice',
                            'options' => [
                                'no_restrictions' => 'No Restrictions',
                                'size_limits' => 'Size/Weight Limits',
                                'breed_restrictions' => 'Breed Restrictions',
                                'no_dogs' => 'No Dogs Allowed',
                                'no_cats' => 'No Cats Allowed'
                            ]
                        ]
                    ]
                ],
                [
                    'id' => 'lifestyle',
                    'title' => 'Your Lifestyle',
                    'questions' => [
                        [
                            'id' => 'activity_level',
                            'text' => 'How would you describe your activity level?',
                            'type' => 'single_choice',
                            'options' => [
                                'very_active' => 'Very Active (Daily Exercise)',
                                'moderate' => 'Moderately Active',
                                'somewhat' => 'Somewhat Active',
                                'sedentary' => 'Mostly Sedentary'
                            ],
                            'weight' => self::QUESTION_WEIGHTS['activity_level']
                        ],
                        [
                            'id' => 'time_available',
                            'text' => 'How much time can you dedicate to pet care daily?',
                            'type' => 'single_choice',
                            'options' => [
                                'very_limited' => 'Less than 1 hour',
                                'limited' => '1-2 hours',
                                'moderate' => '2-4 hours',
                                'extensive' => '4+ hours'
                            ],
                            'weight' => self::QUESTION_WEIGHTS['time_available']
                        ],
                        [
                            'id' => 'work_schedule',
                            'text' => 'What best describes your work/daily schedule?',
                            'type' => 'single_choice',
                            'options' => [
                                'home_all_day' => 'Home Most of the Day',
                                'regular_hours' => 'Regular 9-5 Schedule',
                                'long_hours' => 'Long Hours Away',
                                'variable' => 'Variable/Unpredictable Schedule'
                            ]
                        ]
                    ]
                ],
                [
                    'id' => 'household',
                    'title' => 'Household Information',
                    'questions' => [
                        [
                            'id' => 'children',
                            'text' => 'Are there children in your household?',
                            'type' => 'multiple_choice',
                            'options' => [
                                'no_children' => 'No Children',
                                'young_children' => 'Young Children (0-6)',
                                'older_children' => 'Older Children (7-12)',
                                'teenagers' => 'Teenagers (13+)'
                            ],
                            'weight' => self::QUESTION_WEIGHTS['children']
                        ],
                        [
                            'id' => 'other_pets',
                            'text' => 'Do you have other pets?',
                            'type' => 'multiple_choice',
                            'options' => [
                                'no_pets' => 'No Other Pets',
                                'dogs' => 'Dogs',
                                'cats' => 'Cats',
                                'other_pets' => 'Other Pets'
                            ],
                            'weight' => self::QUESTION_WEIGHTS['other_pets']
                        ]
                    ]
                ],
                [
                    'id' => 'experience',
                    'title' => 'Pet Experience',
                    'questions' => [
                        [
                            'id' => 'pet_experience',
                            'text' => 'What is your experience with pets?',
                            'type' => 'single_choice',
                            'options' => [
                                'first_time' => 'First-time Pet Owner',
                                'some_experience' => 'Some Experience',
                                'experienced' => 'Experienced Pet Owner',
                                'professional' => 'Professional Experience'
                            ],
                            'weight' => self::QUESTION_WEIGHTS['experience']
                        ],
                        [
                            'id' => 'training_willingness',
                            'text' => 'Are you willing to attend training classes or work with a trainer?',
                            'type' => 'single_choice',
                            'options' => [
                                'definitely' => 'Definitely',
                                'maybe' => 'Maybe if Needed',
                                'prefer_not' => 'Prefer Not To',
                                'no' => 'Not Interested'
                            ]
                        ]
                    ]
                ],
                [
                    'id' => 'practical_considerations',
                    'title' => 'Practical Considerations',
                    'questions' => [
                        [
                            'id' => 'budget',
                            'text' => 'What is your monthly budget for pet care (including food, supplies, and routine vet care)?',
                            'type' => 'single_choice',
                            'options' => [
                                'limited' => 'Under $50',
                                'moderate' => '$50-$100',
                                'flexible' => '$100-$200',
                                'unlimited' => '$200+'
                            ],
                            'weight' => self::QUESTION_WEIGHTS['budget']
                        ],
                        [
                            'id' => 'grooming',
                            'text' => 'How much grooming are you willing to do?',
                            'type' => 'single_choice',
                            'options' => [
                                'minimal' => 'Minimal (Basic Care)',
                                'moderate' => 'Moderate (Weekly Grooming)',
                                'high' => 'High (Daily Grooming)',
                                'professional' => 'Will Use Professional Groomer'
                            ]
                        ],
                        [
                            'id' => 'allergies',
                            'text' => 'Does anyone in your household have pet allergies?',
                            'type' => 'multiple_choice',
                            'options' => [
                                'no_allergies' => 'No Allergies',
                                'cat_allergies' => 'Cat Allergies',
                                'dog_allergies' => 'Dog Allergies',
                                'other_allergies' => 'Other Animal Allergies'
                            ],
                            'weight' => self::QUESTION_WEIGHTS['allergies']
                        ]
                    ]
                ],
                [
                    'id' => 'preferences',
                    'title' => 'Pet Preferences',
                    'questions' => [
                        [
                            'id' => 'size_preference',
                            'text' => 'What size pet are you looking for?',
                            'type' => 'multiple_choice',
                            'options' => [
                                'small' => 'Small',
                                'medium' => 'Medium',
                                'large' => 'Large',
                                'no_preference' => 'No Preference'
                            ]
                        ],
                        [
                            'id' => 'noise_tolerance',
                            'text' => 'How much noise can you tolerate from a pet?',
                            'type' => 'single_choice',
                            'options' => [
                                'silent' => 'Prefer Silent Pets',
                                'some_noise' => 'Some Noise is OK',
                                'moderate' => 'Moderate Barking/Noise OK',
                                'any_noise' => 'Noise Doesn\'t Bother Me'
                            ],
                            'weight' => self::QUESTION_WEIGHTS['noise_tolerance']
                        ],
                        [
                            'id' => 'exercise_commitment',
                            'text' => 'How much exercise can you provide?',
                            'type' => 'single_choice',
                            'options' => [
                                'minimal' => 'Minimal (Short Walks)',
                                'moderate' => 'Moderate (Daily Walks)',
                                'active' => 'Active (Long Walks/Play)',
                                'very_active' => 'Very Active (Running/Hiking)'
                            ]
                        ]
                    ]
                ]
        ]
    ];
}
     
    public function processQuiz(int $userId, array $answers): array {
        try {
            // Create new quiz entry
            $quizId = $this->startingQuiz->create($userId);
            
            // Analyze answers and generate recommendations
            $analysis = $this->analyzeAnswers($answers);
            
            // Calculate confidence score
            $confidenceScore = $this->calculateConfidenceScore($answers);
            
            // Save results
            $resultData = [
                'quiz_id' => $quizId,
                'recommended_species' => $analysis['recommended_species'],
                'recommended_breed' => $analysis['recommended_breed'],
                'trait_preferences' => $analysis['trait_preferences'],
                'confidence_score' => $confidenceScore
            ];
            
            $resultId = $this->quizResult->create($resultData);
            
            // Find matching pets
            $matchingPets = $this->findMatchingPets($analysis);
            
            return [
                'quiz_id' => $quizId,
                'result_id' => $resultId,
                'recommendations' => $analysis,
                'confidence_score' => $confidenceScore,
                'matching_pets' => $matchingPets
            ];
        } catch (\Exception $e) {
            error_log("Error processing quiz: " . $e->getMessage());
            throw $e;
        }
    }
        
    private function analyzeAnswers(array $answers): array {
        $speciesScores = [
            'dog' => 0,
            'cat' => 0,
            'bird' => 0,
            'rabbit' => 0
        ];
        
        // Use associative array to prevent duplicates
        $traitPreferences = [];
        
        // Helper function to add trait
        $addTrait = function($trait, $value) use (&$traitPreferences) {
            $traitPreferences[$trait] = [
                'trait' => $trait,
                'value' => $value
            ];
        };
        
        // Process living situation
        if (isset($answers['living_situation'])) {
            if (isset($answers['living_situation']['living_space'])) {
                switch ($answers['living_situation']['living_space']) {
                    case 'house_large':
                        $speciesScores['dog'] += 2 * self::QUESTION_WEIGHTS['living_space'];
                        $addTrait('High Energy', 'binary');
                        $addTrait('Easily Trained', 'binary');
                        break;
                    case 'apartment_small':
                        $speciesScores['cat'] += 2 * self::QUESTION_WEIGHTS['living_space'];
                        $addTrait('Apartment Friendly', 'binary');
                        $addTrait('Calm', 'binary');
                        break;
                }
            }
        }
        
        // Process lifestyle
        if (isset($answers['lifestyle'])) {
            if (isset($answers['lifestyle']['activity_level'])) {
                switch ($answers['lifestyle']['activity_level']) {
                    case 'very_active':
                        $speciesScores['dog'] += 2 * self::QUESTION_WEIGHTS['activity_level'];
                        $addTrait('High Energy', 'binary');
                        break;
                    case 'moderate':
                        $addTrait('Easily Trained', 'binary');
                        break;
                    case 'sedentary':
                        $speciesScores['cat'] += 1.5;
                        $addTrait('Calm', 'binary');
                        break;
                }
            }
        }
        
        // Convert trait preferences back to array
        $traitPreferences = array_values($traitPreferences);
        
        // Normalize scores
        $maxScore = max($speciesScores);
        if ($maxScore > 0) {
            array_walk($speciesScores, function(&$score) use ($maxScore) {
                $score = round(($score / $maxScore) * 100, 2);
            });
        }
        
        return [
            'recommended_species' => array_search(max($speciesScores), $speciesScores),
            'recommended_breed' => null,
            'trait_preferences' => $traitPreferences,
            'species_scores' => $speciesScores
        ];
    }
    
    private function calculateConfidenceScore(array $answers): float {
        $answeredQuestions = 0;
        $totalQuestions = 0;
        
        foreach ($answers as $section) {
            if (is_array($section)) {
                foreach ($section as $value) {
                    $totalQuestions++;
                    if (!empty($value)) {
                        $answeredQuestions++;
                    }
                }
            }
        }
        
        return $totalQuestions > 0 ? 
            round(($answeredQuestions / $totalQuestions) * 100, 2) : 0;
    }
    
    private function findMatchingPets(array $analysis): array {
        $filters = [
            'species' => $analysis['recommended_species'],
            'breed' => $analysis['recommended_breed'] ?? null,
            'traits' => array_map(function($trait) {
                return [
                    'trait' => $trait['trait'],
                    'value' => $trait['value']
                ];
            }, $analysis['trait_preferences'])
        ];
        
        error_log("Finding matching pets with filters: " . json_encode($filters, JSON_PRETTY_PRINT));
        return $this->petModel->findAllWithTraits($filters);
    }
    
    private function getBreedRecommendation(string $species, array $traits, array $answers): ?string {
        // Implement breed recommendation logic based on species and traits
        // This would be expanded based on specific breed characteristics
        return null; // Placeholder
    }
    
    private function convertTraitPreferencesToFilters(array $preferences): array {
        $filters = [];
        foreach ($preferences as $trait => $value) {
            $filters[] = [
                'trait' => $trait,
                'value' => $value
            ];
        }
        return $filters;
    }
}
