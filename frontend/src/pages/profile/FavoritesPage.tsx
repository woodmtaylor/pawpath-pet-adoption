import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Heart, PawPrint } from 'lucide-react';
import { useToast } from '@/hooks/use-toast';
import { Pet } from '@/types/api';
import api from '@/lib/axios';
import { PetCard } from '@/components/pets/PetCard';

interface FavoritedPet {
  pet_id: number;
  name: string;
  species: string;
  breed: string;
  age: number;
  gender: string;
  description: string;
  shelter_name: string;
  favorited_at: string;
  traits?: {
    [category: string]: string[];
  };
}

export default function FavoritesPage() {
    const [favorites, setFavorites] = useState<FavoritedPet[]>([]);
    const [loading, setLoading] = useState(true);
    const navigate = useNavigate();
    const { toast } = useToast();

    useEffect(() => {
        fetchFavorites();
    }, []);

    const fetchFavorites = async () => {
        try {
            setLoading(true);
            const response = await api.get('/favorites');
            
            if (response.data.success) {
                setFavorites(response.data.data);
            } else {
                throw new Error(response.data.error || 'Failed to fetch favorites');
            }
        } catch (error: any) {
            console.error('Error fetching favorites:', error);
            toast({
                variant: "destructive",
                title: "Error",
                description: error.message || "Failed to load favorites",
            });
        } finally {
            setLoading(false);
        }
    };

    const handleRemoveFavorite = async (petId: number) => {
        try {
            await api.delete(`/pets/${petId}/favorite`);
            setFavorites(favorites.filter(pet => pet.pet_id !== petId));
            toast({
                title: "Success",
                description: "Pet removed from favorites",
            });
        } catch (error: any) {
            toast({
                variant: "destructive",
                title: "Error",
                description: "Failed to remove from favorites",
            });
        }
    };

    if (loading) {
        return (
            <div className="container max-w-4xl mx-auto p-4">
                <div className="flex justify-center items-center min-h-[400px]">
                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
                </div>
            </div>
        );
    }

    if (favorites.length === 0) {
        return (
            <div className="container max-w-4xl mx-auto p-4">
                <Card>
                    <CardContent className="flex flex-col items-center justify-center py-12">
                        <Heart className="h-12 w-12 text-muted-foreground mb-4" />
                        <h3 className="text-lg font-semibold mb-2">No Favorite Pets Yet</h3>
                        <p className="text-muted-foreground text-center mb-4">
                            Browse our available pets and add some to your favorites!
                        </p>
                        <Button onClick={() => navigate('/pets')}>
                            Browse Pets
                        </Button>
                    </CardContent>
                </Card>
            </div>
        );
    }

    return (
        <div className="container max-w-4xl mx-auto p-4">
            <div className="mb-6">
                <h1 className="text-3xl font-bold tracking-tight">Favorite Pets</h1>
                <p className="text-muted-foreground">
                    Pets you've saved as favorites
                </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {favorites.map((pet) => (
                    <div key={pet.pet_id} className="relative group">
                        <PetCard
                            pet={pet}
                            onClick={() => navigate(`/pets/${pet.pet_id}`)}
                        />
                        <Button
                            variant="destructive"
                            size="sm"
                            className="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity"
                            onClick={(e) => {
                                e.stopPropagation();
                                handleRemoveFavorite(pet.pet_id);
                            }}
                        >
                            <Heart className="h-4 w-4" /> Remove
                        </Button>
                    </div>
                ))}
            </div>
        </div>
    );
}
