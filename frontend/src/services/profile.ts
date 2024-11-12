import api from '@/lib/axios';
import { UserProfile, ProfileUpdateData } from '@/types/profile';
import { ApiResponse } from '@/types/api';

export const profileService = {
    async getProfile() {
        const response = await api.get<ApiResponse<UserProfile>>('/profile');
        return response.data.data;
    },

    async updateProfile(data: ProfileUpdateData) {
        const response = await api.put<ApiResponse<UserProfile>>('/profile', data);
        return response.data.data;
    },
    
    async updateUserRole(userId: number, role: string) {
        const response = await api.put<ApiResponse<UserProfile>>(`/admin/users/${userId}/role`, { role });
        return response.data.data;
    },
    
    async getUsersByRole(role: string) {
        const response = await api.get<ApiResponse<UserProfile[]>>(`/admin/users`, {
            params: { role }
        });
        return response.data.data;
    },
    
    async getShelterStaff(shelterId: number) {
        const response = await api.get<ApiResponse<UserProfile[]>>(`/shelters/${shelterId}/staff`);
        return response.data.data;
    },

    async verifyEmail(token: string) {
        const response = await api.post<ApiResponse<{ message: string }>>('/auth/verify-email', { token });
        return response.data;
    },
    
    hasPermission(requiredRole: string): boolean {
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        const roles = {
            'admin': ['admin'],
            'shelter_staff': ['admin', 'shelter_staff'],
            'adopter': ['admin', 'shelter_staff', 'adopter']
        };
        return roles[requiredRole]?.includes(user.role) || false;
    }
};
