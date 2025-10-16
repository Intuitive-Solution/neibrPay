import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, unref, type Ref } from 'vue';
import { expensesApi, queryKeys } from '@neibrpay/api-client';
import type { CreateExpenseDto, UpdateExpenseDto } from '@neibrpay/models';

// Get all expenses with optional filters
export function useExpenses(
  filters: {
    include_deleted?: boolean | Ref<boolean>;
    vendor_id?: number | Ref<number>;
    category?: string | Ref<string>;
    status?: string | Ref<string>;
    search?: string | Ref<string>;
  } = {}
) {
  return useQuery({
    queryKey: computed(() => {
      const unwrappedFilters = {
        include_deleted: unref(filters.include_deleted),
        vendor_id: unref(filters.vendor_id),
        category: unref(filters.category),
        status: unref(filters.status),
        search: unref(filters.search),
      };
      return queryKeys.expenses.list(unwrappedFilters);
    }),
    queryFn: () => {
      const unwrappedFilters = {
        include_deleted: unref(filters.include_deleted),
        vendor_id: unref(filters.vendor_id),
        category: unref(filters.category),
        status: unref(filters.status),
        search: unref(filters.search),
      };
      return expensesApi.list(unwrappedFilters);
    },
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
}

// Get a specific expense
export function useExpense(id: number) {
  return useQuery({
    queryKey: queryKeys.expenses.detail(id),
    queryFn: () => expensesApi.get(id),
    enabled: !!id,
  });
}

// Create expense mutation
export function useCreateExpense() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: CreateExpenseDto) => expensesApi.create(data),
    onSuccess: () => {
      // Invalidate and refetch expense lists
      queryClient.invalidateQueries({ queryKey: queryKeys.expenses.lists() });
    },
  });
}

// Update expense mutation
export function useUpdateExpense() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, data }: { id: number; data: UpdateExpenseDto }) =>
      expensesApi.update(id, data),
    onSuccess: (response, { id }) => {
      // Update the specific expense in cache
      queryClient.setQueryData(queryKeys.expenses.detail(id), response);
      // Invalidate lists to ensure consistency
      queryClient.invalidateQueries({ queryKey: queryKeys.expenses.lists() });
    },
  });
}

// Delete expense mutation
export function useDeleteExpense() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => expensesApi.delete(id),
    onSuccess: (_, id) => {
      // Remove from cache
      queryClient.removeQueries({ queryKey: queryKeys.expenses.detail(id) });
      // Invalidate lists
      queryClient.invalidateQueries({ queryKey: queryKeys.expenses.lists() });
    },
  });
}

// Restore expense mutation
export function useRestoreExpense() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => expensesApi.restore(id),
    onSuccess: (response, id) => {
      // Update the specific expense in cache
      queryClient.setQueryData(queryKeys.expenses.detail(id), response);
      // Invalidate lists
      queryClient.invalidateQueries({ queryKey: queryKeys.expenses.lists() });
    },
  });
}

// Get expense attachments
export function useExpenseAttachments(expenseId: number) {
  return useQuery({
    queryKey: queryKeys.expenses.attachments(expenseId),
    queryFn: () => expensesApi.getAttachments(expenseId),
    enabled: !!expenseId,
  });
}

// Upload attachment mutation
export function useUploadAttachment() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({
      expenseId,
      file,
      description,
    }: {
      expenseId: number;
      file: File;
      description?: string;
    }) => expensesApi.uploadAttachment(expenseId, file, description),
    onSuccess: (_, { expenseId }) => {
      // Invalidate attachments for this expense
      queryClient.invalidateQueries({
        queryKey: queryKeys.expenses.attachments(expenseId),
      });
      // Also invalidate the expense detail to refresh the full data
      queryClient.invalidateQueries({
        queryKey: queryKeys.expenses.detail(expenseId),
      });
    },
  });
}

// Delete attachment mutation
export function useDeleteAttachment() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({
      expenseId,
      attachmentId,
    }: {
      expenseId: number;
      attachmentId: number;
    }) => expensesApi.deleteAttachment(expenseId, attachmentId),
    onSuccess: (_, { expenseId }) => {
      // Invalidate attachments for this expense
      queryClient.invalidateQueries({
        queryKey: queryKeys.expenses.attachments(expenseId),
      });
      // Also invalidate the expense detail to refresh the full data
      queryClient.invalidateQueries({
        queryKey: queryKeys.expenses.detail(expenseId),
      });
    },
  });
}
