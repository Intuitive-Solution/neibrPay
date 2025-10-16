import { apiClient } from './apiClient';
import type {
  CreatePaymentRequest,
  UpdatePaymentRequest,
  Payment,
  PaymentListResponse,
} from '@neibrpay/models';

export interface PaymentFilters {
  invoice_id?: number;
  payment_method?: 'cash' | 'check' | 'credit_card' | 'bank_transfer' | 'other';
  start_date?: string;
  end_date?: string;
}

export const paymentsApi = {
  /**
   * List all payments with optional filters
   */
  list: async (filters?: PaymentFilters): Promise<Payment[]> => {
    const response = await apiClient.get<PaymentListResponse>('/payments', {
      params: filters,
    });
    return response.data.data;
  },

  /**
   * Create a new payment for an invoice
   */
  create: async (
    invoiceId: number,
    data: CreatePaymentRequest
  ): Promise<Payment> => {
    const response = await apiClient.post<{ data: Payment }>(
      `/invoices/${invoiceId}/payments`,
      data
    );
    return response.data.data;
  },

  /**
   * Get a specific payment by ID
   */
  get: async (id: number): Promise<Payment> => {
    const response = await apiClient.get<{ data: Payment }>(`/payments/${id}`);
    return response.data.data;
  },

  /**
   * Update an existing payment
   */
  update: async (id: number, data: UpdatePaymentRequest): Promise<Payment> => {
    const response = await apiClient.put<{ data: Payment }>(
      `/payments/${id}`,
      data
    );
    return response.data.data;
  },

  /**
   * Delete a payment
   */
  delete: async (id: number): Promise<void> => {
    await apiClient.delete(`/payments/${id}`);
  },
};
