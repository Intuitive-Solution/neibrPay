import { apiClient } from './apiClient';
import type {
  InvoiceUnit,
  CreateInvoiceRequest,
  UpdateInvoiceRequest,
} from '@neibrpay/models';

export interface InvoiceListResponse {
  data: InvoiceUnit[];
  meta: {
    total: number;
    include_deleted: boolean;
    filters: {
      unit_id?: number;
      status?: string;
    };
  };
}

export interface InvoiceResponse {
  data: InvoiceUnit;
}

export interface CreateInvoiceResponse {
  data: InvoiceUnit[];
  message: string;
}

export const invoicesApi = {
  /**
   * Get all invoices with optional filters
   */
  async getInvoices(params?: {
    include_deleted?: boolean;
    unit_id?: number;
    status?: string;
  }): Promise<InvoiceListResponse> {
    const response = await apiClient.get('/invoices', { params });
    return response.data;
  },

  /**
   * Get a specific invoice by ID
   */
  async getInvoice(id: number): Promise<InvoiceResponse> {
    const response = await apiClient.get(`/invoices/${id}`);
    return response.data;
  },

  /**
   * Create new invoice(s)
   */
  async createInvoice(
    data: CreateInvoiceRequest
  ): Promise<CreateInvoiceResponse> {
    const response = await apiClient.post('/invoices', data);
    return response.data;
  },

  /**
   * Update an existing invoice
   */
  async updateInvoice(
    id: number,
    data: UpdateInvoiceRequest
  ): Promise<InvoiceResponse> {
    const response = await apiClient.put(`/invoices/${id}`, data);
    return response.data;
  },

  /**
   * Delete an invoice (soft delete)
   */
  async deleteInvoice(id: number): Promise<{ message: string }> {
    const response = await apiClient.delete(`/invoices/${id}`);
    return response.data;
  },

  /**
   * Restore a soft-deleted invoice
   */
  async restoreInvoice(id: number): Promise<InvoiceResponse> {
    const response = await apiClient.post(`/invoices/${id}/restore`);
    return response.data;
  },

  /**
   * Permanently delete an invoice
   */
  async forceDeleteInvoice(id: number): Promise<{ message: string }> {
    const response = await apiClient.delete(`/invoices/${id}/force`);
    return response.data;
  },

  /**
   * Mark an invoice as sent
   */
  async markInvoiceAsSent(id: number): Promise<InvoiceResponse> {
    const response = await apiClient.post(`/invoices/${id}/mark-sent`);
    return response.data;
  },

  /**
   * Get invoices for a specific unit
   */
  async getInvoicesForUnit(unitId: number): Promise<InvoiceListResponse> {
    const response = await apiClient.get(`/units/${unitId}/invoices`);
    return response.data;
  },
};
