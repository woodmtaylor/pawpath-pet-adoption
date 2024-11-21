import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { useToast } from '@/hooks/use-toast';
import { Search, MapPin, Phone, Mail, Building2, PawPrint } from 'lucide-react';
import api from '@/lib/axios';

interface Shelter {
  shelter_id: number;
  name: string;
  address: string;
  phone: string;
  email: string;
  is_no_kill: boolean;
  total_pets: number;
  active_applications: number;
}

export default function SheltersPage() {
  const navigate = useNavigate();
  const { toast } = useToast();
  const [shelters, setShelters] = useState<Shelter[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');

  useEffect(() => {
    fetchShelters();
  }, []);


  const fetchShelters = async () => {
    try {
      setLoading(true);
      console.log('Fetching shelters with search term:', searchTerm);
      
      // Note: Now we just use '/shelters' instead of '/api/shelters'
      const response = await api.get('/shelters', {
        params: {
          search: searchTerm || undefined
        }
      });
      
      console.log('Shelter API response:', response);

      if (response.data.success) {
        setShelters(response.data.data);
      } else {
        throw new Error(response.data.error || 'Failed to fetch shelters');
      }
    } catch (error: any) {
      console.error('Error fetching shelters:', error);
      console.error('Full error details:', {
        message: error.message,
        response: error.response?.data,
        status: error.response?.status,
        config: error.config
      });
      
      toast({
        variant: "destructive",
        title: "Error",
        description: error.response?.data?.error || error.message || "Failed to load shelters",
      });
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    fetchShelters();
  };

  return (
    <div className="container mx-auto p-4">
      <div className="mb-8">
        <h1 className="text-3xl font-bold mb-2">Animal Shelters</h1>
        <p className="text-muted-foreground">
          Find local shelters and rescue organizations
        </p>
      </div>

      {/* Search */}
      <form onSubmit={handleSearch} className="mb-8">
        <div className="relative max-w-xl">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <Input
            placeholder="Search shelters by name or location..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="pl-9"
          />
        </div>
      </form>

      {loading ? (
        <div className="flex justify-center items-center min-h-[400px]">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
        </div>
      ) : shelters.length === 0 ? (
        <Card>
          <CardContent className="flex flex-col items-center justify-center py-12">
            <Building2 className="h-12 w-12 text-muted-foreground mb-4" />
            <h3 className="text-lg font-semibold mb-2">No Shelters Found</h3>
            <p className="text-muted-foreground text-center">
              Try adjusting your search terms
            </p>
          </CardContent>
        </Card>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {shelters.map((shelter) => (
            <Card 
              key={shelter.shelter_id}
              className="group hover:shadow-lg transition-shadow cursor-pointer"
              onClick={() => navigate(`/shelters/${shelter.shelter_id}`)}
            >
              <CardHeader>
                <CardTitle className="flex items-center justify-between">
                  <span>{shelter.name}</span>
                  {shelter.is_no_kill && (
                    <Badge variant="success" className="whitespace-nowrap">
                      No-Kill Shelter
                    </Badge>
                  )}
                </CardTitle>
                <CardDescription>
                  <div className="flex items-center text-sm">
                    <MapPin className="h-4 w-4 mr-1" />
                    {shelter.address}
                  </div>
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  <div className="grid grid-cols-2 gap-4">
                    <div className="text-sm space-y-1">
                      <div className="flex items-center text-muted-foreground">
                        <Phone className="h-4 w-4 mr-1" />
                        {shelter.phone}
                      </div>
                      <div className="flex items-center text-muted-foreground">
                        <Mail className="h-4 w-4 mr-1" />
                        {shelter.email}
                      </div>
                    </div>
                    <div className="text-right">
                      <div className="flex items-center justify-end text-sm text-muted-foreground mb-1">
                        <PawPrint className="h-4 w-4 mr-1" />
                        {shelter.total_pets} pets
                      </div>
                    </div>
                  </div>
                  <Button className="w-full opacity-0 group-hover:opacity-100 transition-opacity">
                    View Details
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
