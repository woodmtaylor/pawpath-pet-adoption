import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { PetCard } from '@/components/pets/PetCard';
import { useToast } from '@/hooks/use-toast';
import { Search, PawPrint, Plus } from 'lucide-react';
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
    application_count: number;
    images?: Array<{
        image_id: number;
        url: string;
        is_primary: boolean;
    }>;
}

export default function ShelterManagement() {
    const [pets, setPets] = useState<Pet[]>([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    const navigate = useNavigate();
    const { toast } = useToast();

    useEffect(() => {
        fetchPets();
    }, []);

    const fetchPets = async () => {
        try {
            setLoading(true);
            const response = await api.get('/shelter/pets');
            if (response.data.success) {
                setPets(response.data.data);
            }
        } catch (error: any) {
            toast({
                variant: "destructive",
                title: "Error",
                description: "Failed to load pets"
            });
        } finally {
            setLoading(false);
        }
    };

    const filteredPets = pets.filter(pet =>
        pet.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        pet.breed.toLowerCase().includes(searchTerm.toLowerCase()) ||
        pet.species.toLowerCase().includes(searchTerm.toLowerCase())
    );

    if (loading) {
        return (
            <div className="flex justify-center items-center min-h-screen">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>
        );
    }

    return (
        <div className="container mx-auto p-6">
            <Card>
                <CardHeader>
                    <div className="flex justify-between items-center">
                        <div>
                            <CardTitle>Manage Pets</CardTitle>
                            <CardDescription>
                                Add and manage your shelter's pets
                            </CardDescription>
                        </div>
                        <Button onClick={() => navigate('/shelter/pets/new')}>
                            <Plus className="mr-2 h-4 w-4" />
                            Add New Pet
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div className="mb-6">
                        <div className="relative">
                            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input
                                placeholder="Search pets..."
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                className="pl-9"
                            />
                        </div>
                    </div>

                    {filteredPets.length === 0 ? (
                        <div className="text-center py-12">
                            <PawPrint className="mx-auto h-12 w-12 text-muted-foreground" />
                            <h3 className="mt-4 text-lg font-semibold">No Pets Found</h3>
                            <p className="text-muted-foreground">
                                {pets.length === 0
                                    ? "Start by adding your first pet"
                                    : "No pets match your search"}
                            </p>
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {filteredPets.map((pet) => (
                                <div key={pet.pet_id} className="relative group">
                                    <PetCard
                                        pet={pet}
                                        onClick={() => navigate(`/pets/${pet.pet_id}`)}
                                    />
                                    <div className="absolute top-2 right-2 space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <Button
                                            variant="secondary"
                                            size="sm"
                                            onClick={(e) => {
                                                e.stopPropagation();
                                                navigate(`/shelter/pets/${pet.pet_id}/edit`);
                                            }}
                                        >
                                            Edit
                                        </Button>
                                        {pet.application_count > 0 && (
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={(e) => {
                                                    e.stopPropagation();
                                                    navigate(`/shelter/pets/${pet.pet_id}/applications`);
                                                }}
                                            >
                                                {pet.application_count} Applications
                                            </Button>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </CardContent>
            </Card>
        </div>
    );
}
