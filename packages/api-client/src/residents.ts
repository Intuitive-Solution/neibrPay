import { AxiosResponse } from 'axios';
import { apiClient } from './apiClient';
import type {
  Resident,
  CreateResidentRequest,
  UpdateResidentRequest,
  ResidentsResponse,
  ResidentResponse,
  Unit,
  UnitsResponse,
} from '@neibrpay/models';

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
   * Get units owned by a specific resident
   */
  async getResidentUnits(id: number): Promise<Unit[]> {
    const response: AxiosResponse<UnitsResponse> = await apiClient.get(
      `/residents/${id}/units`
    );
    return response.data.data;
  },

  /**
   * Remove a unit from a resident
   */
  async removeResidentUnit(residentId: number, unitId: number): Promise<void> {
    await apiClient.delete(`/residents/${residentId}/units/${unitId}`);
  },

  /**
   * Update the type of a unit for a resident
   */
  async updateResidentUnitType(
    residentId: number,
    unitId: number,
    type: 'owner' | 'tenant'
  ): Promise<void> {
    await apiClient.put(`/residents/${residentId}/units/${unitId}/type`, {
      type: type,
    });
  },

  /**
   * Get units available to be assigned to a resident
   */
  async getAvailableUnitsForResident(id: number): Promise<Unit[]> {
    const response: AxiosResponse<UnitsResponse> = await apiClient.get(
      `/residents/${id}/available-units`
    );
    return response.data.data;
  },

  /**
   * Add units to a resident
   */
  async addResidentUnits(
    residentId: number,
    units: Array<{ unit_id: number; type: 'owner' | 'tenant' }>
  ): Promise<void> {
    await apiClient.post(`/residents/${residentId}/units`, {
      units: units,
    });
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

  /**
   * Send invite email to a resident with welcome message and login link
   */
  async sendInviteEmail(id: number): Promise<void> {
    await apiClient.post(`/residents/${id}/send-invite`);
  },
};

export default residentsApi;
