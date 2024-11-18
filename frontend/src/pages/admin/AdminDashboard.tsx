import { useEffect, useState } from 'react';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Link, useNavigate } from 'react-router-dom';
import { 
    PawPrint, 
    FileText, 
    Users, 
    Plus, 
    Building2, 
    TrendingUp, 
    AlertTriangle 
} from 'lucide-react';
import { useToast } from '@/hooks/use-toast';
import { useAuth } from '@/contexts/AuthContext';
import api from '@/lib/axios';

interface DashboardStats {
    totalUsers: number;
    totalShelters: number;
    totalPets: number;
    totalApplications: number;
    pendingApplications: number;
    activeUsers: number;
    recentActivity: Array<{
        id: number;
        type: string;
        message: string;
        timestamp: string;
    }>;
}

export default function AdminDashboard() {
    const [stats, setStats] = useState<DashboardStats>({
        totalUsers: 0,
        totalShelters: 0,
        totalPets: 0,
        totalApplications: 0,
        pendingApplications: 0,
        activeUsers: 0,
        recentActivity: []
    });

    const [loading, setLoading] = useState(true);
    const { toast } = useToast();
    const { user, isAuthenticated } = useAuth();
    const navigate = useNavigate();

    useEffect(() => {
        // Check if user is authenticated and has admin role
        if (!isAuthenticated || user?.role !== 'admin') {
            toast({
                variant: "destructive",
                title: "Unauthorized Access",
                description: "You don't have permission to view this page."
            });
            navigate('/unauthorized');
            return;
        }

        const fetchStats = async () => {
            try {
                setLoading(true);
                const response = await api.get('/admin/stats');
                if (response.data.success) {
                    setStats(response.data.data);
                } else {
                    throw new Error(response.data.error || 'Failed to fetch stats');
                }
            } catch (error) {
                console.error('Failed to fetch admin stats:', error);
                toast({
                    variant: "destructive",
                    title: "Error",
                    description: "Failed to load dashboard statistics"
                });
            } finally {
                setLoading(false);
            }
        };

        fetchStats();
    }, [isAuthenticated, user, navigate, toast]);

    const StatCard = ({ 
        title, 
        value, 
        icon: Icon, 
        description, 
        href, 
        trend 
    }: { 
        title: string;
        value: number;
        icon: any;
        description?: string;
        href: string;
        trend?: {
            value: number;
            label: string;
        };
    }) => (
        <Card>
            <CardHeader className="flex flex-row items-center justify-between pb-2">
                <CardTitle className="text-sm font-medium">{title}</CardTitle>
                <Icon className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
                <div className="flex items-center justify-between">
                    <div>
                        <div className="text-2xl font-bold">{value}</div>
                        {description && (
                            <p className="text-xs text-muted-foreground">{description}</p>
                        )}
                    </div>
                    {trend && (
                        <div className={`text-xs ${trend.value >= 0 ? 'text-green-500' : 'text-red-500'}`}>
                            <TrendingUp className="h-3 w-3 inline mr-1" />
                            {trend.value}% {trend.label}
                        </div>
                    )}
                </div>
                <Button asChild variant="link" className="p-0 mt-2">
                    <Link to={href}>View Details</Link>
                </Button>
            </CardContent>
        </Card>
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
            <div className="space-y-4">
                <div className="flex justify-between items-center">
                    <div>
                        <h2 className="text-3xl font-bold tracking-tight">Admin Dashboard</h2>
                        <p className="text-muted-foreground">
                            Welcome back, {user?.username}. Here's what's happening with your platform.
                        </p>
                    </div>
                    <Button asChild>
                        <Link to="/admin/shelters/new">
                            <Plus className="mr-2 h-4 w-4" />
                            Add New Shelter
                        </Link>
                    </Button>
                </div>

                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <StatCard
                        title="Total Users"
                        value={stats.totalUsers}
                        icon={Users}
                        description="Active platform users"
                        href="/admin/users"
                        trend={{ value: 12, label: 'this month' }}
                    />
                    <StatCard
                        title="Animal Shelters"
                        value={stats.totalShelters}
                        icon={Building2}
                        description="Registered shelters"
                        href="/admin/shelters"
                    />
                    <StatCard
                        title="Listed Pets"
                        value={stats.totalPets}
                        icon={PawPrint}
                        description="Available for adoption"
                        href="/admin/pets"
                    />
                    <StatCard
                        title="Pending Applications"
                        value={stats.pendingApplications}
                        icon={FileText}
                        description="Requiring review"
                        href="/admin/applications"
                    />
                </div>

                <div className="grid gap-4 grid-cols-1 md:grid-cols-2 mt-4">
                    {/* Recent Activity */}
                    <Card className="md:col-span-2">
                        <CardHeader>
                            <CardTitle>Recent Activity</CardTitle>
                            <CardDescription>Latest platform updates and changes</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {stats.recentActivity.length > 0 ? (
                                    stats.recentActivity.map((activity) => (
                                        <div 
                                            key={activity.id} 
                                            className="flex items-center justify-between p-2 hover:bg-secondary/50 rounded-lg"
                                        >
                                            <div className="flex items-center gap-2">
                                                <ActivityIcon type={activity.type} />
                                                <div>
                                                    <p className="text-sm">{activity.message}</p>
                                                    <p className="text-xs text-muted-foreground">
                                                        {new Date(activity.timestamp).toLocaleString()}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    <div className="text-center text-muted-foreground py-8">
                                        No recent activity to display
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* System Alerts */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <AlertTriangle className="h-5 w-5" />
                                System Alerts
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            {/* Add system alerts here */}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    );
}

// Helper component for activity icons
const ActivityIcon = ({ type }: { type: string }) => {
    switch (type) {
        case 'user':
            return <Users className="h-4 w-4 text-blue-500" />;
        case 'pet':
            return <PawPrint className="h-4 w-4 text-green-500" />;
        case 'shelter':
            return <Building2 className="h-4 w-4 text-purple-500" />;
        default:
            return <FileText className="h-4 w-4 text-gray-500" />;
    }
};
