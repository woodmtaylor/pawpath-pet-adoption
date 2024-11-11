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

    async verifyEmail(token: string) {
        const response = await api.post<ApiResponse<{ message: string }>>('/auth/verify-email', { token });
        return response.data;
    }
};
