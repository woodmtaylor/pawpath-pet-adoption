import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Label } from '@/components/ui/label';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useToast } from '@/hooks/use-toast';
import api from '@/lib/axios';

// Quiz sections and questions
const quizData = {
  sections: [
    {
      id: 'living_situation',
      title: 'Living Situation',
      questions: [
        {
          id: 'living_space',
          text: 'What type of home do you live in?',
          options: [
            { id: 'apartment_small', label: 'Small Apartment' },
            { id: 'apartment_large', label: 'Large Apartment' },
            { id: 'house_small', label: 'Small House' },
            { id: 'house_large', label: 'Large House with Yard' }
          ]
        },
        {
          id: 'outdoor_access',
          text: 'Do you have access to outdoor space?',
          options: [
            { id: 'private_yard', label: 'Private Yard' },
            { id: 'shared_yard', label: 'Shared Yard/Garden' },
            { id: 'nearby_park', label: 'Nearby Park' },
            { id: 'no_outdoor', label: 'Limited Outdoor Access' }
          ]
        }
      ]
    },
    {
      id: 'lifestyle',
      title: 'Your Lifestyle',
      questions: [
        {
          id: 'activity_level',
          text: 'How would you describe your activity level?',
          options: [
            { id: 'very_active', label: 'Very Active (Daily Exercise)' },
            { id: 'moderate', label: 'Moderately Active' },
            { id: 'somewhat', label: 'Somewhat Active' },
            { id: 'sedentary', label: 'Mostly Sedentary' }
          ]
        },
        {
          id: 'time_available',
          text: 'How much time can you dedicate to pet care daily?',
          options: [
            { id: 'very_limited', label: 'Less than 1 hour' },
            { id: 'limited', label: '1-2 hours' },
            { id: 'moderate', label: '2-4 hours' },
            { id: 'extensive', label: '4+ hours' }
          ]
        }
      ]
    }
    // Add more sections as needed
  ]
};

interface QuizResults {
  quiz_id: number;
  recommendations: {
    species: string;
    breed: string | null;
    traits: Array<{ trait: string; value: string }>;
  };
  confidence_score: number;
  matching_pets: Array<{
    pet_id: number;
    name: string;
    species: string;
    breed: string;
    match_score: number;
    // Add other pet properties as needed
  }>;
}

function QuizPage() {
  const navigate = useNavigate();
  const { toast } = useToast();
  const [currentSection, setCurrentSection] = useState(0);
  const [answers, setAnswers] = useState<Record<string, string>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  const section = quizData.sections[currentSection];
  const progress = ((currentSection + 1) / quizData.sections.length) * 100;

  const handleAnswer = (questionId: string, value: string) => {
    setAnswers(prev => ({
      ...prev,
      [questionId]: value
    }));
  };

  const handleNext = () => {
    if (currentSection < quizData.sections.length - 1) {
      setCurrentSection(prev => prev + 1);
    } else {
      handleSubmit();
    }
  };

  const handlePrevious = () => {
    if (currentSection > 0) {
      setCurrentSection(prev => prev - 1);
    }
  };

  const handleSubmit = async () => {
    try {
      setIsSubmitting(true);
      
      // Format answers into sections for the API
      const formattedAnswers = Object.entries(answers).reduce((acc, [questionId, answer]) => {
        const section = quizData.sections.find(s => 
          s.questions.some(q => q.id === questionId)
        );
        if (section) {
          if (!acc[section.id]) {
            acc[section.id] = {};
          }
          acc[section.id][questionId] = answer;
        }
        return acc;
      }, {} as Record<string, Record<string, string>>);

      // Submit to the API
      const response = await api.post<{ success: boolean; data: QuizResults }>('/quiz/submit', {
        answers: formattedAnswers
      });

      if (response.data.success) {
        // Navigate to results page with the quiz results
        navigate('/quiz/results', { 
          state: { 
            results: response.data.data 
          }
        });
      } else {
        throw new Error('Failed to process quiz results');
      }
    } catch (error: any) {
      console.error('Failed to submit quiz:', error);
      toast({
        variant: "destructive",
        title: "Error",
        description: error.response?.data?.error || 'Failed to submit quiz. Please try again.',
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  const isLastSection = currentSection === quizData.sections.length - 1;
  const hasAnsweredCurrent = section.questions.every(q => answers[q.id]);

  return (
    <div className="container max-w-2xl mx-auto p-4">
      <Card>
        <CardHeader>
          <CardTitle>Pet Matching Quiz</CardTitle>
          <CardDescription>
            Section {currentSection + 1} of {quizData.sections.length}: {section.title}
          </CardDescription>
          <Progress value={progress} className="mt-2" />
        </CardHeader>

        <CardContent className="space-y-6">
          {section.questions.map((question) => (
            <div key={question.id} className="space-y-4">
              <h3 className="font-medium">{question.text}</h3>
              <RadioGroup
                value={answers[question.id] || ''}
                onValueChange={(value) => handleAnswer(question.id, value)}
              >
                {question.options.map((option) => (
                  <div key={option.id} className="flex items-center space-x-2">
                    <RadioGroupItem value={option.id} id={option.id} />
                    <Label htmlFor={option.id}>{option.label}</Label>
                  </div>
                ))}
              </RadioGroup>
            </div>
          ))}
        </CardContent>

        <CardFooter className="flex justify-between">
          <Button
            variant="outline"
            onClick={handlePrevious}
            disabled={currentSection === 0}
          >
            <ChevronLeft className="mr-2 h-4 w-4" />
            Previous
          </Button>

          <Button
            onClick={handleNext}
            disabled={!hasAnsweredCurrent || isSubmitting}
          >
            {isLastSection ? (
              isSubmitting ? 'Submitting...' : 'Submit'
            ) : (
              <>
                Next
                <ChevronRight className="ml-2 h-4 w-4" />
              </>
            )}
          </Button>
        </CardFooter>
      </Card>
    </div>
  );
}

export default QuizPage;
