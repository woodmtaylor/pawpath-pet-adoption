import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useToast } from '@/hooks/use-toast';
import { ChevronRight, ClipboardList } from 'lucide-react';
import api from '@/lib/axios';

interface Application {
  application_id: number;
  pet_id: number;
  status: 'pending' | 'under_review' | 'approved' | 'rejected' | 'withdrawn';
  application_date: string;
  pet_name: string;
  pet_species: string;
  pet_breed: string;
  shelter_name: string;
}

interface ApiResponse {
  success: boolean;
  data: Application[];
  error?: string;
}

export default function ApplicationsPage() {
  const [applications, setApplications] = useState<Application[]>([]);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();
  const { toast } = useToast();

  useEffect(() => {
    fetchApplications();
  }, []);

  const fetchApplications = async () => {
    try {
      setLoading(true);
      const response = await api.get<ApiResponse>('/adoptions/user');
      console.log('API Response:', response.data); // Debug log

      if (response.data.success) {
        setApplications(response.data.data || []);
      } else {
        throw new Error(response.data.error || 'Failed to load applications');
      }
    } catch (error: any) {
      console.error('Error fetching applications:', error);
      toast({
        variant: "destructive",
        title: "Error",
        description: error.message || "Failed to load applications",
      });
      setApplications([]); // Set to empty array on error
    } finally {
      setLoading(false);
    }
  };

  const getStatusBadgeVariant = (status: string) => {
    switch (status) {
      case 'approved':
        return 'success';
      case 'rejected':
        return 'destructive';
      case 'under_review':
        return 'warning';
      case 'withdrawn':
        return 'secondary';
      default:
        return 'default';
    }
  };

  if (loading) {
    return (
      <div className="container max-w-4xl mx-auto p-4">
        <div className="flex justify-center items-center min-h-[400px]">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
        </div>
      </div>
    );
  }

  return (
    <div className="container max-w-4xl mx-auto p-4">
      <div className="mb-6">
        <h1 className="text-3xl font-bold tracking-tight">My Applications</h1>
        <p className="text-muted-foreground">
          Track the status of your adoption applications
        </p>
      </div>

      {applications.length === 0 ? (
        <Card>
          <CardContent className="flex flex-col items-center justify-center py-12">
            <ClipboardList className="h-12 w-12 text-muted-foreground mb-4" />
            <h3 className="text-lg font-semibold mb-2">No Applications Yet</h3>
            <p className="text-muted-foreground text-center mb-4">
              You haven't submitted any adoption applications yet.
            </p>
            <Button onClick={() => navigate('/pets')}>
              Browse Available Pets
            </Button>
          </CardContent>
        </Card>
      ) : (
        <div className="grid gap-4">
          {applications.map((application) => (
            <Card key={application.application_id} className="group">
              <CardContent className="p-6">
                <div className="flex justify-between items-start">
                  <div className="space-y-1">
                    <div className="flex items-center gap-2">
                      <h3 className="font-semibold text-lg">
                        {application.pet_name}
                      </h3>
                      <Badge variant={getStatusBadgeVariant(application.status)}>
                        {application.status.replace('_', ' ')}
                      </Badge>
                    </div>
                    <p className="text-sm text-muted-foreground">
                      {application.pet_breed} • {application.pet_species}
                    </p>
                    <p className="text-sm text-muted-foreground">
                      At {application.shelter_name}
                    </p>
                    <p className="text-sm text-muted-foreground">
                      Submitted on {new Date(application.application_date).toLocaleDateString()}
                    </p>
                  </div>
                  
                  <Button
                    variant="ghost"
                    size="sm"
                    className="opacity-0 group-hover:opacity-100 transition-opacity"
                    onClick={() => navigate(`/profile/applications/${application.application_id}`)}
                  >
                    View Details
                    <ChevronRight className="ml-2 h-4 w-4" />
                  </Button>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      )}
    </div>
  );
}
