import api from '@/lib/axios'

interface LoginCredentials {
  email: string
  password: string
}

interface RegisterCredentials {
  username: string
  email: string
  password: string
}

interface AuthResponse {
  user: {
    user_id: number
    username: string
    email: string
  }
  token: string
}

export const authService = {
    async login(credentials: LoginCredentials) {
        const response = await api.post<ApiResponse<AuthResponse>>('/auth/login', credentials);
        return response.data.data || response.data;
    },

    async register(credentials: RegisterCredentials) {
        const response = await api.post<ApiResponse<AuthResponse>>('/auth/register', credentials);
        return response.data.data || response.data;
    },

    async logout() {
        localStorage.removeItem('token');
    }
}
