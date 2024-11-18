import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useToast } from '@/hooks/use-toast';
import { useAuth } from '@/contexts/AuthContext';
import api from '@/lib/axios';
import { 
    Building2,
    MoreHorizontal, 
    Search, 
    Plus, 
    Edit, 
    Trash2, 
    Users,
    CheckCircle,
    XCircle
} from 'lucide-react';
import { Badge } from '@/components/ui/badge';

interface Shelter {
    shelter_id: number;
    name: string;
    address: string;
    phone: string;
    email: string;
    is_no_kill: boolean;
    manager_id: number | null;
    total_pets: number;
    active_applications: number;
}

interface ShelterFilters {
    search?: string;
    is_no_kill?: boolean;
}

export default function ShelterManagement() {
    const [shelters, setShelters] = useState<Shelter[]>([]);
    const [loading, setLoading] = useState(true);
    const [filters, setFilters] = useState<ShelterFilters>({});
    const { toast } = useToast();
    const navigate = useNavigate();
    const { user } = useAuth();

    useEffect(() => {
        if (user?.role !== 'admin') {
            navigate('/unauthorized');
            return;
        }
        fetchShelters();
    }, [user, navigate]);

    const fetchShelters = async () => {
        try {
            setLoading(true);
            const response = await api.get('/admin/shelters', { params: filters });
            if (response.data.success) {
                setShelters(response.data.data);
            } else {
                throw new Error(response.data.error || 'Failed to fetch shelters');
            }
        } catch (error) {
            toast({
                variant: "destructive",
                title: "Error",
                description: "Failed to fetch shelters"
            });
        } finally {
            setLoading(false);
        }
    };

    const handleDeleteShelter = async (shelterId: number) => {
        if (!confirm('Are you sure you want to delete this shelter? This action cannot be undone.')) {
            return;
        }

        try {
            const response = await api.delete(`/admin/shelters/${shelterId}`);
            if (response.data.success) {
                toast({
                    title: "Success",
                    description: "Shelter deleted successfully"
                });
                fetchShelters();
            } else {
                throw new Error(response.data.error || 'Failed to delete shelter');
            }
        } catch (error: any) {
            toast({
                variant: "destructive",
                title: "Error",
                description: error.response?.data?.error || "Failed to delete shelter"
            });
        }
    };

    const handleSearch = (searchTerm: string) => {
        const newFilters = { ...filters, search: searchTerm };
        setFilters(newFilters);
    };

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
                            <CardTitle>Shelter Management</CardTitle>
                            <CardDescription>
                                Manage animal shelters and their information
                            </CardDescription>
                        </div>
                        <Button onClick={() => navigate('/admin/shelters/new')}>
                            <Plus className="mr-2 h-4 w-4" />
                            Add New Shelter
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div className="flex gap-4 mb-6">
                        <div className="flex-1 relative">
                            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input
                                placeholder="Search shelters..."
                                className="pl-9"
                                onChange={(e) => handleSearch(e.target.value)}
                            />
                        </div>
                    </div>

                    <div className="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Contact</TableHead>
                                    <TableHead>Location</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Pets</TableHead>
                                    <TableHead>Applications</TableHead>
                                    <TableHead className="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {shelters.map((shelter) => (
                                    <TableRow key={shelter.shelter_id}>
                                        <TableCell className="font-medium">
                                            {shelter.name}
                                        </TableCell>
                                        <TableCell>
                                            <div className="text-sm">
                                                <div>{shelter.phone}</div>
                                                <div className="text-muted-foreground">{shelter.email}</div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="text-sm">{shelter.address}</div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge 
                                                variant={shelter.is_no_kill ? "success" : "default"}
                                                className="flex items-center gap-1 w-fit"
                                            >
                                                {shelter.is_no_kill ? 
                                                    <CheckCircle className="h-3 w-3" /> : 
                                                    <XCircle className="h-3 w-3" />
                                                }
                                                {shelter.is_no_kill ? 'No-Kill' : 'Standard'}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline">{shelter.total_pets}</Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline">{shelter.active_applications}</Badge>
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" className="h-8 w-8 p-0">
                                                        <span className="sr-only">Open menu</span>
                                                        <MoreHorizontal className="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                                    <DropdownMenuItem
                                                        onClick={() => navigate(`/admin/shelters/${shelter.shelter_id}`)}
                                                    >
                                                        <Building2 className="mr-2 h-4 w-4" />
                                                        View Details
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem
                                                        onClick={() => navigate(`/admin/shelters/${shelter.shelter_id}/staff`)}
                                                    >
                                                        <Users className="mr-2 h-4 w-4" />
                                                        Manage Staff
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem
                                                        onClick={() => navigate(`/admin/shelters/${shelter.shelter_id}/edit`)}
                                                    >
                                                        <Edit className="mr-2 h-4 w-4" />
                                                        Edit Shelter
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem
                                                        onClick={() => handleDeleteShelter(shelter.shelter_id)}
                                                        className="text-red-600"
                                                    >
                                                        <Trash2 className="mr-2 h-4 w-4" />
                                                        Delete Shelter
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}
