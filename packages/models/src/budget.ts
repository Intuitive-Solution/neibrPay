export enum BudgetCategoryType {
  INCOME = 'income',
  EXPENSE = 'expense',
}

export enum BudgetAuditAction {
  CREATE_CATEGORY = 'create_category',
  UPDATE_CATEGORY = 'update_category',
  DELETE_CATEGORY = 'delete_category',
  UPDATE_FORECAST = 'update_forecast',
  COPY_BUDGET = 'copy_budget',
}

export interface BudgetCategory {
  id: number;
  tenant_id: number;
  name: string;
  type: BudgetCategoryType;
  display_order: number;
  created_by: number;
  created_at: string;
  updated_at: string;
  deleted_at?: string;
  creator?: {
    id: number;
    name: string;
    email: string;
  };
}

export interface BudgetEntry {
  id: number;
  tenant_id: number;
  budget_category_id: number;
  year: number;
  month: number; // 1-12
  forecast_amount: number;
  created_at: string;
  updated_at: string;
}

export interface BudgetAuditLog {
  id: number;
  tenant_id: number;
  user_id: number;
  year: number;
  action: BudgetAuditAction;
  entity_type: 'category' | 'entry';
  entity_id: number | null;
  old_values: Record<string, any> | null;
  new_values: Record<string, any> | null;
  description: string;
  created_at: string;
  updated_at: string;
  user?: {
    id: number;
    name: string;
    email: string;
  };
}

export interface BudgetMonthData {
  forecast: number;
  actual: number;
}

export interface BudgetCategoryData {
  id: number;
  name: string;
  type: BudgetCategoryType;
  display_order: number;
  months: {
    [month: number]: BudgetMonthData; // 1-12
  };
  total: {
    forecast: number;
    actual: number;
  };
}

export interface BudgetData {
  year: number;
  income: BudgetCategoryData[];
  expense: BudgetCategoryData[];
}

export interface CreateBudgetCategoryDto {
  name: string;
  type: BudgetCategoryType;
  display_order?: number;
}

export interface UpdateBudgetCategoryDto {
  name?: string;
  display_order?: number;
}

export interface BudgetEntryUpdateDto {
  budget_category_id: number;
  year: number;
  month: number; // 1-12
  forecast_amount: number;
}

export interface UpdateBudgetEntriesDto {
  entries: BudgetEntryUpdateDto[];
}

export interface BudgetCategoryListResponse {
  data: BudgetCategory[];
}

export interface BudgetCategoryResponse {
  data: BudgetCategory;
  message?: string;
}

export interface BudgetResponse {
  data: BudgetData;
}

export interface BudgetAuditLogListResponse {
  data: BudgetAuditLog[];
}

// Helper function to get category type display name
export function getBudgetCategoryTypeDisplayName(
  type: BudgetCategoryType
): string {
  const displayNames: Record<BudgetCategoryType, string> = {
    [BudgetCategoryType.INCOME]: 'Income',
    [BudgetCategoryType.EXPENSE]: 'Expense',
  };

  return displayNames[type] || type;
}

// Helper function to get audit action display name
export function getBudgetAuditActionDisplayName(
  action: BudgetAuditAction
): string {
  const displayNames: Record<BudgetAuditAction, string> = {
    [BudgetAuditAction.CREATE_CATEGORY]: 'Created Category',
    [BudgetAuditAction.UPDATE_CATEGORY]: 'Updated Category',
    [BudgetAuditAction.DELETE_CATEGORY]: 'Deleted Category',
    [BudgetAuditAction.UPDATE_FORECAST]: 'Updated Forecast',
    [BudgetAuditAction.COPY_BUDGET]: 'Copied Budget',
  };

  return displayNames[action] || action;
}

// Helper function to get month name
export function getMonthName(month: number): string {
  const months = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December',
  ];

  return months[month - 1] || '';
}

// Helper function to get month abbreviation
export function getMonthAbbreviation(month: number): string {
  const months = [
    'Jan',
    'Feb',
    'Mar',
    'Apr',
    'May',
    'Jun',
    'Jul',
    'Aug',
    'Sep',
    'Oct',
    'Nov',
    'Dec',
  ];

  return months[month - 1] || '';
}

// Helper function to check if actual is on or under budget
export function isOnBudget(actual: number, forecast: number): boolean {
  return actual <= forecast;
}
