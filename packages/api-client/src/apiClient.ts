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
});

// Create separate axios instance for file uploads (without Content-Type header)
export const fileUploadClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    Accept: 'application/json',
  },
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
      const apiError: ApiError = {
        message:
          error.response?.data?.message || 'An unexpected error occurred',
        errors: error.response?.data?.errors,
      };

      return Promise.reject(apiError);
    }
  );
};

addErrorInterceptor(apiClient);
addErrorInterceptor(fileUploadClient);
