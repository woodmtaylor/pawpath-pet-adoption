import { useState, useEffect, useCallback } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { PetFilters } from '@/components/pets/PetFilters';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import { Search, PawPrint } from 'lucide-react';
import { PetCard } from '@/components/pets/PetCard';
import api from '@/lib/axios';
import { Pet, ApiResponse, PaginatedResponse } from '@/types/api';
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

interface TraitResponse {
    success: boolean;
    data: {
        traits: Array<{
            trait_id: number;
            trait_name: string;
        }>;
    };
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

    const currentPage = parseInt(searchParams.get('page') || '1', 10);
    const perPage = 12;

    const updateUrlParams = useCallback((page: number, search: string, currentFilters: PetFiltersState) => {
        const newParams = new URLSearchParams(searchParams);
        
        if (page > 1) newParams.set('page', page.toString());
        else newParams.delete('page');
        
        if (search) newParams.set('search', search);
        else newParams.delete('search');
        
        Object.entries(currentFilters).forEach(([key, value]) => {
            if (value !== undefined && value !== null && value !== '') {
                if (Array.isArray(value)) {
                    newParams.delete(key);
                    value.forEach(v => newParams.append(key, v));
                } else {
                    newParams.set(key, value.toString());
                }
            } else {
                newParams.delete(key);
            }
        });

        setSearchParams(newParams);
    }, [setSearchParams, searchParams]);

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
                const response = await api.get<TraitResponse>('/pet-traits');
                if (response.data.success && response.data.data.traits) {
                    setAvailableTraits(response.data.data.traits.map(trait => trait.trait_name));
                }
            } catch (err) {
                console.error('Failed to fetch traits:', err);
            }
        };

        fetchTraits();
    }, []);

    // Fetch pets with pagination
    useEffect(() => {
        const fetchPets = async () => {
            if (!isAuthenticated) return;
            
            try {
                setLoading(true);
                setError(null);

                const offset = (currentPage - 1) * perPage;

                const response = await api.get<ApiResponse<PaginatedResponse<Pet>>>('/pets', { 
                    params: {
                        page: currentPage,
                        perPage,
                        offset,
                        search: searchTerm,
                        ...filters
                    }
                });

                if (response.data.success) {
                    setPets(response.data.data.items);
                    const newTotalPages = Math.ceil(response.data.data.total / perPage);
                    setTotalPages(newTotalPages);
                    
                    if (currentPage > newTotalPages && newTotalPages > 0) {
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

        fetchPets();
    }, [currentPage, searchTerm, filters, toast, updateUrlParams, isAuthenticated]);

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

    // Pagination Controls Component
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
                            <PetCard
                                key={pet.pet_id}
                                pet={pet}
                                onClick={() => navigate(`/pets/${pet.pet_id}`)}
                            />
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
