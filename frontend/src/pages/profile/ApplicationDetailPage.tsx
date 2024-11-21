import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { format } from 'date-fns';
import { 
    Card, 
    CardHeader, 
    CardTitle, 
    CardDescription, 
    CardContent 
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { useToast } from '@/hooks/use-toast';
import { ChevronLeft, PawPrint, Clock, Building2, FileText } from 'lucide-react';
import api from '@/lib/axios';

interface ApplicationDetails {
    application_id: number;
    pet_id: number;
    user_id: number;
    status: 'pending' | 'under_review' | 'approved' | 'rejected' | 'withdrawn';
    application_date: string;
    reason: string;
    experience: string;
    living_situation: string;
    has_other_pets: boolean;
    other_pets_details: string | null;
    daily_schedule: string;
    veterinarian: string | null;
    pet_name: string;
    pet_breed: string;
    pet_species: string;
    shelter_name: string;
    last_updated: string;
}

export default function ApplicationDetailPage() {
    const { id } = useParams();
    const navigate = useNavigate();
    const { toast } = useToast();
    const [application, setApplication] = useState<ApplicationDetails | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (id) {
            fetchApplicationDetails();
        }
    }, [id]);

    const fetchApplicationDetails = async () => {
        try {
            setLoading(true);
            setError(null);
            console.log('Fetching application details for ID:', id);

            const response = await api.get(`/adoptions/${id}`);
            console.log('API Response:', response.data);

            if (response.data.success) {
                setApplication(response.data.data);
            } else {
                throw new Error(response.data.error || 'Failed to fetch application details');
            }
        } catch (error: any) {
            console.error('Error fetching application:', error);
            console.error('Full error details:', {
                message: error.message,
                response: error.response?.data,
                status: error.response?.status
            });

            const errorMessage = error.response?.data?.error 
                || error.message 
                || "Failed to load application details";
            
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

    const formatDate = (dateString: string) => {
        return format(new Date(dateString), 'PPP');
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

    if (!application) {
        return (
            <div className="container max-w-4xl mx-auto p-4">
                <Card>
                    <CardContent className="flex flex-col items-center justify-center py-12">
                        <FileText className="h-12 w-12 text-muted-foreground mb-4" />
                        <h3 className="text-lg font-semibold mb-2">Application Not Found</h3>
                        <p className="text-muted-foreground text-center mb-4">
                            The application you're looking for doesn't exist or you don't have permission to view it.
                        </p>
                        <Button onClick={() => navigate('/profile/applications')}>
                            View All Applications
                        </Button>
                    </CardContent>
                </Card>
            </div>
        );
    }

    return (
        <div className="container max-w-4xl mx-auto p-4">
            <Button
                variant="ghost"
                className="mb-4"
                onClick={() => navigate('/profile/applications')}
            >
                <ChevronLeft className="mr-2 h-4 w-4" />
                Back to Applications
            </Button>

            <div className="grid gap-6">
                {/* Header Card */}
                <Card>
                    <CardHeader>
                        <div className="flex justify-between items-start">
                            <div>
                                <CardTitle>Adoption Application</CardTitle>
                                <CardDescription>
                                    Application for {application.pet_name}
                                </CardDescription>
                            </div>
                            <Badge variant={getStatusBadgeVariant(application.status)}>
                                {application.status.replace('_', ' ')}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4">
                            <div className="flex items-center gap-2 text-muted-foreground">
                                <Clock className="h-4 w-4" />
                                Submitted on {formatDate(application.application_date)}
                            </div>
                            <div className="flex items-center gap-2 text-muted-foreground">
                                <Building2 className="h-4 w-4" />
                                {application.shelter_name}
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Pet Details Card */}
                <Card>
                    <CardHeader>
                        <CardTitle>Pet Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4">
                            <div className="flex items-center gap-4">
                                <PawPrint className="h-8 w-8 text-muted-foreground" />
                                <div>
                                    <h3 className="font-semibold">{application.pet_name}</h3>
                                    <p className="text-muted-foreground">
                                        {application.pet_breed} • {application.pet_species}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Application Details Card */}
                <Card>
                    <CardHeader>
                        <CardTitle>Application Details</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-6">
                            <div>
                                <h3 className="font-semibold mb-2">Why do you want to adopt this pet?</h3>
                                <p className="text-muted-foreground">{application.reason}</p>
                            </div>

                            <div>
                                <h3 className="font-semibold mb-2">Pet Care Experience</h3>
                                <p className="text-muted-foreground">{application.experience}</p>
                            </div>

                            <div>
                                <h3 className="font-semibold mb-2">Living Situation</h3>
                                <p className="text-muted-foreground">{application.living_situation}</p>
                            </div>

                            {application.has_other_pets && application.other_pets_details && (
                                <div>
                                    <h3 className="font-semibold mb-2">Other Pets</h3>
                                    <p className="text-muted-foreground">{application.other_pets_details}</p>
                                </div>
                            )}

                            <div>
                                <h3 className="font-semibold mb-2">Daily Schedule</h3>
                                <p className="text-muted-foreground">{application.daily_schedule}</p>
                            </div>

                            {application.veterinarian && (
                                <div>
                                    <h3 className="font-semibold mb-2">Veterinarian Information</h3>
                                    <p className="text-muted-foreground">{application.veterinarian}</p>
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    );
}
