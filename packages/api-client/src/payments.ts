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

export interface FeeCalculation {
  invoice_amount: number;
  card: {
    processing_fee: number;
    total: number;
    breakdown: {
      platform_fee: number;
      stripe_fee: number;
    };
  };
  ach: {
    processing_fee: number;
    total: number;
    breakdown: {
      platform_fee: number;
      stripe_fee: number;
    };
  };
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
   * Calculate fees for an invoice payment
   */
  calculateFees: async (
    invoiceId: number,
    amount?: number
  ): Promise<FeeCalculation> => {
    const response = await apiClient.post<{ data: FeeCalculation }>(
      `/invoices/${invoiceId}/calculate-fees`,
      amount ? { amount } : {}
    );
    return response.data.data;
  },

  /**
   * Create a Stripe Checkout session for an invoice
   */
  createStripeCheckout: async (
    invoiceId: number,
    paymentMethod: 'card' | 'ach' = 'card',
    amount?: number
  ): Promise<StripeCheckoutResponse> => {
    const response = await apiClient.post<{ data: StripeCheckoutResponse }>(
      `/invoices/${invoiceId}/stripe/checkout`,
      {
        payment_method: paymentMethod,
        ...(amount && { amount }),
      }
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

  /**
   * Approve a payment (admin only)
   */
  approve: async (
    paymentId: number,
    data: {
      admin_comment_public?: string;
      admin_comment_private?: string;
    }
  ): Promise<Payment> => {
    const response = await apiClient.post<{ data: Payment }>(
      `/payments/${paymentId}/approve`,
      data
    );
    return response.data.data;
  },

  /**
   * Reject a payment (admin only)
   */
  reject: async (
    paymentId: number,
    data: {
      admin_comment_public: string;
      admin_comment_private?: string;
    }
  ): Promise<Payment> => {
    const response = await apiClient.post<{ data: Payment }>(
      `/payments/${paymentId}/reject`,
      data
    );
    return response.data.data;
  },

  /**
   * Resubmit a rejected payment (resident only)
   */
  resubmit: async (
    paymentId: number,
    data: UpdatePaymentRequest
  ): Promise<Payment> => {
    const response = await apiClient.post<{ data: Payment }>(
      `/payments/${paymentId}/resubmit`,
      data
    );
    return response.data.data;
  },
};
