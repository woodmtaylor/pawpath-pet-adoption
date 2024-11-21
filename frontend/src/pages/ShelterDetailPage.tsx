import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { useToast } from '@/hooks/use-toast';
import { ChevronLeft, MapPin, Phone, Mail, PawPrint } from 'lucide-react';
import { PetCard } from '@/components/pets/PetCard';
import api from '@/lib/axios';

interface Shelter {
  shelter_id: number;
  name: string;
  address: string;
  phone: string;
  email: string;
  is_no_kill: boolean;
  total_pets: number;
  active_applications: number;
}

interface Pet {
  pet_id: number;
  name: string;
  species: string;
  breed: string;
  age: number;
  gender: string;
  description: string;
  shelter_name: string;
  images?: Array<{
    image_id: number;
    url: string;
    is_primary: boolean;
  }>;
  traits?: {
    [category: string]: string[];
  };
}

export default function ShelterDetailPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { toast } = useToast();
  const [shelter, setShelter] = useState<Shelter | null>(null);
  const [pets, setPets] = useState<Pet[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchShelterDetails();
  }, [id]);

  const fetchShelterDetails = async () => {
    try {
      setLoading(true);
      // Fetch shelter details
      const shelterResponse = await api.get(`/shelters/${id}`);
      if (!shelterResponse.data.success) {
        throw new Error(shelterResponse.data.error || 'Failed to fetch shelter details');
      }
      setShelter(shelterResponse.data.data);

      // Fetch shelter's pets
      const petsResponse = await api.get('/pets', {
        params: {
          shelter_id: id
        }
      });
      if (petsResponse.data.success) {
        setPets(petsResponse.data.data.items);
      }
    } catch (error: any) {
      console.error('Error fetching shelter details:', error);
      toast({
        variant: "destructive",
        title: "Error",
        description: error.message || "Failed to load shelter details",
      });
      navigate('/shelters');
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="container mx-auto p-4">
        <div className="flex justify-center items-center min-h-[400px]">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
        </div>
      </div>
    );
  }

  if (!shelter) {
    return (
      <div className="container mx-auto p-4">
        <Card>
          <CardContent className="p-6 text-center">
            <h3 className="text-lg font-semibold mb-2">Shelter not found</h3>
            <p className="text-muted-foreground mb-4">
              The shelter you're looking for doesn't exist or has been removed.
            </p>
            <Button onClick={() => navigate('/shelters')}>
              View All Shelters
            </Button>
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className="container mx-auto p-4">
      <Button
        variant="ghost"
        className="mb-4"
        onClick={() => navigate('/shelters')}
      >
        <ChevronLeft className="mr-2 h-4 w-4" />
        Back to Shelters
      </Button>

      <div className="grid gap-6">
        {/* Shelter Info Card */}
        <Card>
          <CardHeader>
            <div className="flex justify-between items-start">
              <div>
                <CardTitle className="text-3xl">{shelter.name}</CardTitle>
                <CardDescription className="flex items-center mt-2">
                  <MapPin className="h-4 w-4 mr-2" />
                  {shelter.address}
                </CardDescription>
              </div>
              {shelter.is_no_kill && (
                <Badge variant="success">No-Kill Shelter</Badge>
              )}
            </div>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <div className="flex items-center text-muted-foreground">
                    <Phone className="h-4 w-4 mr-2" />
                    {shelter.phone}
                  </div>
                  <div className="flex items-center text-muted-foreground">
                    <Mail className="h-4 w-4 mr-2" />
                    {shelter.email}
                  </div>
                </div>
                <div className="space-y-2">
                  <div className="flex items-center text-muted-foreground">
                    <PawPrint className="h-4 w-4 mr-2" />
                    {shelter.total_pets} pets available
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Available Pets Section */}
        <Card>
          <CardHeader>
            <CardTitle>Available Pets</CardTitle>
            <CardDescription>
              Pets currently available for adoption at {shelter.name}
            </CardDescription>
          </CardHeader>
          <CardContent>
            {pets.length === 0 ? (
              <div className="text-center py-8">
                <PawPrint className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                <h3 className="text-lg font-semibold mb-2">No Pets Available</h3>
                <p className="text-muted-foreground">
                  This shelter currently has no pets listed for adoption.
                </p>
              </div>
            ) : (
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {pets.map((pet) => (
                  <PetCard
                    key={pet.pet_id}
                    pet={pet}
                    onClick={() => navigate(`/pets/${pet.pet_id}`)}
                  />
                ))}
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
