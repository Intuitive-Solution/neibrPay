import { apiClient } from './apiClient';
import type {
  BudgetCategory,
  BudgetCategoryListResponse,
  BudgetCategoryResponse,
  BudgetData,
  BudgetResponse,
  BudgetAuditLog,
  BudgetAuditLogListResponse,
  CreateBudgetCategoryDto,
  UpdateBudgetCategoryDto,
  UpdateBudgetEntriesDto,
} from '@neibrpay/models';

export const budgetApi = {
  /**
   * Get all budget categories
   */
  async getCategories(type?: 'income' | 'expense'): Promise<BudgetCategory[]> {
    const params = new URLSearchParams();
    if (type) {
      params.append('type', type);
    }

    const response = await apiClient.get<BudgetCategoryListResponse>(
      `/budget/categories${params.toString() ? `?${params.toString()}` : ''}`
    );
    return response.data.data;
  },

  /**
   * Create a new budget category
   */
  async createCategory(data: CreateBudgetCategoryDto): Promise<BudgetCategory> {
    const response = await apiClient.post<BudgetCategoryResponse>(
      '/budget/categories',
      data
    );
    return response.data.data;
  },

  /**
   * Update a budget category
   */
  async updateCategory(
    id: number,
    data: UpdateBudgetCategoryDto
  ): Promise<BudgetCategory> {
    const response = await apiClient.put<BudgetCategoryResponse>(
      `/budget/categories/${id}`,
      data
    );
    return response.data.data;
  },

  /**
   * Delete a budget category
   */
  async deleteCategory(id: number): Promise<void> {
    await apiClient.delete(`/budget/categories/${id}`);
  },

  /**
   * Get budget data for a specific year
   */
  async getBudget(year: number): Promise<BudgetData> {
    const response = await apiClient.get<BudgetResponse>(`/budget/${year}`);
    return response.data.data;
  },

  /**
   * Update budget entries
   */
  async updateEntries(data: UpdateBudgetEntriesDto): Promise<void> {
    await apiClient.put('/budget/entries', data);
  },

  /**
   * Copy budget from one year to another
   */
  async copyBudget(
    fromYear: number,
    toYear: number,
    type?: 'all' | 'income' | 'expense'
  ): Promise<void> {
    const params = new URLSearchParams();
    if (type && type !== 'all') {
      params.append('type', type);
    }
    const queryString = params.toString() ? `?${params.toString()}` : '';
    await apiClient.post(`/budget/copy/${fromYear}/${toYear}${queryString}`);
  },

  /**
   * Get audit logs for a specific year
   */
  async getAuditLogs(year: number): Promise<BudgetAuditLog[]> {
    const response = await apiClient.get<BudgetAuditLogListResponse>(
      `/budget/${year}/audit-logs`
    );
    return response.data.data;
  },
};
