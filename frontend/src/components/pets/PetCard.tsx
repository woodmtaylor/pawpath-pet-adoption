import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { PawPrint, MapPin } from 'lucide-react';

interface PetImage {
    image_id: number;
    url: string;
    is_primary: boolean;
}

interface PetCardProps {
    pet: {
        pet_id: number;
        name: string;
        species: string;
        breed: string;
        age: number;
        gender: string;
        description: string;
        shelter_name: string;
        traits?: { [category: string]: string[] };
        images?: PetImage[];
    };
    onClick?: () => void;
}

export function PetCard({ pet, onClick }: PetCardProps) {
    // Find primary image or first image
    const displayImage = pet.images?.find(img => img.is_primary) || pet.images?.[0];

    return (
        <Card
            className="cursor-pointer hover:shadow-lg transition-shadow h-full"
            onClick={onClick}
        >
            <div className="aspect-square relative bg-white">
                {displayImage ? (
                    <img
                        src={displayImage.url}
                        alt={`${pet.name} - ${pet.breed}`}
                        className="w-full h-full object-contain"
                        style={{ backgroundColor: 'white' }}
                        onError={(e) => {
                            console.error(`Failed to load image: ${displayImage.url}`);
                            const target = e.target as HTMLImageElement;
                            target.style.display = 'none';
                            const fallback = target.parentElement?.querySelector('.fallback');
                            if (fallback) fallback.classList.remove('hidden');
                        }}
                    />
                ) : (
                    <div className="w-full h-full flex items-center justify-center">
                        <PawPrint className="h-12 w-12 text-muted-foreground" />
                    </div>
                )}
                {/* Hidden fallback that shows if image fails to load */}
                <div className="fallback hidden w-full h-full flex items-center justify-center absolute top-0 left-0 bg-muted">
                    <PawPrint className="h-12 w-12 text-muted-foreground" />
                </div>
            </div>

            <CardHeader className="space-y-1">
                <CardTitle className="text-xl">{pet.name}</CardTitle>
                <CardDescription>
                    {pet.breed} • {pet.age} years old • {pet.gender}
                </CardDescription>
            </CardHeader>

            <CardContent>
                <p className="text-sm text-muted-foreground line-clamp-2 mb-4">
                    {pet.description}
                </p>

                {pet.traits && Object.keys(pet.traits).length > 0 && (
                    <div className="flex flex-wrap gap-2 mb-4">
                        {Object.entries(pet.traits).map(([category, traits]) => (
                            Array.isArray(traits) ? traits.map(trait => (
                                <Badge
                                    key={`${category}-${trait}`}
                                    variant="secondary"
                                    className="text-xs"
                                >
                                    {trait}
                                </Badge>
                            )) : null
                        ))}
                    </div>
                )}

                <div className="flex items-center text-sm text-muted-foreground">
                    <MapPin className="h-4 w-4 mr-1" />
                    {pet.shelter_name}
                </div>
            </CardContent>
        </Card>
    );
}
