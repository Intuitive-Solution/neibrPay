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
  }): Promise<InvoiceUnit[]> {
    const response = await apiClient.get('/invoices', { params });
    return response.data.data; // Extract the data array from the response
  },

  /**
   * Get a specific invoice by ID
   */
  async getInvoice(id: number): Promise<InvoiceUnit> {
    const response = await apiClient.get(`/invoices/${id}`);
    return response.data.data; // Extract the data object from the response
  },

  /**
   * Create new invoice(s)
   */
  async createInvoice(data: CreateInvoiceRequest): Promise<InvoiceUnit[]> {
    const response = await apiClient.post('/invoices', data);
    return response.data.data; // Extract the data array from the response
  },

  /**
   * Update an existing invoice
   */
  async updateInvoice(
    id: number,
    data: UpdateInvoiceRequest
  ): Promise<InvoiceUnit> {
    const response = await apiClient.put(`/invoices/${id}`, data);
    return response.data.data; // Extract the data object from the response
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
  async restoreInvoice(id: number): Promise<InvoiceUnit> {
    const response = await apiClient.post(`/invoices/${id}/restore`);
    return response.data.data; // Extract the data object from the response
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
  async markInvoiceAsSent(id: number): Promise<InvoiceUnit> {
    const response = await apiClient.post(`/invoices/${id}/mark-sent`);
    return response.data.data; // Extract the data object from the response
  },

  /**
   * Get invoices for a specific unit
   */
  async getInvoicesForUnit(unitId: number): Promise<InvoiceUnit[]> {
    const response = await apiClient.get(`/units/${unitId}/invoices`);
    return response.data.data; // Extract the data array from the response
  },

  /**
   * Get attachments for a specific invoice
   */
  async getInvoiceAttachments(invoiceId: number): Promise<any[]> {
    const response = await apiClient.get(`/invoices/${invoiceId}/attachments`);
    return response.data.data; // Extract the data array from the response
  },

  /**
   * Upload an attachment to an invoice
   */
  async uploadInvoiceAttachment(
    invoiceId: number,
    file: File,
    description?: string
  ): Promise<any> {
    const formData = new FormData();
    formData.append('file', file);
    if (description) {
      formData.append('description', description);
    }

    const response = await apiClient.post(
      `/invoices/${invoiceId}/attachments`,
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      }
    );
    return response.data.data; // Extract the data object from the response
  },

  /**
   * Download an invoice attachment
   */
  async downloadInvoiceAttachment(
    invoiceId: number,
    attachmentId: number
  ): Promise<Blob> {
    const response = await apiClient.get(
      `/invoices/${invoiceId}/attachments/${attachmentId}/download`,
      {
        responseType: 'blob',
      }
    );
    return response.data;
  },

  /**
   * Delete an invoice attachment
   */
  async deleteInvoiceAttachment(
    invoiceId: number,
    attachmentId: number
  ): Promise<{ message: string }> {
    const response = await apiClient.delete(
      `/invoices/${invoiceId}/attachments/${attachmentId}`
    );
    return response.data;
  },

  /**
   * Mark an invoice as paid
   */
  async markInvoiceAsPaid(id: number): Promise<InvoiceUnit> {
    const response = await apiClient.post(`/invoices/${id}/mark-paid`);
    return response.data.data;
  },

  /**
   * Clone an existing invoice
   */
  async cloneInvoice(id: number): Promise<InvoiceUnit[]> {
    const response = await apiClient.post(`/invoices/${id}/clone`);
    return response.data.data;
  },

  /**
   * Email an invoice
   */
  async emailInvoice(id: number, email?: string): Promise<{ message: string }> {
    const response = await apiClient.post(`/invoices/${id}/email`, {
      email,
    });
    return response.data;
  },
};
