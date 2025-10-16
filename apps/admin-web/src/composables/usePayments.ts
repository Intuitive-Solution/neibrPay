import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query';
import { paymentsApi, paymentKeys, invoiceKeys } from '@neibrpay/api-client';
import type {
  CreatePaymentRequest,
  UpdatePaymentRequest,
  Payment,
  PaymentFilters,
} from '@neibrpay/models';

/**
 * Hook to fetch payments with optional filters
 */
export const usePayments = (filters?: PaymentFilters) => {
  return useQuery({
    queryKey: paymentKeys.list(filters),
    queryFn: () => paymentsApi.list(filters),
  });
};

/**
 * Hook to fetch a single payment by ID
 */
export const usePayment = (id: number) => {
  return useQuery({
    queryKey: paymentKeys.detail(id),
    queryFn: () => paymentsApi.get(id),
    enabled: !!id,
  });
};

/**
 * Hook to create a new payment
 */
export const useCreatePayment = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({
      invoiceId,
      data,
    }: {
      invoiceId: number;
      data: CreatePaymentRequest;
    }) => paymentsApi.create(invoiceId, data),
    onSuccess: (_, variables) => {
      // Invalidate payment lists and invoice details
      queryClient.invalidateQueries({ queryKey: paymentKeys.lists() });
      queryClient.invalidateQueries({
        queryKey: invoiceKeys.detail(variables.invoiceId),
      });
      queryClient.invalidateQueries({ queryKey: invoiceKeys.lists() });
    },
  });
};

/**
 * Hook to update an existing payment
 */
export const useUpdatePayment = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, data }: { id: number; data: UpdatePaymentRequest }) =>
      paymentsApi.update(id, data),
    onSuccess: updatedPayment => {
      // Invalidate payment lists and specific payment detail
      queryClient.invalidateQueries({ queryKey: paymentKeys.lists() });
      queryClient.invalidateQueries({
        queryKey: paymentKeys.detail(updatedPayment.id),
      });

      // Invalidate invoice details if we have the invoice ID
      if (updatedPayment.invoice_unit_id) {
        queryClient.invalidateQueries({
          queryKey: invoiceKeys.detail(updatedPayment.invoice_unit_id),
        });
        queryClient.invalidateQueries({ queryKey: invoiceKeys.lists() });
      }
    },
  });
};

/**
 * Hook to delete a payment
 */
export const useDeletePayment = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => paymentsApi.delete(id),
    onSuccess: (_, paymentId) => {
      // Invalidate payment lists and remove from cache
      queryClient.invalidateQueries({ queryKey: paymentKeys.lists() });
      queryClient.removeQueries({ queryKey: paymentKeys.detail(paymentId) });

      // Invalidate all invoice queries since payment deletion affects invoice totals
      queryClient.invalidateQueries({ queryKey: invoiceKeys.lists() });
    },
  });
};
