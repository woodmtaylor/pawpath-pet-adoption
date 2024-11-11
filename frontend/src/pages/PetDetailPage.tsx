import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ChevronLeft, Heart, MapPin, Phone, Mail, Calendar, Info, PawPrint } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { useAuth } from '@/contexts/AuthContext';
import api from '@/lib/axios';

interface Pet {
  pet_id: number;
  name: string;
  species: string;
  breed: string;
  age: number;
  gender: string;
  description: string;
  images: string[];
  traits: {
    [category: string]: string[];
  };
  shelter: {
    name: string;
    address: string;
    phone: string;
    email: string;
  };
}

function PetDetailPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { isAuthenticated } = useAuth();
  const [pet, setPet] = useState<Pet | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [activeImage, setActiveImage] = useState(0);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchPet = async () => {
      try {
        const response = await api.get(`/pets/${id}`);
        setPet(response.data);
      } catch (err: any) {
        setError(err.response?.data?.error || 'Failed to load pet details');
      } finally {
        setIsLoading(false);
      }
    };

    fetchPet();
  }, [id]);

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

  const handleAdopt = () => {
    if (!isAuthenticated) {
      navigate('/login', { state: { from: `/pets/${id}` } });
      return;
    }
    navigate(`/adopt/${id}`);
  };

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
              <img
                src={pet.images[activeImage]}
                alt={pet.name}
                className="object-cover w-full h-full"
              />
            ) : (
              <div className="w-full h-full flex items-center justify-center">
                <PawPrint className="h-12 w-12 text-muted-foreground" />
              </div>
            )}
          </div>
          
          {pet.images && pet.images.length > 1 && (
            <div className="grid grid-cols-4 gap-2">
              {pet.images.map((image, index) => (
                <button
                  key={index}
                  onClick={() => setActiveImage(index)}
                  className={`aspect-square rounded-md overflow-hidden border-2 ${
                    index === activeImage ? 'border-primary' : 'border-transparent'
                  }`}
                >
                  <img
                    src={image}
                    alt={`${pet.name} - view ${index + 1}`}
                    className="object-cover w-full h-full"
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

          {/* Shelter Information */}
          <Card>
            <CardContent className="p-6">
              <h2 className="text-xl font-semibold mb-3">Available at</h2>
              <div className="space-y-2">
                <p className="flex items-center">
                  <MapPin className="mr-2 h-4 w-4" />
                  {pet.shelter.name}
                </p>
                <p className="flex items-center">
                  <Info className="mr-2 h-4 w-4" />
                  {pet.shelter.address}
                </p>
                <p className="flex items-center">
                  <Phone className="mr-2 h-4 w-4" />
                  {pet.shelter.phone}
                </p>
                <p className="flex items-center">
                  <Mail className="mr-2 h-4 w-4" />
                  {pet.shelter.email}
                </p>
              </div>
            </CardContent>
          </Card>

          {/* Action Buttons */}
          <div className="flex gap-4">
            <Button onClick={handleAdopt} className="flex-1">
              Start Adoption Process
            </Button>
            <Button variant="outline" className="w-auto">
              <Heart className="h-4 w-4" />
            </Button>
          </div>
        </div>
      </div>
    </div>
  );
}

export default PetDetailPage;
