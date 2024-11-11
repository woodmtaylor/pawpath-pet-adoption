import { useState, useEffect } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { PetFilters } from '@/components/pets/PetFilters';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Search, PawPrint, MapPin } from 'lucide-react';
import api from '@/lib/axios';
import { Pet, ApiResponse } from '@/types/api';
import { useToast } from '@/hooks/use-toast';

interface Pet {
  pet_id: number;
  name: string;
  species: string;
  breed: string;
  age: number;
  gender: string;
  description: string;
  shelter_name: string;
  traits: string[];
  images?: string[];
}

interface PetFiltersState {
  species?: string;
  breed?: string;
  ageMin?: number;
  ageMax?: number;
  gender?: string;
  size?: string;
  goodWith?: string[];
  traits?: string[];
}

function PetsPage() {
  const navigate = useNavigate();
  const [searchParams, setSearchParams] = useSearchParams();
  const [pets, setPets] = useState<Pet[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [searchTerm, setSearchTerm] = useState(searchParams.get('search') || '');
  const [filters, setFilters] = useState<PetFiltersState>({});
  const [availableTraits, setAvailableTraits] = useState<string[]>([]);

  useEffect(() => {
    const fetchTraits = async () => {
      try {
        const response = await api.get('/pet-traits');
        setAvailableTraits(response.data.map((trait: any) => trait.trait_name));
      } catch (err) {
        console.error('Failed to fetch traits:', err);
      }
    };

    fetchTraits();
  }, []);

  useEffect(() => {
    const fetchPets = async () => {
      try {
          setLoading(true);
          setError(null);
          
          const params = Object.entries(filters)
              .filter(([_, value]) => value !== '')
              .reduce((acc, [key, value]) => ({
                  ...acc,
                  [key]: value
              }), {});

          const response = await api.get<ApiResponse<Pet[]>>('/pets', { params });
          setPets(response.data.data || []);
      } catch (err: any) {
          const errorMessage = err.response?.data?.error || 'Failed to fetch pets';
          setError(errorMessage);
          toast({
              variant: "destructive",
              title: "Error",
              description: errorMessage,
          });
      } finally {
          setLoading(false);
      }
  };
    const timeoutId = setTimeout(fetchPets, 300);
    return () => clearTimeout(timeoutId);
  }, [searchTerm, filters, setSearchParams]);

  const handleSearch = (event: React.FormEvent) => {
    event.preventDefault();
  };

  const handleFilterChange = (newFilters: PetFiltersState) => {
    setFilters(newFilters);
  };

  return (
    <div className="container mx-auto p-4">
      <div className="mb-8 space-y-4">
        <div>
          <h1 className="text-3xl font-bold mb-2">Find Your Perfect Companion</h1>
          <p className="text-muted-foreground">
            Browse available pets or use filters to narrow your search
          </p>
        </div>

        <div className="flex flex-col sm:flex-row gap-4">
          <form onSubmit={handleSearch} className="flex-1">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Search by name, breed, or description..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="pl-9"
              />
            </div>
          </form>
          <PetFilters
            onFiltersChange={handleFilterChange}
            initialFilters={filters}
            availableTraits={availableTraits}
          />
        </div>
      </div>

      {error && (
        <Card className="mb-8">
          <CardContent className="p-6">
            <p className="text-red-500">{error}</p>
          </CardContent>
        </Card>
      )}

      {loading ? (
        <div className="flex justify-center items-center min-h-[400px]">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
        </div>
      ) : pets.length === 0 ? (
        <Card>
          <CardContent className="p-6 text-center">
            <PawPrint className="h-12 w-12 mx-auto mb-4 text-muted-foreground" />
            <h3 className="text-lg font-semibold mb-2">No Pets Found</h3>
            <p className="text-muted-foreground">
              Try adjusting your filters or search terms
            </p>
          </CardContent>
        </Card>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {pets.map((pet) => (
              <Card key={pet.pet_id} className="overflow-hidden">
                  <CardHeader>
                      <CardTitle>{pet.name}</CardTitle>
                      <CardDescription>
                          {pet.breed} • {pet.gender} • {pet.age} years old
                      </CardDescription>
                  </CardHeader>
                  <CardContent>
                      <p className="text-muted-foreground mb-4">{pet.description}</p>
                      
                      {/* Traits */}
                      {Object.entries(pet.traits || {}).map(([category, traits]) => (
                          <div key={category} className="mb-2">
                              <h4 className="text-sm font-medium text-muted-foreground">
                                  {category}:
                              </h4>
                              <div className="flex flex-wrap gap-1">
                                  {traits.map((trait) => (
                                      <span
                                          key={trait}
                                          className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary"
                                      >
                                          {trait}
                                      </span>
                                  ))}
                              </div>
                          </div>
                      ))}
                  </CardContent>
              </Card>
          ))}
        </div>
      )}
    </div>
  );
}

export default PetsPage;
