import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useNavigate, Link } from 'react-router-dom';
import { Button } from '@/components/ui/button';
import { Form } from '@/components/ui/form';
import { Alert, AlertDescription } from '@/components/ui/alert';
import AuthLayout from './AuthLayout';
import FormInput from './FormInput';

// Validation schema
const loginSchema = z.object({
  email: z.string().email('Invalid email address'),
  password: z.string().min(8, 'Password must be at least 8 characters')
});

type LoginFormData = z.infer<typeof loginSchema>;

const LoginPage = () => {
  const navigate = useNavigate();
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  
  const form = useForm<LoginFormData>({
    resolver: zodResolver(loginSchema),
    defaultValues: {
      email: '',
      password: ''
    }
  });

  const onSubmit = async (data: LoginFormData) => {
    try {
      setError(null);
      setIsLoading(true);
      
      // TODO: Implement login logic with authService
      console.log('Login data:', data);
      
      // Redirect to home page on success
      navigate('/');
    } catch (err) {
      setError(err instanceof Error ? err.message : 'An error occurred during login');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <AuthLayout 
      title="Welcome back" 
      description="Enter your email to sign in to your account"
    >
      <Form {...form}>
        <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
          {error && (
            <Alert variant="destructive">
              <AlertDescription>{error}</AlertDescription>
            </Alert>
          )}
          
          <FormInput
            form={form}
            name="email"
            label="Email"
            placeholder="Enter your email"
            type="email"
          />
          
          <FormInput
            form={form}
            name="password"
            label="Password"
            placeholder="Enter your password"
            type="password"
          />
          
          <Button 
            type="submit" 
            className="w-full"
            disabled={isLoading}
          >
            {isLoading ? 'Signing in...' : 'Sign in'}
          </Button>
          
          <div className="text-center text-sm">
            <Link 
              to="/forgot-password"
              className="text-primary hover:text-primary/90"
            >
              Forgot password?
            </Link>
          </div>
          
          <div className="text-center text-sm">
            Don't have an account?{' '}
            <Link 
              to="/register"
              className="text-primary hover:text-primary/90"
            >
              Sign up
            </Link>
          </div>
        </form>
      </Form>
    </AuthLayout>
  );
};

export default LoginPage;
