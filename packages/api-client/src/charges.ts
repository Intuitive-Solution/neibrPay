import { apiClient } from './apiClient';
import type {
  CreateChargeDto,
  UpdateChargeDto,
  ChargeFilters,
  ChargeListResponse,
  ChargeResponse,
} from '@neibrpay/models';

export const chargesApi = {
  /**
   * Get all charges with optional filters
   */
  list: async (filters?: ChargeFilters): Promise<ChargeListResponse> => {
    const params = new URLSearchParams();

    if (filters?.category && filters.category !== '') {
      params.append('category', filters.category);
    }

    if (filters?.is_active !== undefined && filters.is_active !== '') {
      params.append('is_active', filters.is_active.toString());
    }

    if (filters?.include_deleted) {
      params.append('include_deleted', 'true');
    }

    if (filters?.search) {
      params.append('search', filters.search);
    }

    const queryString = params.toString();
    const url = queryString ? `/charges?${queryString}` : '/charges';

    const response = await apiClient.get(url);
    return response.data;
  },

  /**
   * Create a new charge
   */
  create: async (data: CreateChargeDto): Promise<ChargeResponse> => {
    const response = await apiClient.post('/charges', data);
    return response.data;
  },

  /**
   * Get a single charge by ID
   */
  get: async (id: number): Promise<ChargeResponse> => {
    const response = await apiClient.get(`/charges/${id}`);
    return response.data;
  },

  /**
   * Update a charge
   */
  update: async (
    id: number,
    data: UpdateChargeDto
  ): Promise<ChargeResponse> => {
    const response = await apiClient.put(`/charges/${id}`, data);
    return response.data;
  },

  /**
   * Delete a charge (soft delete)
   */
  delete: async (id: number): Promise<{ message: string }> => {
    const response = await apiClient.delete(`/charges/${id}`);
    return response.data;
  },

  /**
   * Restore a soft-deleted charge
   */
  restore: async (id: number): Promise<ChargeResponse> => {
    const response = await apiClient.post(`/charges/${id}/restore`);
    return response.data;
  },
};
