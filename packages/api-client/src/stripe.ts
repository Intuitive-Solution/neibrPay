import { useMutation, useQueryClient } from '@tanstack/vue-query';
import { apiClient } from './apiClient';
import { settingsKeys } from './queryKeys';

export interface StripeConnectResponse {
  message: string;
  stripe_connect_id: string;
  onboarding_url?: string;
  dashboard_url?: string;
  charges_enabled?: boolean;
  details_submitted?: boolean;
  status?: string;
}

export interface StripeConnectStatus {
  stripe_connect_id: string;
  charges_enabled: boolean;
  details_submitted: boolean;
  status: 'active' | 'pending' | 'not_connected';
}

export const stripeApi = {
  /**
   * Initiate Stripe Connect Express account creation
   */
  connect: async (): Promise<StripeConnectResponse> => {
    const response = await apiClient.post('/stripe/connect');
    return response.data;
  },

  /**
   * Get a login link to the Stripe Express dashboard
   */
  getDashboardLink: async (): Promise<StripeConnectResponse> => {
    const response = await apiClient.post('/stripe/dashboard');
    return response.data;
  },

  /**
   * Verify Stripe Connect account status
   */
  verifyStatus: async (): Promise<StripeConnectStatus> => {
    const response = await apiClient.post('/stripe/verify');
    return response.data;
  },

  /**
   * Disconnect and delete Stripe Connect account
   */
  disconnect: async (): Promise<{ message: string }> => {
    const response = await apiClient.post('/stripe/disconnect');
    return response.data;
  },
};

/**
 * TanStack Query mutation to disconnect Stripe account
 * Automatically invalidates settings query on success
 */
export function useDisconnectStripe() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: () => stripeApi.disconnect(),
    onSuccess: () => {
      // Invalidate settings query to refresh Stripe connection status
      queryClient.invalidateQueries({ queryKey: settingsKeys.detail() });
    },
  });
}
