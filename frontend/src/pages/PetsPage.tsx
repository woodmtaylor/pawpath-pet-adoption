import { useState, useEffect } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { PetFilters } from '@/components/pets/PetFilters';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Search, PawPrint, MapPin } from 'lucide-react';
import api from '@/lib/axios';

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
      setLoading(true);
      setError(null);

      try {
        const params = new URLSearchParams();
        if (searchTerm) params.set('search', searchTerm);
        if (filters.species) params.set('species', filters.species);
        if (filters.breed) params.set('breed', filters.breed);
        if (filters.ageMin) params.set('age_min', filters.ageMin.toString());
        if (filters.ageMax) params.set('age_max', filters.ageMax.toString());
        if (filters.gender) params.set('gender', filters.gender);
        if (filters.size) params.set('size', filters.size);
        if (filters.goodWith?.length) params.set('good_with', filters.goodWith.join(','));
        if (filters.traits?.length) params.set('traits', filters.traits.join(','));

        setSearchParams(params);

        const response = await api.get(`/pets?${params.toString()}`);
        setPets(response.data);
      } catch (err: any) {
        setError(err.response?.data?.error || 'Failed to fetch pets');
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
            <Card
              key={pet.pet_id}
              className="cursor-pointer hover:shadow-lg transition-shadow"
              onClick={() => navigate(`/pets/${pet.pet_id}`)}
            >
              <div className="aspect-square relative bg-muted">
                {pet.images?.[0] ? (
                  <img
                    src={pet.images[0]}
                    alt={pet.name}
                    className="object-cover w-full h-full"
                  />
                ) : (
                  <div className="w-full h-full flex items-center justify-center">
                    <PawPrint className="h-12 w-12 text-muted-foreground" />
                  </div>
                )}
              </div>
              
              <CardHeader>
                <CardTitle>{pet.name}</CardTitle>
                <CardDescription>
                  {pet.breed} • {pet.age} years old • {pet.gender}
                </CardDescription>
              </CardHeader>

              <CardContent>
                <p className="text-sm text-muted-foreground line-clamp-2 mb-4">
                  {pet.description}
                </p>

                <div className="flex flex-wrap gap-2 mb-4">
                  {Array.isArray(pet.traits) ? (
                    pet.traits.map((trait) => (
                      <Badge
                        key={trait}
                        variant="secondary"
                        className="text-xs"
                      >
                        {trait}
                      </Badge>
                    ))
                  ) : null}
                </div>

                <div className="flex items-center text-sm text-muted-foreground">
                  <MapPin className="h-4 w-4 mr-1" />
                  {pet.shelter_name}
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      )}
    </div>
  );
}

export default PetsPage;
