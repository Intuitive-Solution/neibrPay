import { apiClient } from './apiClient';
import type {
  CreateVendorDto,
  UpdateVendorDto,
  VendorFilters,
  VendorListResponse,
  VendorResponse,
} from '@neibrpay/models';

export const vendorsApi = {
  /**
   * Get all vendors with optional filters
   */
  list: async (filters?: VendorFilters): Promise<VendorListResponse> => {
    const params = new URLSearchParams();

    if (filters?.category && filters.category !== '') {
      params.append('category', filters.category);
    }

    if (filters?.include_deleted) {
      params.append('include_deleted', 'true');
    }

    if (filters?.search) {
      params.append('search', filters.search);
    }

    const queryString = params.toString();
    const url = queryString ? `/vendors?${queryString}` : '/vendors';

    const response = await apiClient.get(url);
    return response.data;
  },

  /**
   * Create a new vendor
   */
  create: async (data: CreateVendorDto): Promise<VendorResponse> => {
    const response = await apiClient.post('/vendors', data);
    return response.data;
  },

  /**
   * Get a single vendor by ID
   */
  get: async (id: number): Promise<VendorResponse> => {
    const response = await apiClient.get(`/vendors/${id}`);
    return response.data;
  },

  /**
   * Update a vendor
   */
  update: async (
    id: number,
    data: UpdateVendorDto
  ): Promise<VendorResponse> => {
    const response = await apiClient.put(`/vendors/${id}`, data);
    return response.data;
  },

  /**
   * Delete a vendor (soft delete)
   */
  delete: async (id: number): Promise<{ message: string }> => {
    const response = await apiClient.delete(`/vendors/${id}`);
    return response.data;
  },

  /**
   * Restore a soft-deleted vendor
   */
  restore: async (id: number): Promise<VendorResponse> => {
    const response = await apiClient.post(`/vendors/${id}/restore`);
    return response.data;
  },
};
