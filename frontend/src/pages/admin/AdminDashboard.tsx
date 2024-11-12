import { useEffect, useState } from 'react';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Link } from 'react-router-dom';
import { Users, Building2, PawPrint, FileText } from 'lucide-react';
import api from '@/lib/axios';

interface DashboardStats {
  totalUsers: number;
  totalShelters: number;
  totalPets: number;
  totalApplications: number;
}

export default function AdminDashboard() {
  const [stats, setStats] = useState<DashboardStats>({
    totalUsers: 0,
    totalShelters: 0,
    totalPets: 0,
    totalApplications: 0
  });

  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchStats = async () => {
      try {
        const response = await api.get('/admin/stats');
        setStats(response.data.data);
      } catch (error) {
        console.error('Failed to fetch admin stats:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchStats();
  }, []);

  const StatCard = ({ title, value, icon: Icon, href }: { 
    title: string;
    value: number;
    icon: any;
    href: string;
  }) => (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle className="text-sm font-medium">
          {title}
        </CardTitle>
        <Icon className="h-4 w-4 text-muted-foreground" />
      </CardHeader>
      <CardContent>
        <div className="text-2xl font-bold">{value}</div>
        <Button asChild variant="link" className="p-0">
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
        <div>
          <h2 className="text-3xl font-bold tracking-tight">Admin Dashboard</h2>
          <p className="text-muted-foreground">
            Overview of system statistics and management options
          </p>
        </div>

        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <StatCard
            title="Total Users"
            value={stats.totalUsers}
            icon={Users}
            href="/admin/users"
          />
          <StatCard
            title="Shelters"
            value={stats.totalShelters}
            icon={Building2}
            href="/admin/shelters"
          />
          <StatCard
            title="Pets Listed"
            value={stats.totalPets}
            icon={PawPrint}
            href="/admin/pets"
          />
          <StatCard
            title="Adoption Applications"
            value={stats.totalApplications}
            icon={FileText}
            href="/admin/applications"
          />
        </div>

        {/* Recent Activity Section */}
        <Card className="mt-6">
          <CardHeader>
            <CardTitle>Recent Activity</CardTitle>
            <CardDescription>
              Latest system updates and changes
            </CardDescription>
          </CardHeader>
          <CardContent>
            {/* Add recent activity content here */}
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
