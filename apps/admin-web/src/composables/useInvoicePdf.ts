import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import { invoicesApi } from '@neibrpay/api-client';
import type { InvoicePdf } from '@neibrpay/models';

// Query keys for invoice PDFs
export const invoicePdfKeys = {
  all: ['invoice-pdfs'] as const,
  latest: (invoiceId: number) =>
    [...invoicePdfKeys.all, 'latest', invoiceId] as const,
};

/**
 * Generate PDF for an invoice
 */
export function useGenerateInvoicePdf() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({
      invoiceId,
      html,
    }: {
      invoiceId: number;
      html: string;
    }) => {
      return await invoicesApi.generateInvoicePdf(invoiceId, html);
    },
    onSuccess: (data, variables) => {
      // Invalidate and refetch the latest PDF query
      queryClient.invalidateQueries({
        queryKey: invoicePdfKeys.latest(variables.invoiceId),
      });
    },
  });
}

/**
 * Get the latest PDF for an invoice
 */
export function useLatestInvoicePdf(invoiceId: number) {
  return useQuery({
    queryKey: invoicePdfKeys.latest(invoiceId),
    queryFn: async (): Promise<InvoicePdf | null> => {
      try {
        return await invoicesApi.getLatestInvoicePdf(invoiceId);
      } catch (error: any) {
        if (error.response?.status === 404) {
          return null;
        }
        throw error;
      }
    },
    enabled: !!invoiceId,
    staleTime: 0,
    refetchOnMount: 'always',
    refetchOnWindowFocus: true,
  });
}

/**
 * Download the latest PDF for an invoice
 */
export function useDownloadInvoicePdf() {
  return useMutation({
    mutationFn: async (invoiceId: number) => {
      const blob = await invoicesApi.downloadInvoicePdf(invoiceId);

      // Create download link
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;

      // Get filename from response headers or use default
      link.download = `invoice-${invoiceId}.pdf`;

      // Trigger download
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);

      // Clean up
      window.URL.revokeObjectURL(url);

      return blob;
    },
  });
}
