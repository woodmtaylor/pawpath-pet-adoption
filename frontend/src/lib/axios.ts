import axios from 'axios';
import { getStoredToken } from '@/stores/auth';

const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
  },
});

// Add a request interceptor to add the auth token to requests
api.interceptors.request.use((config) => {
  const token = getStoredToken();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Add a response interceptor to handle response formatting and errors
api.interceptors.response.use(
  (response) => {
    // For auth endpoints, don't modify the response structure
    if (response.config.url?.includes('/auth/')) {
      return response;
    }
    
    // For other endpoints, standardize the response
    if (response.data.hasOwnProperty('success')) {
      return response;
    }
    
    return {
      ...response,
      data: {
        success: true,
        data: response.data
      }
    };
  },
  (error) => {
    // Format error response
    const errorMessage = error.response?.data?.error || error.message || 'An error occurred';
    return Promise.reject({
      response: {
        ...error.response,
        data: {
          success: false,
          error: errorMessage
        }
      }
    });
  }
);

export default api;
