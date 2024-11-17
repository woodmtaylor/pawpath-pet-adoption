import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { PawPrint, MapPin } from 'lucide-react';

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
    traits?: { [category: string]: string[] }; // Make traits optional
    images?: string[];
  };
  onClick?: () => void;
}

export function PetCard({ pet, onClick }: PetCardProps) {
  const renderTraits = () => {
    // Handle case where traits is undefined or null
    if (!pet.traits) {
      return null;
    }

    return Object.entries(pet.traits).map(([category, traits]) => (
      Array.isArray(traits) ? traits.map(trait => (
        <Badge
          key={`${category}-${trait}`}
          variant="secondary"
          className="text-xs"
        >
          {trait}
        </Badge>
      )) : null
    ));
  };

  return (
    <Card
      className="cursor-pointer hover:shadow-lg transition-shadow"
      onClick={onClick}
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

        {pet.traits && Object.keys(pet.traits).length > 0 && (
          <div className="flex flex-wrap gap-2 mb-4">
            {renderTraits()}
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
