import { AxiosResponse } from 'axios';
import { apiClient, fileUploadClient } from './apiClient';
import type {
  Unit,
  UnitWithResident,
  CreateUnitRequest,
  UpdateUnitRequest,
  UnitsResponse,
  UnitsWithResidentResponse,
  UnitResponse,
  UnitDocument,
  UnitDocumentsResponse,
  UnitDocumentResponse,
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
   * Get units with resident information for invoice creation
   */
  async getUnitsForInvoices(): Promise<UnitWithResident[]> {
    const response: AxiosResponse<UnitsWithResidentResponse> =
      await apiClient.get('/units/for-invoices');
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

  /**
   * Add owners to a unit
   */
  async addOwners(id: number, ownerIds: number[]): Promise<Unit> {
    const response: AxiosResponse<UnitResponse> = await apiClient.post(
      `/units/${id}/owners`,
      { owner_ids: ownerIds }
    );
    return response.data.data;
  },

  /**
   * Remove owners from a unit
   */
  async removeOwners(id: number, ownerIds: number[]): Promise<Unit> {
    const response: AxiosResponse<UnitResponse> = await apiClient.delete(
      `/units/${id}/owners`,
      { data: { owner_ids: ownerIds } }
    );
    return response.data.data;
  },

  /**
   * Sync owners for a unit (replace all owners)
   */
  async syncOwners(id: number, ownerIds: number[]): Promise<Unit> {
    const response: AxiosResponse<UnitResponse> = await apiClient.put(
      `/units/${id}/owners`,
      { owner_ids: ownerIds }
    );
    return response.data.data;
  },

  /**
   * Get all documents for a unit
   */
  async getDocuments(id: number): Promise<UnitDocument[]> {
    const response: AxiosResponse<UnitDocumentsResponse> = await apiClient.get(
      `/units/${id}/documents`
    );
    return response.data.data;
  },

  /**
   * Upload a document to a unit
   */
  async uploadDocument(
    id: number,
    file: File,
    description?: string
  ): Promise<UnitDocument> {
    const formData = new FormData();
    formData.append('file', file);
    if (description) {
      formData.append('description', description);
    }

    const response: AxiosResponse<UnitDocumentResponse> =
      await fileUploadClient.post(`/units/${id}/documents`, formData);
    return response.data.data;
  },

  /**
   * Get a specific document
   */
  async getDocument(unitId: number, documentId: number): Promise<UnitDocument> {
    const response: AxiosResponse<UnitDocumentResponse> = await apiClient.get(
      `/units/${unitId}/documents/${documentId}`
    );
    return response.data.data;
  },

  /**
   * Download a document
   */
  async downloadDocument(unitId: number, documentId: number): Promise<Blob> {
    const response = await apiClient.get(
      `/units/${unitId}/documents/${documentId}/download`,
      {
        responseType: 'blob',
      }
    );
    return response.data;
  },

  /**
   * Delete a document
   */
  async deleteDocument(unitId: number, documentId: number): Promise<void> {
    await apiClient.delete(`/units/${unitId}/documents/${documentId}`);
  },

  /**
   * Permanently delete a document
   */
  async forceDeleteDocument(unitId: number, documentId: number): Promise<void> {
    await apiClient.delete(`/units/${unitId}/documents/${documentId}/force`);
  },
};

export default unitsApi;
