import { useState, useEffect, useCallback } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { PetFilters } from '@/components/pets/PetFilters';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { Search, PawPrint, ArrowRight } from 'lucide-react';
import api from '@/lib/axios';
import { Pet, ApiResponse } from '@/types/api';
import { useToast } from '@/hooks/use-toast';
import { useAuth } from '@/contexts/AuthContext';

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

interface PaginatedResponse {
    items: Pet[];
    total: number;
    page: number;
    perPage: number;
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
    const [totalPages, setTotalPages] = useState(1);
    const { toast } = useToast();
    const { isAuthenticated } = useAuth();

    // Get current page from URL
    const currentPage = parseInt(searchParams.get('page') || '1', 10);
    const perPage = 12;

    const updateUrlParams = useCallback((page: number, search: string, currentFilters: PetFiltersState) => {
        const newParams = new URLSearchParams();
        
        if (page > 1) newParams.set('page', page.toString());
        if (search) newParams.set('search', search);
        
        // Add filters to URL if they exist
        Object.entries(currentFilters).forEach(([key, value]) => {
            if (value !== undefined && value !== null && value !== '') {
                if (Array.isArray(value)) {
                    value.forEach(v => newParams.append(key, v));
                } else {
                    newParams.set(key, value.toString());
                }
            }
        });

        setSearchParams(newParams);
    }, [setSearchParams]);

    // Authentication check
    useEffect(() => {
        if (!isAuthenticated) {
            navigate('/login', { state: { from: '/pets' } });
        }
    }, [isAuthenticated, navigate]);

    // Fetch available traits
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

    // Fetch pets with debounce
    useEffect(() => {
        const fetchPets = async () => {
            try {
                setLoading(true);
                setError(null);

                const response = await api.get<ApiResponse<PaginatedResponse>>('/pets', { 
                    params: {
                        page: currentPage,
                        perPage,
                        search: searchTerm,
                        ...filters
                    }
                });

                if (response.data.success) {
                    setPets(response.data.data.items);
                    const newTotalPages = Math.ceil(response.data.data.total / perPage);
                    setTotalPages(newTotalPages);
                    
                    // If current page is greater than total pages, reset to page 1
                    if (currentPage > newTotalPages) {
                        updateUrlParams(1, searchTerm, filters);
                    }
                } else {
                    throw new Error(response.data.error || 'Failed to fetch pets');
                }
            } catch (err: any) {
                const errorMessage = err.response?.data?.error || err.message || 'Failed to fetch pets';
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
    }, [currentPage, searchTerm, filters, toast, updateUrlParams]);

    const handleSearch = (event: React.FormEvent) => {
        event.preventDefault();
        updateUrlParams(1, searchTerm, filters);
    };

    const handleFilterChange = (newFilters: PetFiltersState) => {
        setFilters(newFilters);
        updateUrlParams(1, searchTerm, newFilters);
    };

    const handlePageChange = (page: number) => {
        updateUrlParams(page, searchTerm, filters);
    };

    const handlePetClick = (petId: number) => {
        navigate(`/pets/${petId}`);
    };

    // Pagination Component
    const PaginationControls = () => {
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }

        return (
            <div className="flex gap-2">
                <Button
                    variant="outline"
                    onClick={() => handlePageChange(currentPage - 1)}
                    disabled={currentPage === 1 || loading}
                >
                    Previous
                </Button>
                
                {startPage > 1 && (
                    <>
                        <Button
                            variant="outline"
                            onClick={() => handlePageChange(1)}
                            className="hidden sm:inline-flex"
                        >
                            1
                        </Button>
                        {startPage > 2 && <span className="px-2">...</span>}
                    </>
                )}

                {Array.from(
                    { length: endPage - startPage + 1 },
                    (_, i) => startPage + i
                ).map((page) => (
                    <Button
                        key={page}
                        variant={currentPage === page ? "default" : "outline"}
                        onClick={() => handlePageChange(page)}
                        className="hidden sm:inline-flex"
                    >
                        {page}
                    </Button>
                ))}

                {endPage < totalPages && (
                    <>
                        {endPage < totalPages - 1 && <span className="px-2">...</span>}
                        <Button
                            variant="outline"
                            onClick={() => handlePageChange(totalPages)}
                            className="hidden sm:inline-flex"
                        >
                            {totalPages}
                        </Button>
                    </>
                )}

                <div className="flex items-center px-4 sm:hidden">
                    Page {currentPage} of {totalPages}
                </div>
                
                <Button
                    variant="outline"
                    onClick={() => handlePageChange(currentPage + 1)}
                    disabled={currentPage === totalPages || loading}
                >
                    Next
                </Button>
            </div>
        );
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
                <>
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {pets.map((pet) => (
                            <Card 
                                key={pet.pet_id} 
                                className="overflow-hidden hover:shadow-lg transition-shadow cursor-pointer"
                                onClick={() => handlePetClick(pet.pet_id)}
                            >
                                <CardHeader>
                                    <CardTitle>{pet.name}</CardTitle>
                                    <CardDescription>
                                        {pet.breed} • {pet.gender} • {pet.age} years old
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-muted-foreground mb-4">{pet.description}</p>
                                    
                                    {pet.traits && Object.entries(pet.traits).map(([category, traits]) => (
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
                                <CardFooter className="justify-end">
                                    <Button variant="ghost" size="sm">
                                        View Details <ArrowRight className="ml-2 h-4 w-4" />
                                    </Button>
                                </CardFooter>
                            </Card>
                        ))}
                    </div>

                    <div className="mt-6 flex justify-center">
                        <PaginationControls />
                    </div>
                </>
            )}
        </div>
    );
}

export default PetsPage;
