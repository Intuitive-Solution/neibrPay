import { apiClient } from './apiClient';
import type {
  CreatePaymentRequest,
  UpdatePaymentRequest,
  Payment,
  PaymentListResponse,
} from '@neibrpay/models';

export interface PaymentFilters {
  invoice_id?: number;
  payment_method?:
    | 'cash'
    | 'check'
    | 'credit_card'
    | 'bank_transfer'
    | 'stripe_card'
    | 'stripe_ach'
    | 'other';
  start_date?: string;
  end_date?: string;
}

export interface StripeCheckoutResponse {
  checkout_url: string;
  session_id: string;
  payment_id: number;
}

export interface StripePaymentStatus {
  has_pending_payments: boolean;
  pending_payments: Array<{
    id: number;
    amount: number;
    session_id: string;
    created_at: string;
  }>;
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

  /**
   * Create a Stripe Checkout session for an invoice
   */
  createStripeCheckout: async (
    invoiceId: number,
    amount?: number
  ): Promise<StripeCheckoutResponse> => {
    const response = await apiClient.post<{ data: StripeCheckoutResponse }>(
      `/invoices/${invoiceId}/stripe/checkout`,
      amount ? { amount } : {}
    );
    return response.data.data;
  },

  /**
   * Get Stripe payment status for an invoice
   */
  getStripePaymentStatus: async (
    invoiceId: number
  ): Promise<StripePaymentStatus> => {
    const response = await apiClient.get<{ data: StripePaymentStatus }>(
      `/invoices/${invoiceId}/stripe/status`
    );
    return response.data.data;
  },
};
