import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
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
    MoreHorizontal, 
    Search, 
    UserPlus, 
    Shield, 
    Ban, 
    Mail,
    RefreshCw
} from 'lucide-react';

interface User {
    user_id: number;
    username: string;
    email: string;
    role: string;
    account_status: string;
    registration_date: string;
    last_login?: string;
}

interface UserFilters {
    search?: string;
    role?: string;
    status?: string;
}

export default function UserManagement() {
    const [users, setUsers] = useState<User[]>([]);
    const [loading, setLoading] = useState(true);
    const [filters, setFilters] = useState<UserFilters>({});
    const { toast } = useToast();
    const navigate = useNavigate();
    const { user: currentUser } = useAuth();

    useEffect(() => {
        // Check admin access
        if (currentUser?.role !== 'admin') {
            navigate('/unauthorized');
            return;
        }
        
        fetchUsers();
    }, [currentUser, navigate]);

    const fetchUsers = async (appliedFilters: UserFilters = filters) => {
        try {
            setLoading(true);
            const response = await api.get('/admin/users', { params: appliedFilters });
            if (response.data.success) {
                setUsers(response.data.data);
            } else {
                throw new Error(response.data.error || 'Failed to fetch users');
            }
        } catch (error) {
            toast({
                variant: "destructive",
                title: "Error",
                description: "Failed to fetch users"
            });
        } finally {
            setLoading(false);
        }
    };

    const handleRoleChange = async (userId: number, newRole: string) => {
        try {
            const response = await api.put(`/admin/users/${userId}/role`, { role: newRole });
            if (response.data.success) {
                fetchUsers();
                toast({
                    title: "Success",
                    description: "User role updated successfully"
                });
            } else {
                throw new Error(response.data.error || 'Failed to update role');
            }
        } catch (error) {
            toast({
                variant: "destructive",
                title: "Error",
                description: "Failed to update user role"
            });
        }
    };

    const handleStatusChange = async (userId: number, newStatus: string) => {
        try {
            const response = await api.put(`/admin/users/${userId}/status`, { status: newStatus });
            if (response.data.success) {
                fetchUsers();
                toast({
                    title: "Success",
                    description: "User status updated successfully"
                });
            } else {
                throw new Error(response.data.error || 'Failed to update status');
            }
        } catch (error) {
            toast({
                variant: "destructive",
                title: "Error",
                description: "Failed to update user status"
            });
        }
    };

    const resendVerificationEmail = async (userId: number) => {
        try {
            const response = await api.post(`/admin/users/${userId}/resend-verification`);
            if (response.data.success) {
                toast({
                    title: "Success",
                    description: "Verification email sent successfully"
                });
            } else {
                throw new Error(response.data.error || 'Failed to send verification email');
            }
        } catch (error) {
            toast({
                variant: "destructive",
                title: "Error",
                description: "Failed to send verification email"
            });
        }
    };

    const handleSearch = (searchTerm: string) => {
        const newFilters = { ...filters, search: searchTerm };
        setFilters(newFilters);
        fetchUsers(newFilters);
    };

    const handleFilterChange = (key: string, value: string) => {
        const newFilters = { ...filters, [key]: value };
        setFilters(newFilters);
        fetchUsers(newFilters);
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
                            <CardTitle>User Management</CardTitle>
                            <CardDescription>
                                Manage user roles and account status
                            </CardDescription>
                        </div>
                        <Button>
                            <UserPlus className="mr-2 h-4 w-4" />
                            Add User
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div className="flex gap-4 mb-6">
                        <div className="flex-1 relative">
                            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input
                                placeholder="Search users..."
                                className="pl-9"
                                onChange={(e) => handleSearch(e.target.value)}
                            />
                        </div>
                        <Select
                            defaultValue={filters.role}
                            onValueChange={(value) => handleFilterChange('role', value)}
                        >
                            <SelectTrigger className="w-[180px]">
                                <SelectValue placeholder="Filter by role" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Roles</SelectItem>
                                <SelectItem value="admin">Admin</SelectItem>
                                <SelectItem value="shelter_staff">Shelter Staff</SelectItem>
                                <SelectItem value="adopter">Adopter</SelectItem>
                            </SelectContent>
                        </Select>
                        <Select
                            defaultValue={filters.status}
                            onValueChange={(value) => handleFilterChange('status', value)}
                        >
                            <SelectTrigger className="w-[180px]">
                                <SelectValue placeholder="Filter by status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Statuses</SelectItem>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="pending">Pending</SelectItem>
                                <SelectItem value="suspended">Suspended</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div className="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Username</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead>Role</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Registration Date</TableHead>
                                    <TableHead>Last Login</TableHead>
                                    <TableHead className="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {users.map((user) => (
                                    <TableRow key={user.user_id}>
                                        <TableCell className="font-medium">{user.username}</TableCell>
                                        <TableCell>{user.email}</TableCell>
                                        <TableCell>
                                            <Select
                                                defaultValue={user.role}
                                                onValueChange={(value) => handleRoleChange(user.user_id, value)}
                                                disabled={currentUser?.user_id === user.user_id}
                                            >
                                                <SelectTrigger className="w-[140px]">
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="adopter">Adopter</SelectItem>
                                                    <SelectItem value="shelter_staff">Shelter Staff</SelectItem>
                                                    <SelectItem value="admin">Admin</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </TableCell>
                                        <TableCell>
                                            <div className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                                ${user.account_status === 'active' ? 'bg-green-100 text-green-800' :
                                                user.account_status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                                'bg-red-100 text-red-800'}`}>
                                                {user.account_status.charAt(0).toUpperCase() + user.account_status.slice(1)}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            {new Date(user.registration_date).toLocaleDateString()}
                                        </TableCell>
                                        <TableCell>
                                            {user.last_login ? new Date(user.last_login).toLocaleString() : 'Never'}
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
                                                        onClick={() => handleStatusChange(user.user_id, 
                                                            user.account_status === 'suspended' ? 'active' : 'suspended')}
                                                    >
                                                        {user.account_status === 'suspended' ? (
                                                            <>
                                                                <RefreshCw className="mr-2 h-4 w-4" />
                                                                Reactivate Account
                                                            </>
                                                        ) : (
                                                            <>
                                                                <Ban className="mr-2 h-4 w-4" />
                                                                Suspend Account
                                                            </>
                                                        )}
                                                    </DropdownMenuItem>
                                                    {user.account_status === 'pending' && (
                                                        <DropdownMenuItem
                                                            onClick={() => resendVerificationEmail(user.user_id)}
                                                        >
                                                            <Mail className="mr-2 h-4 w-4" />
                                                            Resend Verification
                                                        </DropdownMenuItem>
                                                    )}
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem
                                                        onClick={() => navigate(`/admin/users/${user.user_id}`)}
                                                    >
                                                        <Shield className="mr-2 h-4 w-4" />
                                                        View Full Profile
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
