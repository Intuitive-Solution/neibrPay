import axios, { AxiosResponse } from 'axios';
import type {
  Resident,
  CreateResidentRequest,
  UpdateResidentRequest,
  ResidentsResponse,
  ResidentResponse,
  ApiError,
} from '@neibrpay/models';

// Base API configuration
const API_BASE_URL =
  (import.meta as any).env?.VITE_API_BASE_URL || 'http://localhost:8000/api';

// Create axios instance with default config
const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
});

// Token getter function (will be set by the app)
let getAuthToken: (() => string | null) | null = null;

export const setAuthTokenGetter = (tokenGetter: () => string | null) => {
  getAuthToken = tokenGetter;
};

// Add auth interceptor
apiClient.interceptors.request.use(config => {
  const token = getAuthToken ? getAuthToken() : null;
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Add response interceptor for error handling
apiClient.interceptors.response.use(
  response => response,
  error => {
    const apiError: ApiError = {
      message: error.response?.data?.message || 'An unexpected error occurred',
      errors: error.response?.data?.errors,
    };
    return Promise.reject(apiError);
  }
);

// Resident API functions
export const residentsApi = {
  /**
   * Get all residents for the current tenant
   */
  async getResidents(includeDeleted = false): Promise<Resident[]> {
    const response: AxiosResponse<ResidentsResponse> = await apiClient.get(
      '/residents',
      {
        params: { include_deleted: includeDeleted },
      }
    );
    return response.data.data;
  },

  /**
   * Get a specific resident by ID
   */
  async getResident(id: number): Promise<Resident> {
    const response: AxiosResponse<ResidentResponse> = await apiClient.get(
      `/residents/${id}`
    );
    return response.data.data;
  },

  /**
   * Create a new resident
   */
  async createResident(data: CreateResidentRequest): Promise<Resident> {
    const response: AxiosResponse<ResidentResponse> = await apiClient.post(
      '/residents',
      data
    );
    return response.data.data;
  },

  /**
   * Update an existing resident
   */
  async updateResident(
    id: number,
    data: UpdateResidentRequest
  ): Promise<Resident> {
    const response: AxiosResponse<ResidentResponse> = await apiClient.put(
      `/residents/${id}`,
      data
    );
    return response.data.data;
  },

  /**
   * Soft delete a resident
   */
  async deleteResident(id: number): Promise<void> {
    await apiClient.delete(`/residents/${id}`);
  },

  /**
   * Restore a soft-deleted resident
   */
  async restoreResident(id: number): Promise<Resident> {
    const response: AxiosResponse<ResidentResponse> = await apiClient.post(
      `/residents/${id}/restore`
    );
    return response.data.data;
  },

  /**
   * Permanently delete a resident (admin only)
   */
  async forceDeleteResident(id: number): Promise<void> {
    await apiClient.delete(`/residents/${id}/force`);
  },
};

export default residentsApi;
