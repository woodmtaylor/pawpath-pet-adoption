import { useEffect, useState } from 'react';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Link } from 'react-router-dom';
import { PawPrint, FileText, Users, Plus } from 'lucide-react';
import api from '@/lib/axios';

interface ShelterStats {
  totalPets: number;
  activeApplications: number;
  adoptedPets: number;
}

export default function ShelterDashboard() {
  const [stats, setStats] = useState<ShelterStats>({
    totalPets: 0,
    activeApplications: 0,
    adoptedPets: 0
  });

  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchStats = async () => {
      try {
        const response = await api.get('/shelter/stats');
        setStats(response.data.data);
      } catch (error) {
        console.error('Failed to fetch shelter stats:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchStats();
  }, []);

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
            <h2 className="text-3xl font-bold tracking-tight">Shelter Dashboard</h2>
            <p className="text-muted-foreground">
              Manage your shelter's pets and adoption applications
            </p>
          </div>
          <Button asChild>
            <Link to="/shelter/pets/new">
              <Plus className="mr-2 h-4 w-4" />
              Add New Pet
            </Link>
          </Button>
        </div>

        <div className="grid gap-4 md:grid-cols-3">
          <Card>
            <CardHeader>
              <CardTitle>Available Pets</CardTitle>
              <div className="text-3xl font-bold">{stats.totalPets}</div>
            </CardHeader>
            <CardContent>
              <Button asChild variant="link" className="p-0">
                <Link to="/shelter/pets">View All Pets</Link>
              </Button>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Active Applications</CardTitle>
              <div className="text-3xl font-bold">{stats.activeApplications}</div>
            </CardHeader>
            <CardContent>
              <Button asChild variant="link" className="p-0">
                <Link to="/shelter/applications">View Applications</Link>
              </Button>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Successful Adoptions</CardTitle>
              <div className="text-3xl font-bold">{stats.adoptedPets}</div>
            </CardHeader>
            <CardContent>
              <Button asChild variant="link" className="p-0">
                <Link to="/shelter/adoptions">View History</Link>
              </Button>
            </CardContent>
          </Card>
        </div>

        {/* Recent Applications */}
        <Card className="mt-6">
          <CardHeader>
            <CardTitle>Recent Applications</CardTitle>
            <CardDescription>
              Latest adoption applications requiring attention
            </CardDescription>
          </CardHeader>
          <CardContent>
            {/* Add recent applications table/list here */}
          </CardContent>
        </Card>

        {/* Featured Pets */}
        <Card className="mt-6">
          <CardHeader>
            <CardTitle>Featured Pets</CardTitle>
            <CardDescription>
              Pets that have been listed the longest
            </CardDescription>
          </CardHeader>
          <CardContent>
            {/* Add featured pets grid here */}
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
