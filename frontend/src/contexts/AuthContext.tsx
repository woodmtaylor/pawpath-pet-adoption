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

    // Set up axios interceptor for authentication
    useEffect(() => {
        const token = getStoredToken();
        if (token) {
            api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        }

        // Add request interceptor
        const requestInterceptor = api.interceptors.request.use(
            (config) => {
                const token = getStoredToken();
                if (token) {
                    config.headers.Authorization = `Bearer ${token}`;
                }
                return config;
            },
            (error) => {
                return Promise.reject(error);
            }
        );

        // Add response interceptor
        const responseInterceptor = api.interceptors.response.use(
            (response) => response,
            async (error) => {
                if (error.response?.status === 401) {
                    // Token expired or invalid
                    logout();
                }
                return Promise.reject(error);
            }
        );

        return () => {
            // Clean up interceptors
            api.interceptors.request.eject(requestInterceptor);
            api.interceptors.response.eject(responseInterceptor);
        };
    }, []);

    // Initialize auth state from localStorage
    useEffect(() => {
        const initializeAuth = async () => {
            try {
                setIsLoading(true);
                const token = getStoredToken();
                const storedUser = getStoredUser();
                
                if (!token) {
                    setIsLoading(false);
                    return;
                }

                api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
                
                try {
                    // Verify token with backend
                    const response = await api.get('/auth/me');
                    const userData = response.data.data.user;
                    
                    if (JSON.stringify(userData) !== JSON.stringify(storedUser)) {
                        setStoredUser(userData);
                        setUser(userData);
                    } else {
                        setUser(storedUser);
                    }
                } catch (error) {
                    console.error('Token verification failed:', error);
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
    }, []);

    const login = async (email: string, password: string) => {
        try {
            const response = await api.post('/auth/login', { email, password });
            const { token, user: userData } = response.data.data;
            
            setStoredToken(token);
            api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            setStoredUser(userData);
            setUser(userData);
            
            console.log('Login successful, user:', userData);
        } catch (error) {
            console.error('Login error:', error);
            throw error;
        }
    };

    const logout = () => {
        removeStoredToken();
        removeStoredUser();
        delete api.defaults.headers.common['Authorization'];
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
