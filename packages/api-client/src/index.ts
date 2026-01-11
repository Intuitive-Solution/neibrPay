// API Client exports
// This will contain the typed SDK with axios wrapper, endpoints, interceptors, and DTOs

export * from './residents';
export { unitsApi } from './units';
export { invoicesApi } from './invoices';
export { paymentsApi } from './payments';
export { chargesApi } from './charges';
export { expensesApi } from './expenses';
export { vendorsApi } from './vendors';
export { announcementsApi } from './announcements';
export { documentsApi } from './documents';
export { budgetApi } from './budget';
export {
  stripeApi,
  useVerifyStripeStatus,
  useDisconnectStripe,
  type StripeConnectResponse,
  type StripeConnectStatus,
} from './stripe';
export type {
  HoaDocument,
  HoaDocumentFolder,
  CreateDocumentRequest,
  UpdateDocumentRequest,
  CreateFolderRequest,
  UpdateFolderRequest,
} from './documents';
export {
  settingsApi,
  useSettings,
  useUpdateTenantSettings,
  useUpdateUserProfile,
  useUpdatePassword,
  useUpdateLocalization,
  type SettingsData,
  type UpdateTenantSettingsRequest,
  type UpdateUserProfileRequest,
  type UpdatePasswordRequest,
  type UpdateLocalizationRequest,
} from './settings';
export {
  plaidApi,
  usePlaidLinkToken,
  useBankAccounts,
  useTransactions,
  useExchangeToken,
  useDisconnectBankAccount,
  useSyncAccount,
  type BankAccount,
  type PlaidTransaction,
  type LinkTokenResponse,
  type ExchangeTokenRequest,
  type ExchangeTokenResponse,
  type GetBankAccountsResponse,
  type GetTransactionsRequest,
  type GetTransactionsResponse,
  type SyncAccountRequest,
  type SyncAccountResponse,
} from './plaid';
export * from './queryKeys';
export { announcementKeys, plaidKeys, budgetKeys } from './queryKeys';
export { apiClient, fileUploadClient, setAuthTokenGetter } from './apiClient';
