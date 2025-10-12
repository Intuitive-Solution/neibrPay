import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, unref, type Ref } from 'vue';
import { invoicesApi } from '@neibrpay/api-client';
import type {
  CreateInvoiceRequest,
  UpdateInvoiceRequest,
} from '@neibrpay/models';
import { invoicePdfKeys } from './useInvoicePdf';

// Query keys
export const invoiceQueryKeys = {
  all: ['invoices'] as const,
  lists: () => [...invoiceQueryKeys.all, 'list'] as const,
  list: (filters: Record<string, any>) =>
    [...invoiceQueryKeys.lists(), filters] as const,
  details: () => [...invoiceQueryKeys.all, 'detail'] as const,
  detail: (id: number) => [...invoiceQueryKeys.details(), id] as const,
  unitInvoices: (unitId: number) =>
    [...invoiceQueryKeys.all, 'unit', unitId] as const,
};

// Get all invoices with optional filters
export function useInvoices(
  filters: {
    include_deleted?: boolean | Ref<boolean>;
    unit_id?: number | Ref<number>;
    status?: string | Ref<string>;
  } = {}
) {
  return useQuery({
    queryKey: computed(() => {
      const unwrappedFilters = {
        include_deleted: unref(filters.include_deleted),
        unit_id: unref(filters.unit_id),
        status: unref(filters.status),
      };
      return invoiceQueryKeys.list(unwrappedFilters);
    }),
    queryFn: () => {
      const unwrappedFilters = {
        include_deleted: unref(filters.include_deleted),
        unit_id: unref(filters.unit_id),
        status: unref(filters.status),
      };
      return invoicesApi.getInvoices(unwrappedFilters);
    },
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
}

// Get a specific invoice
export function useInvoice(id: number) {
  return useQuery({
    queryKey: invoiceQueryKeys.detail(id),
    queryFn: () => invoicesApi.getInvoice(id),
    enabled: !!id,
  });
}

// Get invoices for a specific unit
export function useUnitInvoices(unitId: number) {
  return useQuery({
    queryKey: invoiceQueryKeys.unitInvoices(unitId),
    queryFn: () => invoicesApi.getInvoicesForUnit(unitId),
    enabled: !!unitId,
  });
}

// Create invoice mutation
export function useCreateInvoice() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: CreateInvoiceRequest) => invoicesApi.createInvoice(data),
    onSuccess: () => {
      // Invalidate and refetch invoice lists
      queryClient.invalidateQueries({ queryKey: invoiceQueryKeys.lists() });
    },
  });
}

// Update invoice mutation
export function useUpdateInvoice() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, data }: { id: number; data: UpdateInvoiceRequest }) =>
      invoicesApi.updateInvoice(id, data),
    onSuccess: (response, { id }) => {
      // Update the specific invoice in cache
      queryClient.setQueryData(invoiceQueryKeys.detail(id), response);
      // Invalidate lists to ensure consistency
      queryClient.invalidateQueries({ queryKey: invoiceQueryKeys.lists() });
    },
  });
}

// Delete invoice mutation
export function useDeleteInvoice() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => invoicesApi.deleteInvoice(id),
    onSuccess: (_, id) => {
      // Remove from cache
      queryClient.removeQueries({ queryKey: invoiceQueryKeys.detail(id) });
      // Invalidate lists
      queryClient.invalidateQueries({ queryKey: invoiceQueryKeys.lists() });
    },
  });
}

// Restore invoice mutation
export function useRestoreInvoice() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => invoicesApi.restoreInvoice(id),
    onSuccess: (response, id) => {
      // Update the specific invoice in cache
      queryClient.setQueryData(invoiceQueryKeys.detail(id), response);
      // Invalidate lists
      queryClient.invalidateQueries({ queryKey: invoiceQueryKeys.lists() });
    },
  });
}

// Mark invoice as sent mutation
export function useMarkInvoiceAsSent() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => invoicesApi.markInvoiceAsSent(id),
    onSuccess: (response, id) => {
      // Update the specific invoice in cache
      queryClient.setQueryData(invoiceQueryKeys.detail(id), response);
      // Invalidate lists
      queryClient.invalidateQueries({ queryKey: invoiceQueryKeys.lists() });
    },
  });
}

// Force delete invoice mutation
export function useForceDeleteInvoice() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => invoicesApi.forceDeleteInvoice(id),
    onSuccess: (_, id) => {
      // Remove from cache
      queryClient.removeQueries({ queryKey: invoiceQueryKeys.detail(id) });
      // Invalidate lists
      queryClient.invalidateQueries({ queryKey: invoiceQueryKeys.lists() });
    },
  });
}

// Mark invoice as paid mutation
export function useMarkInvoiceAsPaid() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => invoicesApi.markInvoiceAsPaid(id),
    onSuccess: (response, id) => {
      // Update the specific invoice in cache
      queryClient.setQueryData(invoiceQueryKeys.detail(id), response);
      // Invalidate lists to ensure consistency
      queryClient.invalidateQueries({ queryKey: invoiceQueryKeys.lists() });
      // Invalidate PDF queries to refresh PDF viewer with payment details
      queryClient.invalidateQueries({ queryKey: invoicePdfKeys.latest(id) });
      queryClient.invalidateQueries({ queryKey: invoicePdfKeys.versions(id) });
    },
  });
}

// Clone invoice mutation
export function useCloneInvoice() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => invoicesApi.cloneInvoice(id),
    onSuccess: () => {
      // Invalidate lists to show the new cloned invoice
      queryClient.invalidateQueries({ queryKey: invoiceQueryKeys.lists() });
    },
  });
}

// Email invoice mutation
export function useEmailInvoice() {
  return useMutation({
    mutationFn: ({ id, email }: { id: number; email?: string }) =>
      invoicesApi.emailInvoice(id, email),
  });
}
