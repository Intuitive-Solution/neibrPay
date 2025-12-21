import { apiClient } from './apiClient';
import type {
  Expense,
  ExpenseAttachment,
  CreateExpenseDto,
  UpdateExpenseDto,
  ExpenseFilters,
  ExpenseListResponse,
  ExpenseResponse,
  ExpenseAttachmentResponse,
} from '@neibrpay/models';

export const expensesApi = {
  /**
   * Get all expenses with optional filters
   */
  async list(filters?: ExpenseFilters): Promise<Expense[]> {
    const params = new URLSearchParams();

    if (filters?.vendor_id) {
      params.append('vendor_id', filters.vendor_id.toString());
    }
    if (filters?.budget_category_id) {
      params.append(
        'budget_category_id',
        filters.budget_category_id.toString()
      );
    }
    if (filters?.status) {
      params.append('status', filters.status);
    }
    if (filters?.search) {
      params.append('search', filters.search);
    }
    if (filters?.include_deleted) {
      params.append('include_deleted', 'true');
    }

    const response = await apiClient.get<ExpenseListResponse>(
      `/expenses?${params.toString()}`
    );
    return response.data.data;
  },

  /**
   * Get a single expense by ID
   */
  async get(id: number): Promise<Expense> {
    const response = await apiClient.get<ExpenseResponse>(`/expenses/${id}`);
    return response.data.data;
  },

  /**
   * Create a new expense
   */
  async create(data: CreateExpenseDto): Promise<Expense> {
    const response = await apiClient.post<ExpenseResponse>('/expenses', data);
    return response.data.data;
  },

  /**
   * Update an existing expense
   */
  async update(id: number, data: UpdateExpenseDto): Promise<Expense> {
    const response = await apiClient.put<ExpenseResponse>(
      `/expenses/${id}`,
      data
    );
    return response.data.data;
  },

  /**
   * Delete an expense (soft delete)
   */
  async delete(id: number): Promise<void> {
    await apiClient.delete(`/expenses/${id}`);
  },

  /**
   * Restore a deleted expense
   */
  async restore(id: number): Promise<Expense> {
    const response = await apiClient.post<ExpenseResponse>(
      `/expenses/${id}/restore`
    );
    return response.data.data;
  },

  /**
   * Get attachments for an expense
   */
  async getAttachments(expenseId: number): Promise<ExpenseAttachment[]> {
    const response = await apiClient.get<{ data: ExpenseAttachment[] }>(
      `/expenses/${expenseId}/attachments`
    );
    return response.data.data;
  },

  /**
   * Upload an attachment to an expense
   */
  async uploadAttachment(
    expenseId: number,
    file: File,
    description?: string
  ): Promise<ExpenseAttachment> {
    const formData = new FormData();
    formData.append('file', file);
    if (description) {
      formData.append('description', description);
    }

    const response = await apiClient.post<ExpenseAttachmentResponse>(
      `/expenses/${expenseId}/attachments`,
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      }
    );
    return response.data.data;
  },

  /**
   * Download an expense attachment
   */
  async downloadAttachment(
    expenseId: number,
    attachmentId: number
  ): Promise<Blob> {
    const response = await apiClient.get(
      `/expenses/${expenseId}/attachments/${attachmentId}/download`,
      {
        responseType: 'blob',
      }
    );
    return response.data;
  },

  /**
   * Delete an expense attachment
   */
  async deleteAttachment(
    expenseId: number,
    attachmentId: number
  ): Promise<void> {
    await apiClient.delete(
      `/expenses/${expenseId}/attachments/${attachmentId}`
    );
  },
};
