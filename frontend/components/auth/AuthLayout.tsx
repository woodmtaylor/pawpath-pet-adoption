import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';

const AuthLayout = ({ 
  children,
  title,
  description
}: { 
  children: React.ReactNode;
  title: string;
  description?: string;
}) => {
  return (
    <div className="min-h-screen w-full flex items-center justify-center bg-background p-4">
      <Card className="w-full max-w-md">
        <CardHeader className="space-y-1">
          <CardTitle className="text-2xl text-center">{title}</CardTitle>
          {description && (
            <CardDescription className="text-center">
              {description}
            </CardDescription>
          )}
        </CardHeader>
        <CardContent>
          {children}
        </CardContent>
      </Card>
    </div>
  );
};

export default AuthLayout;
