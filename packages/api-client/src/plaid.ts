import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query';
import { apiClient } from './apiClient';
import { plaidKeys } from './queryKeys';

// Types
export interface BankAccount {
  id: number;
  account_name: string;
  account_mask: string;
  institution_name: string;
  status: 'active' | 'error' | 'disconnected';
  current_balance: number | null;
  available_balance: number | null;
  last_synced_at: string | null;
  sync_start_date: string | null;
}

export interface PlaidTransaction {
  id: number;
  tenant_id: number;
  plaid_bank_account_id: number;
  plaid_transaction_id: string;
  amount: number | string;
  date: string;
  name: string;
  merchant_name: string | null;
  category: string | null;
  categories: string[];
  pending: boolean;
  personal_finance_category?: string | Record<string, any>;
  bank_account?: {
    id: number;
    account_name: string;
    account_mask: string;
  };
  created_at: string;
  updated_at: string;
}

export interface LinkTokenResponse {
  link_token: string;
  expiration: string;
  request_id: string;
}

export interface ExchangeTokenRequest {
  public_token: string;
}

export interface ExchangeTokenResponse {
  message: string;
  bank_account: {
    id: number;
    account_name: string;
    account_mask: string;
    institution_name: string;
  };
}

export interface GetBankAccountsResponse {
  bank_accounts: BankAccount[];
}

export interface GetTransactionsRequest {
  bank_account_id?: number | null;
  start_date?: string | null;
  end_date?: string | null;
  search?: string | null;
  pending?: boolean | null;
  page?: number;
  per_page?: number;
  sort_by?:
    | 'date'
    | 'name'
    | 'amount'
    | 'category'
    | 'pending'
    | 'plaid_bank_account_id'
    | null;
  sort_order?: 'asc' | 'desc' | null;
}

export interface GetTransactionsResponse {
  data: PlaidTransaction[];
  pagination: {
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
    from: number;
    to: number;
  };
}

export interface SyncAccountRequest {
  bank_account_id: number;
}

export interface SyncAccountResponse {
  message: string;
  result: {
    success: boolean;
    synced_count: number;
    total_transactions: number;
    bank_account_id: number;
  };
}

// API Client
export const plaidApi = {
  /**
   * Create a Plaid Link token
   */
  async createLinkToken(): Promise<LinkTokenResponse> {
    const response =
      await apiClient.post<LinkTokenResponse>('/plaid/link-token');
    return response.data;
  },

  /**
   * Exchange public token for access token
   */
  async exchangeToken(
    data: ExchangeTokenRequest
  ): Promise<ExchangeTokenResponse> {
    const response = await apiClient.post<ExchangeTokenResponse>(
      '/plaid/exchange-token',
      data
    );
    return response.data;
  },

  /**
   * Get all connected bank accounts
   */
  async getBankAccounts(): Promise<GetBankAccountsResponse> {
    const response = await apiClient.get<GetBankAccountsResponse>(
      '/plaid/bank-accounts'
    );
    return response.data;
  },

  /**
   * Disconnect a bank account
   */
  async disconnectBankAccount(id: number): Promise<{ message: string }> {
    const response = await apiClient.delete<{ message: string }>(
      `/plaid/bank-accounts/${id}`
    );
    return response.data;
  },

  /**
   * Get transactions with filters and pagination
   */
  async getTransactions(
    params: GetTransactionsRequest
  ): Promise<GetTransactionsResponse> {
    const response = await apiClient.get<GetTransactionsResponse>(
      '/plaid/transactions',
      { params }
    );
    return response.data;
  },

  /**
   * Manually sync a specific bank account
   */
  async syncAccount(data: SyncAccountRequest): Promise<SyncAccountResponse> {
    const response = await apiClient.post<SyncAccountResponse>(
      '/plaid/sync',
      data
    );
    return response.data;
  },
};

// TanStack Query Hooks

/**
 * Hook to fetch the Link token for Plaid onboarding
 */
export function usePlaidLinkToken() {
  return useQuery({
    queryKey: plaidKeys.linkToken(),
    queryFn: () => plaidApi.createLinkToken(),
  });
}

/**
 * Hook to fetch connected bank accounts
 */
export function useBankAccounts() {
  return useQuery({
    queryKey: plaidKeys.bankAccounts(),
    queryFn: () => plaidApi.getBankAccounts(),
  });
}

/**
 * Hook to fetch transactions with filters - accepts computed ref or direct params
 */
export function useTransactions(params: GetTransactionsRequest | any) {
  // If it's a computed ref, extract the value reactively
  const getParams = () => (params && 'value' in params ? params.value : params);

  return useQuery({
    queryKey: () => plaidKeys.transactions(getParams()),
    queryFn: () => plaidApi.getTransactions(getParams()),
  });
}

/**
 * Hook to exchange public token for access token
 */
export function useExchangeToken() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: ExchangeTokenRequest) => plaidApi.exchangeToken(data),
    onSuccess: () => {
      // Invalidate bank accounts query to refresh the list
      queryClient.invalidateQueries({ queryKey: plaidKeys.bankAccounts() });
    },
  });
}

/**
 * Hook to disconnect a bank account
 */
export function useDisconnectBankAccount() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => plaidApi.disconnectBankAccount(id),
    onSuccess: () => {
      // Invalidate bank accounts and transactions queries
      queryClient.invalidateQueries({ queryKey: plaidKeys.bankAccounts() });
      queryClient.invalidateQueries({ queryKey: plaidKeys.all });
    },
  });
}

/**
 * Hook to manually sync a bank account
 */
export function useSyncAccount() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: SyncAccountRequest) => plaidApi.syncAccount(data),
    onSuccess: () => {
      // Invalidate transactions query to refresh the list
      queryClient.invalidateQueries({ queryKey: plaidKeys.all });
    },
  });
}
