import axios from 'axios';
import { getStoredToken } from '@/stores/auth';

console.log('API Base URL:', import.meta.env.VITE_API_URL || '/api');

const api = axios.create({
    baseURL: '/api',  // This should match your backend API prefix
    headers: {
        'Content-Type': 'application/json',
    },
});

// Add request interceptor to include auth token
api.interceptors.request.use((config) => {
    const token = getStoredToken();
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
}, (error) => {
    return Promise.reject(error);
});

// Add response interceptor to handle image URLs
api.interceptors.response.use((response) => {
    // Function to process image URLs in an object
    const processImageUrls = (obj: any) => {
        if (!obj) return obj;
        
        if (Array.isArray(obj)) {
            return obj.map(item => processImageUrls(item));
        }
        
        if (typeof obj === 'object') {
            Object.keys(obj).forEach(key => {
                if (key === 'images' && Array.isArray(obj[key])) {
                    obj[key] = obj[key].map((image: any) => ({
                        ...image,
                        url: `${window.location.origin}${image.url}`
                    }));
                } else if (typeof obj[key] === 'object') {
                    obj[key] = processImageUrls(obj[key]);
                }
            });
        }
        return obj;
    };

    // Process the response data
    if (response.data && response.data.data) {
        response.data.data = processImageUrls(response.data.data);
    }

    return response;
}, (error) => {
    return Promise.reject(error);
});

export default api;
