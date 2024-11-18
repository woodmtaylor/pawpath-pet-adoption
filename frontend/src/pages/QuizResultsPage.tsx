import { useEffect, useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { PawPrint, Heart, ArrowRight } from 'lucide-react';

interface Pet {
  pet_id: number;
  name: string;
  species: string;
  breed: string;
  age: number;
  gender: string;
  description: string;
  shelter_name: string;
  traits: {
    [category: string]: string[];
  };
  images?: Array<{
    image_id: number;
    url: string;
    is_primary: boolean;
  }>;
  match_score?: number;
}

interface QuizResults {
  recommended_species: string;
  trait_preferences?: Array<{ trait: string; value: string }>;
  recommended_traits?: string[];
  matching_pets: Pet[];
  confidence_score: number;
}

function QuizResultsPage() {
  const location = useLocation();
  const navigate = useNavigate();
  const [results, setResults] = useState<QuizResults | null>(null);

  useEffect(() => {
    if (!location.state?.results) {
      navigate('/quiz');
      return;
    }

    // Transform the data if needed
    const rawResults = location.state.results;
    const transformedResults = {
      ...rawResults,
      // Convert trait_preferences to recommended_traits if needed
      recommended_traits: rawResults.recommended_traits || 
        rawResults.trait_preferences?.map(t => t.trait) ||
        []
    };
    
    setResults(transformedResults);
  }, [location.state, navigate]);

  // Show loading state while results are being set
  if (!results) {
    return (
      <div className="container max-w-4xl mx-auto p-4">
        <Card>
          <CardContent className="p-6 text-center">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div>
            <p className="mt-4">Loading your results...</p>
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className="container max-w-4xl mx-auto p-4">
      <div className="mb-8 text-center">
        <h1 className="text-3xl font-bold mb-2">Your Perfect Pet Match Results</h1>
        <p className="text-muted-foreground">
          Based on your responses, we've found some great matches for you!
        </p>
      </div>

      <div className="grid gap-6 mb-8">
        <Card>
          <CardHeader>
            <CardTitle>Match Summary</CardTitle>
            <CardDescription>
              Our algorithm is {results.confidence_score}% confident in these recommendations
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4">
              <div>
                <h3 className="font-medium mb-2">Recommended Pet Type</h3>
                <Badge variant="secondary" className="text-lg">
                  <PawPrint className="mr-2 h-4 w-4" />
                  {results.recommended_species}
                </Badge>
              </div>
              
              {(results.recommended_traits?.length || results.trait_preferences?.length) > 0 && (
                <div>
                  <h3 className="font-medium mb-2">Key Traits That Match You</h3>
                  <div className="flex flex-wrap gap-2">
                    {results.recommended_traits?.map((trait) => (
                      <Badge key={trait} variant="outline">
                        {trait}
                      </Badge>
                    )) || 
                    results.trait_preferences?.map((trait) => (
                      <Badge key={trait.trait} variant="outline">
                        {trait.trait}
                      </Badge>
                    ))}
                  </div>
                </div>
              )}
            </div>
          </CardContent>
        </Card>

            {results.matching_pets?.length > 0 && (
                <Card>
                    <CardHeader>
                        <CardTitle>Your Top Matches</CardTitle>
                        <CardDescription>
                            These pets match your lifestyle and preferences
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-6 md:grid-cols-2">
                            {results.matching_pets.map((pet) => (
                                <Card key={pet.pet_id} className="overflow-hidden">
                                    <div className="aspect-square relative">
                                        {pet.images && pet.images.length > 0 ? (
                                            <img
                                                src={pet.images[0].url}
                                                alt={pet.name}
                                                className="object-cover w-full h-full"
                                            />
                                        ) : (
                                            <div className="w-full h-full flex items-center justify-center bg-muted">
                                                <PawPrint className="h-12 w-12 text-muted-foreground" />
                                            </div>
                                        )}
                                        {pet.match_score && (
                                            <Badge 
                                                className="absolute top-2 right-2"
                                                variant="secondary"
                                            >
                                                {pet.match_score}% Match
                                            </Badge>
                                        )}
                                    </div>
                                    <CardContent className="p-4">
                                        <div className="flex justify-between items-start mb-2">
                                            <div>
                                                <h3 className="font-semibold text-lg">{pet.name}</h3>
                                                <p className="text-sm text-muted-foreground">
                                                    {pet.breed} • {pet.age} years old
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <p className="text-sm mb-4">{pet.description}</p>
                                        
                                        {pet.traits && Object.keys(pet.traits).length > 0 && (
                                            <div className="flex flex-wrap gap-2 mb-4">
                                                {Object.entries(pet.traits).map(([category, traits]) => (
                                                    traits.map((trait) => (
                                                        <Badge 
                                                            key={`${category}-${trait}`}
                                                            variant="outline"
                                                        >
                                                            {trait}
                                                        </Badge>
                                                    ))
                                                ))}
                                            </div>
                                        )}
                                        
                                        <p className="text-sm text-muted-foreground mb-4">
                                            Available at {pet.shelter_name}
                                        </p>
                                        
                                        <Button 
                                            className="w-full"
                                            onClick={() => navigate(`/pets/${pet.pet_id}`)}
                                        >
                                            <Heart className="mr-2 h-4 w-4" />
                                            Meet {pet.name}
                                        </Button>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            )}
      </div>

      <div className="flex justify-center gap-4">
        <Button variant="outline" onClick={() => navigate('/pets')}>
          Browse All Pets
        </Button>
        <Button onClick={() => navigate('/quiz')}>
          Retake Quiz
          <ArrowRight className="ml-2 h-4 w-4" />
        </Button>
      </div>
    </div>
  );
}

export default QuizResultsPage;
