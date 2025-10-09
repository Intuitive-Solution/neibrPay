import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, unref, type Ref } from 'vue';
import { invoicesApi } from '@neibrpay/api-client';

// Query keys
export const invoiceAttachmentQueryKeys = {
  all: ['invoice-attachments'] as const,
  lists: () => [...invoiceAttachmentQueryKeys.all, 'list'] as const,
  list: (invoiceId: number) =>
    [...invoiceAttachmentQueryKeys.lists(), invoiceId] as const,
};

// Get attachments for a specific invoice
export function useInvoiceAttachments(invoiceId: number | Ref<number>) {
  return useQuery({
    queryKey: computed(() => invoiceAttachmentQueryKeys.list(unref(invoiceId))),
    queryFn: () => {
      const id = unref(invoiceId);
      return invoicesApi.getInvoiceAttachments(id);
    },
    enabled: computed(() => !!unref(invoiceId)),
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
}

// Upload attachment mutation
export function useUploadInvoiceAttachment() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({
      invoiceId,
      file,
      description,
    }: {
      invoiceId: number;
      file: File;
      description?: string;
    }) => invoicesApi.uploadInvoiceAttachment(invoiceId, file, description),
    onSuccess: (_, { invoiceId }) => {
      // Invalidate and refetch attachment lists for this invoice
      queryClient.invalidateQueries({
        queryKey: invoiceAttachmentQueryKeys.list(invoiceId),
      });
    },
  });
}

// Delete attachment mutation
export function useDeleteInvoiceAttachment() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({
      invoiceId,
      attachmentId,
    }: {
      invoiceId: number;
      attachmentId: number;
    }) => invoicesApi.deleteInvoiceAttachment(invoiceId, attachmentId),
    onSuccess: (_, { invoiceId }) => {
      // Invalidate and refetch attachment lists for this invoice
      queryClient.invalidateQueries({
        queryKey: invoiceAttachmentQueryKeys.list(invoiceId),
      });
    },
  });
}

// Download attachment mutation
export function useDownloadInvoiceAttachment() {
  return useMutation({
    mutationFn: ({
      invoiceId,
      attachmentId,
      fileName,
    }: {
      invoiceId: number;
      attachmentId: number;
      fileName: string;
    }) => {
      return invoicesApi
        .downloadInvoiceAttachment(invoiceId, attachmentId)
        .then(blob => {
          // Create download link
          const url = window.URL.createObjectURL(blob);
          const link = document.createElement('a');
          link.href = url;
          link.download = fileName;
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
          window.URL.revokeObjectURL(url);
          return { success: true };
        });
    },
  });
}
