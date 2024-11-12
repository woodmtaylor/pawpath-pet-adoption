import { createContext, useContext, useEffect, useState } from 'react';
import { useAtom } from 'jotai';
import { 
  userAtom, 
  authLoadingAtom,
  getStoredToken,
  setStoredToken,
  removeStoredToken,
  getStoredUser,
  setStoredUser,
  removeStoredUser,
  User 
} from '@/stores/auth';
import api from '@/lib/axios';

interface AuthContextType {
  isAuthenticated: boolean;
  user: User | null;
  login: (email: string, password: string) => Promise<void>;
  logout: () => void;
  isLoading: boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useAtom(userAtom);
  const [isLoading, setIsLoading] = useAtom(authLoadingAtom);

  // Setup axios interceptor for token expiration
  useEffect(() => {
    const interceptor = api.interceptors.response.use(
      (response) => response,
      (error) => {
        if (error.response?.status === 401) {
          // Token expired or invalid
          logout();
        }
        return Promise.reject(error);
      }
    );

    return () => {
      api.interceptors.response.eject(interceptor);
    };
  }, []);

  // Initialize auth state from localStorage
  useEffect(() => {
    const initializeAuth = async () => {
      try {
        setIsLoading(true);
        const token = getStoredToken();
        const storedUser = getStoredUser();
        
        if (token && storedUser) {
          // Set default authorization header
          api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
          
          try {
            // Verify token is still valid with backend
            const response = await api.get('/auth/me');
            const userData = response.data.user;
            
            // Update stored user data if different
            if (JSON.stringify(userData) !== JSON.stringify(storedUser)) {
              setStoredUser(userData);
              setUser(userData);
            } else {
              setUser(storedUser);
            }
          } catch (error) {
            // If verification fails, clear auth state
            console.error('Token verification failed:', error);
            logout();
          }
        } else {
          // No stored auth data
          logout();
        }
      } catch (error) {
        console.error('Auth initialization error:', error);
        logout();
      } finally {
        setIsLoading(false);
      }
    };

    initializeAuth();
  }, [setUser, setIsLoading]);

  const login = async (email: string, password: string) => {
      try {
          const response = await api.post('/auth/login', { email, password });
          const { token, user } = response.data.data;
          
          // Store token
          setStoredToken(token);
          
          // Update axios default header
          api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
          
          // Store user
          setStoredUser(user);
          setUser(user);
          
          // Log for debugging
          console.log('Login successful, token stored');
      } catch (error) {
          console.error('Login error:', error);
          throw error;
      }
  };

  useEffect(() => {
    const token = getStoredToken();
    if (token) {
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        console.log('Token restored from storage');
      }
  }, []);

  const logout = () => {
    // Clear stored auth data
    removeStoredToken();
    removeStoredUser();
    
    // Clear axios default header
    delete api.defaults.headers.common['Authorization'];
    
    // Reset state
    setUser(null);
  };

  return (
    <AuthContext.Provider 
      value={{ 
        isAuthenticated: !!user, 
        user, 
        login, 
        logout,
        isLoading 
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
