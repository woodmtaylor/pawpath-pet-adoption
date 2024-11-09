import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '@/lib/axios';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { Search, Filter } from 'lucide-react';

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
}

function PetsPage() {
  const navigate = useNavigate();
  const [pets, setPets] = useState<Pet[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [filters, setFilters] = useState({
    species: '',
    breed: '',
    age_min: '',
    age_max: '',
    gender: ''
  });

  useEffect(() => {
    fetchPets();
  }, [filters]);

  const fetchPets = async () => {
    try {
      setLoading(true);
      // Build query params from non-empty filters
      const params = Object.entries(filters)
        .filter(([_, value]) => value !== '')
        .reduce((acc, [key, value]) => ({
          ...acc,
          [key]: value
        }), {});

      const response = await api.get('/pets', { params });
      setPets(response.data);
    } catch (err: any) {
      setError(err.response?.data?.error || 'Failed to fetch pets');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="container mx-auto p-4">
      <div className="mb-8">
        <h1 className="text-3xl font-bold mb-4">Find Your Perfect Companion</h1>
        
        {/* Filters */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div>
            <Select
              value={filters.species}
              onValueChange={(value) => setFilters(f => ({ ...f, species: value }))}
            >
              <option value="">All Species</option>
              <option value="Dog">Dogs</option>
              <option value="Cat">Cats</option>
              <option value="Bird">Birds</option>
              <option value="Rabbit">Rabbits</option>
            </Select>
          </div>
          
          <div>
            <Input
              placeholder="Search by breed..."
              value={filters.breed}
              onChange={(e) => setFilters(f => ({ ...f, breed: e.target.value }))}
            />
          </div>
          
          <div>
            <Select
              value={filters.gender}
              onValueChange={(value) => setFilters(f => ({ ...f, gender: value }))}
            >
              <option value="">Any Gender</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </Select>
          </div>
          
          <div>
            <Button 
              variant="outline" 
              className="w-full"
              onClick={() => setFilters({
                species: '',
                breed: '',
                age_min: '',
                age_max: '',
                gender: ''
              })}
            >
              <Filter className="mr-2 h-4 w-4" />
              Clear Filters
            </Button>
          </div>
        </div>
        
        {error && (
          <div className="bg-red-50 text-red-500 p-4 rounded-lg mb-4">
            {error}
          </div>
        )}
      </div>

      {loading ? (
        <div className="flex justify-center items-center min-h-[400px]">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {pets.map((pet) => (
            <Card key={pet.pet_id} className="overflow-hidden">
              <CardHeader>
                <CardTitle>{pet.name}</CardTitle>
                <CardDescription>{pet.breed} • {pet.gender} • {pet.age} years old</CardDescription>
              </CardHeader>
              <CardContent>
                <p className="text-muted-foreground mb-4">{pet.description}</p>
                
                {/* Traits */}
                {Object.entries(pet.traits).map(([category, traits]) => (
                  <div key={category} className="mb-2">
                    <h4 className="text-sm font-medium text-muted-foreground">{category}:</h4>
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
                
                <div className="mt-4">
                  <p className="text-sm text-muted-foreground">
                    Available at: {pet.shelter_name}
                  </p>
                </div>
                
                <Button 
                  className="w-full mt-4"
                  onClick={() => navigate(`/pets/${pet.pet_id}`)}
                >
                  Learn More
                </Button>
              </CardContent>
            </Card>
          ))}
        </div>
      )}
    </div>
  );
}

export default PetsPage;
