import axios from 'axios';
import type { ApiError } from '@neibrpay/models';

// Base API configuration
const API_BASE_URL =
  import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

// Create axios instance with default config
export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
  withCredentials: true, // Important for Sanctum
});

// Create separate axios instance for file uploads (without Content-Type header)
export const fileUploadClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    Accept: 'application/json',
  },
  withCredentials: true, // Important for Sanctum
});

// Token getter function (will be set by the app)
let getAuthToken: (() => string | null) | null = null;

export const setAuthTokenGetter = (tokenGetter: () => string | null) => {
  getAuthToken = tokenGetter;
};

// Add auth interceptor to both clients
const addAuthInterceptor = (client: typeof apiClient) => {
  client.interceptors.request.use(config => {
    if (getAuthToken) {
      const token = getAuthToken();
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
    } else {
      // Fallback to localStorage if token getter not set
      const token = localStorage.getItem('auth_token');
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
    }

    return config;
  });
};

addAuthInterceptor(apiClient);
addAuthInterceptor(fileUploadClient);

// Add response interceptor for error handling to both clients
const addErrorInterceptor = (client: typeof apiClient) => {
  client.interceptors.response.use(
    response => response,
    error => {
      // Handle 401 Unauthorized - token expired or invalid
      if (error.response?.status === 401) {
        // Clear token from localStorage
        localStorage.removeItem('auth_token');

        // Redirect to auth page if we're in a browser environment
        if (
          typeof window !== 'undefined' &&
          window.location.pathname !== '/auth'
        ) {
          window.location.href = '/auth';
        }
      }

      const apiError: ApiError = {
        message:
          error.response?.data?.message ||
          error.response?.data?.error ||
          'An unexpected error occurred',
        errors: error.response?.data?.errors,
      };

      return Promise.reject(apiError);
    }
  );
};

addErrorInterceptor(apiClient);
addErrorInterceptor(fileUploadClient);
