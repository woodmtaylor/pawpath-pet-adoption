import { useNavigate } from 'react-router-dom';
import { Button } from '@/components/ui/button';
import { Shield, Home } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';

export default function UnauthorizedPage() {
  const navigate = useNavigate();

  return (
    <div className="container max-w-md mx-auto px-4 py-16">
      <Card>
        <CardContent className="pt-6 text-center">
          <Shield className="h-16 w-16 mx-auto mb-6 text-muted-foreground" />
          
          <h1 className="text-2xl font-bold mb-4">
            Access Denied
          </h1>
          
          <p className="text-muted-foreground mb-8">
            You don't have permission to access this page. Please contact your administrator if you believe this is an error.
          </p>
          
          <div className="space-x-4">
            <Button 
              variant="default"
              onClick={() => navigate(-1)}
            >
              Go Back
            </Button>
            
            <Button 
              variant="outline"
              onClick={() => navigate('/')}
            >
              <Home className="mr-2 h-4 w-4" />
              Home
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
