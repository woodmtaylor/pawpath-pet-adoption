import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ChevronLeft, Heart, MapPin, Phone, Mail, Calendar, Info, PawPrint, ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { useAuth } from '@/contexts/AuthContext';
import { useToast } from '@/hooks/use-toast';
import { Pet } from '@/types/api';
import api from '@/lib/axios';

function PetDetailPage() {
    const { id } = useParams();
    const navigate = useNavigate();
    const { isAuthenticated } = useAuth();
    const { toast } = useToast();
    const [pet, setPet] = useState<Pet | null>(null);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [isFavorited, setIsFavorited] = useState(false);
    const [isToggling, setIsToggling] = useState(false);
    const [currentImageIndex, setCurrentImageIndex] = useState(0);  // Add this line

    useEffect(() => {
        const fetchPet = async () => {
            try {
                setIsLoading(true);
                const response = await api.get(`/pets/${id}`);
                if (response.data.success) {
                    setPet(response.data.data);
                    setCurrentImageIndex(0); // Reset image index when pet data changes
                    
                    if (isAuthenticated) {
                        const favResponse = await api.get(`/pets/${id}/favorite`);
                        setIsFavorited(favResponse.data.data.is_favorited);
                    }
                } else {
                    throw new Error(response.data.error || 'Failed to load pet details');
                }
            } catch (err: any) {
                const errorMsg = err.response?.data?.error || err.message || 'Failed to load pet details';
                setError(errorMsg);
                toast({
                    variant: "destructive",
                    title: "Error",
                    description: errorMsg,
                });
            } finally {
                setIsLoading(false);
            }
        };

        fetchPet();
    }, [id, isAuthenticated, toast]);

    const navigateImage = (direction: 'next' | 'prev') => {
        if (!pet?.images?.length) return;
        
        setCurrentImageIndex(prev => {
            if (direction === 'next') {
                return prev === pet.images!.length - 1 ? 0 : prev + 1;
            } else {
                return prev === 0 ? pet.images!.length - 1 : prev - 1;
            }
        });
    };

    const handleFavoriteToggle = async () => {
        if (!isAuthenticated) {
            navigate('/login', { state: { from: `/pets/${id}` } });
            return;
        }

        try {
            setIsToggling(true);
            if (isFavorited) {
                await api.delete(`/pets/${id}/favorite`);
                toast({
                    title: "Removed from favorites",
                    description: "Pet has been removed from your favorites",
                });
            } else {
                await api.post(`/pets/${id}/favorite`);
                toast({
                    title: "Added to favorites",
                    description: "Pet has been added to your favorites",
                });
            }
            setIsFavorited(!isFavorited);
        } catch (err) {
            toast({
                variant: "destructive",
                title: "Error",
                description: "Failed to update favorites",
            });
        } finally {
            setIsToggling(false);
        }
    };

    const handleAdopt = () => {
        if (!isAuthenticated) {
            navigate('/login', { state: { from: `/pets/${id}` } });
            return;
        }
        navigate(`/adopt/${id}`);
    };

    if (isLoading) {
        return (
            <div className="container max-w-4xl mx-auto p-4">
                <div className="h-96 flex items-center justify-center">
                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
                </div>
            </div>
        );
    }

    if (error || !pet) {
        return (
            <div className="container max-w-4xl mx-auto p-4">
                <Card>
                    <CardContent className="p-6">
                        <h2 className="text-lg font-semibold mb-2">Error</h2>
                        <p className="text-muted-foreground">{error || 'Pet not found'}</p>
                        <Button
                            className="mt-4"
                            variant="outline"
                            onClick={() => navigate('/pets')}
                        >
                            <ChevronLeft className="mr-2 h-4 w-4" />
                            Back to Pets
                        </Button>
                    </CardContent>
                </Card>
            </div>
        );
    }

    return (
        <div className="container max-w-4xl mx-auto p-4">
      <Button
        variant="ghost"
        className="mb-4"
        onClick={() => navigate('/pets')}
      >
        <ChevronLeft className="mr-2 h-4 w-4" />
        Back to Pets
      </Button>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
      {/* Image Gallery */}
      <div className="space-y-4">
          <div className="aspect-square relative bg-muted rounded-lg overflow-hidden">
              {pet.images && pet.images.length > 0 ? (
                  <>
                      <img
                          src={pet.images[currentImageIndex].url}
                          alt={`${pet.name} - ${pet.breed}`}
                          className="w-full h-full object-contain"  // Changed from object-cover
                          style={{
                              backgroundColor: 'white',  // Add white background
                              maxHeight: '600px'        // Limit maximum height
                          }}
                      />
                      {pet.images.length > 1 && (
                          <>
                              <Button
                                  variant="secondary"
                                  size="icon"
                                  className="absolute left-2 top-1/2 -translate-y-1/2"
                                  onClick={() => navigateImage('prev')}
                              >
                                  <ChevronLeft className="h-4 w-4" />
                              </Button>
                              <Button
                                  variant="secondary"
                                  size="icon"
                                  className="absolute right-2 top-1/2 -translate-y-1/2"
                                  onClick={() => navigateImage('next')}
                              >
                                  <ChevronRight className="h-4 w-4" />
                              </Button>
                          </>
                      )}
                  </>
              ) : (
                  <div className="w-full h-full flex items-center justify-center">
                      <PawPrint className="h-12 w-12 text-muted-foreground" />
                  </div>
              )}
          </div>

                  {/* Thumbnail Grid */}
                  {pet.images && pet.images.length > 1 && (
                      <div className="grid grid-cols-4 gap-2">
                          {pet.images.map((image, index) => (
                              <button
                                  key={image.image_id}
                                  className={`aspect-square rounded-md overflow-hidden border-2 
                                      ${currentImageIndex === index ? 'border-primary' : 'border-transparent'}`}
                                  onClick={() => setCurrentImageIndex(index)}
                              >
                                  <img
                                      src={image.url}
                                      alt={`${pet.name} ${index + 1}`}
                                      className="w-full h-full object-contain bg-white"
                                  />
                              </button>
                          ))}
                      </div>
                  )}
                </div>
        {/* Pet Details */}
        <div className="space-y-6">
          <div>
            <h1 className="text-3xl font-bold mb-2">{pet.name}</h1>
            <div className="flex flex-wrap gap-2 mb-4">
              <Badge variant="secondary">{pet.species}</Badge>
              <Badge variant="secondary">{pet.breed}</Badge>
              <Badge variant="secondary">{pet.gender}</Badge>
              <Badge variant="secondary">{pet.age} years old</Badge>
            </div>
            <p className="text-muted-foreground">{pet.description}</p>
          </div>

          {/* Traits */}
          {pet.traits && Object.keys(pet.traits).length > 0 && (
            <div>
              <h2 className="text-xl font-semibold mb-3">Characteristics</h2>
              {Object.entries(pet.traits).map(([category, traits]) => (
                <div key={category} className="mb-4">
                  <h3 className="text-sm font-medium text-muted-foreground mb-2">
                    {category}:
                  </h3>
                  <div className="flex flex-wrap gap-2">
                    {traits.map((trait) => (
                      <Badge key={trait} variant="outline">
                        {trait}
                      </Badge>
                    ))}
                  </div>
                </div>
              ))}
            </div>
          )}

          {/* Shelter Information */}
          <Card>
            <CardContent className="p-6">
              <h2 className="text-xl font-semibold mb-3">Available at</h2>
              <div className="space-y-2">
                <p className="flex items-center">
                  <MapPin className="mr-2 h-4 w-4" />
                  {pet.shelter_name}
                </p>
              </div>
            </CardContent>
          </Card>

          {/* Action Buttons */}
          <div className="flex gap-4">
            <Button onClick={handleAdopt} className="flex-1">
              Start Adoption Process
            </Button>
            <Button 
              variant="outline" 
              className={`w-auto ${isFavorited ? 'bg-primary text-primary-foreground' : ''}`}
              onClick={handleFavoriteToggle}
              disabled={isToggling}
            >
              <Heart className={`h-4 w-4 ${isFavorited ? 'fill-current' : ''}`} />
            </Button>
          </div>
        </div>
      </div>
    </div>
  );
}

export default PetDetailPage;
