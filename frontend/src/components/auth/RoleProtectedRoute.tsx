import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '@/contexts/AuthContext';

interface RoleProtectedRouteProps {
  children: React.ReactNode;
  requiredRole: string;
}

export function RoleProtectedRoute({ children, requiredRole }: RoleProtectedRouteProps) {
  const { isAuthenticated, isLoading, user } = useAuth();
  const location = useLocation();

  if (isLoading) {
    return <div>Loading...</div>;
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" state={{ from: location }} replace />;
  }

  // Check role authorization
  const roleHierarchy = {
    'admin': ['admin'],
    'shelter_staff': ['admin', 'shelter_staff'],
    'adopter': ['admin', 'shelter_staff', 'adopter']
  };

  const userRole = user?.role || 'adopter';
  const allowedRoles = roleHierarchy[requiredRole] || [];

  if (!allowedRoles.includes(userRole)) {
    return <Navigate to="/unauthorized" replace />;
  }

  return <>{children}</>;
}
