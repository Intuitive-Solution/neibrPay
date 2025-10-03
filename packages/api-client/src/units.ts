import { AxiosResponse } from 'axios';
import { apiClient } from './apiClient';
import type {
  Unit,
  CreateUnitRequest,
  UpdateUnitRequest,
  UnitsResponse,
  UnitResponse,
} from '@neibrpay/models';

// Unit API functions
export const unitsApi = {
  /**
   * Get all units for the current tenant
   */
  async getUnits(includeDeleted = false): Promise<Unit[]> {
    const response: AxiosResponse<UnitsResponse> = await apiClient.get(
      '/units',
      {
        params: { include_deleted: includeDeleted },
      }
    );
    return response.data.data;
  },

  /**
   * Get a specific unit by ID
   */
  async getUnit(id: number): Promise<Unit> {
    const response: AxiosResponse<UnitResponse> = await apiClient.get(
      `/units/${id}`
    );
    return response.data.data;
  },

  /**
   * Create a new unit
   */
  async createUnit(data: CreateUnitRequest): Promise<Unit> {
    const response: AxiosResponse<UnitResponse> = await apiClient.post(
      '/units',
      data
    );
    return response.data.data;
  },

  /**
   * Update an existing unit
   */
  async updateUnit(id: number, data: UpdateUnitRequest): Promise<Unit> {
    const response: AxiosResponse<UnitResponse> = await apiClient.put(
      `/units/${id}`,
      data
    );
    return response.data.data;
  },

  /**
   * Soft delete a unit
   */
  async deleteUnit(id: number): Promise<void> {
    await apiClient.delete(`/units/${id}`);
  },

  /**
   * Restore a soft-deleted unit
   */
  async restoreUnit(id: number): Promise<Unit> {
    const response: AxiosResponse<UnitResponse> = await apiClient.post(
      `/units/${id}/restore`
    );
    return response.data.data;
  },

  /**
   * Permanently delete a unit (admin only)
   */
  async forceDeleteUnit(id: number): Promise<void> {
    await apiClient.delete(`/units/${id}/force`);
  },
};

export default unitsApi;
