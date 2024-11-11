import { useState } from 'react';
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from '@/components/ui/sheet';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Slider } from '@/components/ui/slider';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Filter, X } from 'lucide-react';

interface PetFilters {
  species?: string;
  breed?: string;
  ageMin?: number;
  ageMax?: number;
  gender?: string;
  size?: string;
  goodWith?: string[];
  traits?: string[];
}

interface PetFiltersProps {
  onFiltersChange: (filters: PetFilters) => void;
  initialFilters?: PetFilters;
  availableTraits: string[];
}

export function PetFilters({ onFiltersChange, initialFilters, availableTraits }: PetFiltersProps) {
  const [filters, setFilters] = useState<PetFilters>(initialFilters || {});
  const [isOpen, setIsOpen] = useState(false);

  const handleFilterChange = (key: keyof PetFilters, value: any) => {
    const newFilters = { ...filters, [key]: value };
    setFilters(newFilters);
    onFiltersChange(newFilters);
  };

  const clearFilters = () => {
    setFilters({});
    onFiltersChange({});
  };

  return (
    <Sheet open={isOpen} onOpenChange={setIsOpen}>
      <SheetTrigger asChild>
        <Button variant="outline" size="sm">
          <Filter className="mr-2 h-4 w-4" />
          Filters
        </Button>
      </SheetTrigger>
      <SheetContent className="w-full sm:max-w-md">
        <SheetHeader>
          <SheetTitle>Filter Pets</SheetTitle>
          <SheetDescription>
            Customize your search to find the perfect pet
          </SheetDescription>
        </SheetHeader>

        <div className="space-y-6 py-4">
          <div className="space-y-2">
            <Label>Species</Label>
            <Select
              value={filters.species}
              onValueChange={(value) => handleFilterChange('species', value)}
            >
              <SelectTrigger>
                <SelectValue placeholder="Select species" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="dog">Dogs</SelectItem>
                <SelectItem value="cat">Cats</SelectItem>
                <SelectItem value="bird">Birds</SelectItem>
                <SelectItem value="rabbit">Rabbits</SelectItem>
                <SelectItem value="other">Other</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div className="space-y-2">
            <Label>Breed</Label>
            <Input
              placeholder="Search breeds..."
              value={filters.breed || ''}
              onChange={(e) => handleFilterChange('breed', e.target.value)}
            />
          </div>

          <div className="space-y-2">
            <Label>Age Range (Years)</Label>
            <div className="flex items-center space-x-4">
              <Input
                type="number"
                placeholder="Min"
                min={0}
                max={20}
                value={filters.ageMin || ''}
                onChange={(e) => handleFilterChange('ageMin', e.target.value)}
              />
              <span>to</span>
              <Input
                type="number"
                placeholder="Max"
                min={0}
                max={20}
                value={filters.ageMax || ''}
                onChange={(e) => handleFilterChange('ageMax', e.target.value)}
              />
            </div>
          </div>

          <div className="space-y-2">
            <Label>Gender</Label>
            <RadioGroup
              value={filters.gender}
              onValueChange={(value) => handleFilterChange('gender', value)}
            >
              <div className="flex items-center space-x-4">
                <div className="flex items-center space-x-2">
                  <RadioGroupItem value="any" id="gender-any" />
                  <Label htmlFor="gender-any">Any</Label>
                </div>
                <div className="flex items-center space-x-2">
                  <RadioGroupItem value="male" id="gender-male" />
                  <Label htmlFor="gender-male">Male</Label>
                </div>
                <div className="flex items-center space-x-2">
                  <RadioGroupItem value="female" id="gender-female" />
                  <Label htmlFor="gender-female">Female</Label>
                </div>
              </div>
            </RadioGroup>
          </div>

          <div className="space-y-2">
            <Label>Size</Label>
            <Select
              value={filters.size}
              onValueChange={(value) => handleFilterChange('size', value)}
            >
              <SelectTrigger>
                <SelectValue placeholder="Select size" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="small">Small</SelectItem>
                <SelectItem value="medium">Medium</SelectItem>
                <SelectItem value="large">Large</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div className="space-y-2">
            <Label>Good with</Label>
            <div className="flex flex-wrap gap-2">
              {['kids', 'dogs', 'cats'].map((type) => (
                <Button
                  key={type}
                  variant="outline"
                  size="sm"
                  className={`capitalize ${
                    filters.goodWith?.includes(type)
                      ? 'bg-primary text-primary-foreground'
                      : ''
                  }`}
                  onClick={() => {
                    const current = filters.goodWith || [];
                    const updated = current.includes(type)
                      ? current.filter((t) => t !== type)
                      : [...current, type];
                    handleFilterChange('goodWith', updated);
                  }}
                >
                  {type}
                </Button>
              ))}
            </div>
          </div>

          <div className="pt-4 flex justify-between">
            <Button variant="outline" onClick={clearFilters}>
              <X className="mr-2 h-4 w-4" />
              Clear Filters
            </Button>
            <Button onClick={() => setIsOpen(false)}>
              Apply Filters
            </Button>
          </div>
        </div>
      </SheetContent>
    </Sheet>
  );
}
