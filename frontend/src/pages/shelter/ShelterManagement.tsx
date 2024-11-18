import { useState, useEffect } from 'react';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Link } from 'react-router-dom';
import { Plus, Search } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { PetCard } from '@/components/pets/PetCard';
import { PetImageUploader } from '@/components/pets/PetImageUploader';
import { useToast } from '@/hooks/use-toast';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import { Pet, PetImage } from '@/types/api';
import api from '@/lib/axios';

interface ManagementDialogState {
    isOpen: boolean;
    pet: Pet | null;
}

export default function ShelterManagement() {
    const [pets, setPets] = useState<Pet[]>([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    const [managementDialog, setManagementDialog] = useState<ManagementDialogState>({
        isOpen: false,
        pet: null
    });
    const { toast } = useToast();

    useEffect(() => {
        fetchPets();
    }, []);

    const fetchPets = async () => {
        try {
            setLoading(true);
            const response = await api.get('/shelter/pets');
            setPets(response.data.data);
        } catch (error) {
            console.error('Failed to fetch pets:', error);
            toast({
                variant: "destructive",
                title: "Error",
                description: "Failed to load pets",
            });
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async (petId: number) => {
        if (!confirm('Are you sure you want to delete this pet listing?')) return;

        try {
            await api.delete(`/shelter/pets/${petId}`);
            toast({
                title: "Success",
                description: "Pet listing deleted successfully",
            });
            fetchPets(); // Refresh the list
        } catch (error) {
            toast({
                variant: "destructive",
                title: "Error",
                description: "Failed to delete pet listing",
            });
        }
    };

    const handleImagesUpdated = async (petId: number, updatedImages: PetImage[]) => {
        setPets(currentPets => 
            currentPets.map(pet => 
                pet.pet_id === petId 
                    ? { ...pet, images: updatedImages }
                    : pet
            )
        );
    };

    const openManagementDialog = (pet: Pet) => {
        setManagementDialog({
            isOpen: true,
            pet
        });
    };

    const closeManagementDialog = () => {
        setManagementDialog({
            isOpen: false,
            pet: null
        });
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
                                Add, edit, and manage your shelter's pet listings
                            </CardDescription>
                        </div>
                        <Button asChild>
                            <Link to="/shelter/pets/new">
                                <Plus className="mr-2 h-4 w-4" />
                                Add New Pet
                            </Link>
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    {/* Search Bar */}
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

                    {/* Pet Grid */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {filteredPets.map((pet) => (
                            <div key={pet.pet_id} className="relative group">
                                <PetCard
                                    pet={pet}
                                    onClick={() => openManagementDialog(pet)}
                                />
                                <div className="absolute top-2 right-2 space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <Button
                                        variant="secondary"
                                        size="sm"
                                        asChild
                                    >
                                        <Link to={`/shelter/pets/${pet.pet_id}/edit`}>
                                            Edit
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        onClick={() => handleDelete(pet.pet_id)}
                                    >
                                        Delete
                                    </Button>
                                </div>
                            </div>
                        ))}
                    </div>
                </CardContent>
            </Card>

            {/* Image Management Dialog */}
            <Dialog open={managementDialog.isOpen} onOpenChange={closeManagementDialog}>
                <DialogContent className="max-w-4xl">
                    <DialogHeader>
                        <DialogTitle>Manage Images for {managementDialog.pet?.name}</DialogTitle>
                        <DialogDescription>
                            Upload, delete, or set primary images for this pet
                        </DialogDescription>
                    </DialogHeader>
                    {managementDialog.pet && (
                        <PetImageUploader
                            petId={managementDialog.pet.pet_id}
                            existingImages={managementDialog.pet.images}
                            onImagesUpdated={(images) => handleImagesUpdated(managementDialog.pet!.pet_id, images)}
                        />
                    )}
                </DialogContent>
            </Dialog>
        </div>
    );
}
