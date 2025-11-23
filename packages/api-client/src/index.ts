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
export {
  stripeApi,
  type StripeConnectResponse,
  type StripeConnectStatus,
} from './stripe';
export type {
  HoaDocument,
  CreateDocumentRequest,
  UpdateDocumentRequest,
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
export * from './queryKeys';
export { announcementKeys } from './queryKeys';
export { apiClient, fileUploadClient, setAuthTokenGetter } from './apiClient';
