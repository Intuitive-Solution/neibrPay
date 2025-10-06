import { ref, computed } from 'vue';
import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query';
import { unitsApi, unitKeys } from '@neibrpay/api-client';
// import type { UnitDocument } from '@neibrpay/models';

export function useUnitDocuments(unitId: number) {
  const queryClient = useQueryClient();

  // Query for documents
  const {
    data: documents,
    isLoading,
    error,
    refetch,
  } = useQuery({
    queryKey: unitKeys.documents(unitId),
    queryFn: () => unitsApi.getDocuments(unitId),
    enabled: computed(() => !!unitId),
  });

  // Upload document mutation
  const uploadDocumentMutation = useMutation({
    mutationFn: ({ file, description }: { file: File; description?: string }) =>
      unitsApi.uploadDocument(unitId, file, description),
    onSuccess: () => {
      // Invalidate and refetch documents
      queryClient.invalidateQueries({
        queryKey: unitKeys.documents(unitId),
      });
      // Also invalidate unit details to refresh document count
      queryClient.invalidateQueries({
        queryKey: unitKeys.detail(unitId),
      });
    },
  });

  // Delete document mutation
  const deleteDocumentMutation = useMutation({
    mutationFn: (documentId: number) =>
      unitsApi.deleteDocument(unitId, documentId),
    onSuccess: () => {
      // Invalidate and refetch documents
      queryClient.invalidateQueries({
        queryKey: unitKeys.documents(unitId),
      });
      // Also invalidate unit details to refresh document count
      queryClient.invalidateQueries({
        queryKey: unitKeys.detail(unitId),
      });
    },
  });

  // Download document function
  const downloadDocument = async (documentId: number, fileName: string) => {
    try {
      const blob = await unitsApi.downloadDocument(unitId, documentId);

      // Create download link
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = fileName;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url);
    } catch (error) {
      console.error('Download failed:', error);
      throw error;
    }
  };

  return {
    documents: computed(() => documents.value || []),
    isLoading,
    error,
    refetch,
    uploadDocument: uploadDocumentMutation.mutateAsync,
    isUploading: computed(() => uploadDocumentMutation.isPending.value),
    uploadError: computed(() => uploadDocumentMutation.error.value),
    deleteDocument: deleteDocumentMutation.mutateAsync,
    isDeleting: computed(() => deleteDocumentMutation.isPending.value),
    deleteError: computed(() => deleteDocumentMutation.error.value),
    downloadDocument,
  };
}

export function useUnitDocumentUpload(unitId: number) {
  const queryClient = useQueryClient();
  const uploadProgress = ref(0);

  const uploadMutation = useMutation({
    mutationFn: ({ file, description }: { file: File; description?: string }) =>
      unitsApi.uploadDocument(unitId, file, description),
    onSuccess: () => {
      // Invalidate queries
      queryClient.invalidateQueries({
        queryKey: unitKeys.documents(unitId),
      });
      queryClient.invalidateQueries({
        queryKey: unitKeys.detail(unitId),
      });
      uploadProgress.value = 0;
    },
    onError: () => {
      uploadProgress.value = 0;
    },
  });

  const uploadDocument = async (file: File, description?: string) => {
    uploadProgress.value = 0;
    return uploadMutation.mutateAsync({ file, description });
  };

  return {
    uploadDocument,
    isUploading: computed(() => uploadMutation.isPending.value),
    uploadError: computed(() => uploadMutation.error.value),
    uploadProgress,
  };
}
