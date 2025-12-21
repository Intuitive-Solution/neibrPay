export enum ExpenseStatus {
  UNPAID = 'unpaid',
  PARTIAL = 'partial',
  PAID = 'paid',
}

export enum PaymentMethod {
  CASH = 'cash',
  CHECK = 'check',
  CREDIT_CARD = 'credit_card',
  BANK_TRANSFER = 'bank_transfer',
  OTHER = 'other',
}

export interface Expense {
  id: number;
  tenant_id: number;
  vendor_id: number;
  invoice_number: string;
  invoice_date: string;
  invoice_due_date: string;
  invoice_amount: number;
  budget_category_id?: number | null;
  note?: string;
  status: ExpenseStatus;
  payment_details?: string;
  payment_method?: PaymentMethod;
  paid_amount: number;
  paid_date?: string;
  created_by: number;
  created_at: string;
  updated_at: string;
  deleted_at?: string;
  // Relationships
  vendor?: {
    id: number;
    name: string;
    category: string;
    contact_name: string;
    contact_email: string;
    contact_phone: string;
  };
  budget_category?: {
    id: number;
    name: string;
    type: string;
    display_order: number;
  };
  creator?: {
    id: number;
    name: string;
    email: string;
  };
  attachments?: ExpenseAttachment[];
}

export interface ExpenseAttachment {
  id: number;
  expense_id: number;
  file_name: string;
  file_path: string;
  file_hash: string;
  file_size: number;
  mime_type: string;
  attachment_type: 'pdf' | 'image' | 'document' | 'other';
  uploaded_by: number;
  created_at: string;
  updated_at: string;
  // Relationships
  uploader?: {
    id: number;
    name: string;
    email: string;
  };
}

export interface CreateExpenseDto {
  vendor_id: number;
  invoice_number: string;
  invoice_date: string;
  invoice_due_date: string;
  invoice_amount: number;
  budget_category_id?: number | null;
  note?: string;
  status: ExpenseStatus;
  payment_details?: string;
  payment_method?: PaymentMethod;
  paid_amount?: number;
  paid_date?: string;
}

export interface UpdateExpenseDto {
  vendor_id?: number;
  invoice_number?: string;
  invoice_date?: string;
  invoice_due_date?: string;
  invoice_amount?: number;
  budget_category_id?: number | null;
  note?: string;
  status?: ExpenseStatus;
  payment_details?: string;
  payment_method?: PaymentMethod;
  paid_amount?: number;
  paid_date?: string;
}

export interface ExpenseFilters {
  vendor_id?: number;
  budget_category_id?: number;
  status?: ExpenseStatus | string;
  search?: string;
  include_deleted?: boolean;
}

export interface ExpenseListResponse {
  data: Expense[];
  meta: {
    total: number;
    include_deleted?: boolean;
    filters?: {
      vendor_id?: number;
      budget_category_id?: number;
      status?: string;
      search?: string;
    };
  };
}

export interface ExpenseResponse {
  data: Expense;
  message?: string;
}

export interface ExpenseAttachmentResponse {
  data: ExpenseAttachment;
  message?: string;
}

// Helper function to get status display name
export function getExpenseStatusDisplayName(status: ExpenseStatus): string {
  const displayNames: Record<ExpenseStatus, string> = {
    [ExpenseStatus.UNPAID]: 'Unpaid',
    [ExpenseStatus.PARTIAL]: 'Partial',
    [ExpenseStatus.PAID]: 'Paid',
  };

  return displayNames[status] || status;
}

// Helper function to get payment method display name
export function getPaymentMethodDisplayName(method: PaymentMethod): string {
  const displayNames: Record<PaymentMethod, string> = {
    [PaymentMethod.CASH]: 'Cash',
    [PaymentMethod.CHECK]: 'Check',
    [PaymentMethod.CREDIT_CARD]: 'Credit Card',
    [PaymentMethod.BANK_TRANSFER]: 'Bank Transfer',
    [PaymentMethod.OTHER]: 'Other',
  };

  return displayNames[method] || method;
}

// Helper function to get all status options for dropdowns
export function getExpenseStatusOptions(): Array<{
  value: ExpenseStatus;
  label: string;
}> {
  return Object.values(ExpenseStatus).map(status => ({
    value: status,
    label: getExpenseStatusDisplayName(status),
  }));
}

// Helper function to get all payment method options for dropdowns
export function getPaymentMethodOptions(): Array<{
  value: PaymentMethod;
  label: string;
}> {
  return Object.values(PaymentMethod).map(method => ({
    value: method,
    label: getPaymentMethodDisplayName(method),
  }));
}

// Helper function to get status badge class for UI
export function getExpenseStatusBadgeClass(status: ExpenseStatus): string {
  const statusClasses: Record<ExpenseStatus, string> = {
    [ExpenseStatus.UNPAID]: 'bg-red-100 text-red-800',
    [ExpenseStatus.PARTIAL]: 'bg-yellow-100 text-yellow-800',
    [ExpenseStatus.PAID]: 'bg-green-100 text-green-800',
  };

  return statusClasses[status] || 'bg-gray-100 text-gray-800';
}

// Helper function to calculate remaining balance
export function calculateExpenseBalance(expense: Expense): number {
  return expense.invoice_amount - expense.paid_amount;
}

// Helper function to check if expense is overdue
export function isExpenseOverdue(expense: Expense): boolean {
  return (
    expense.status !== ExpenseStatus.PAID &&
    new Date(expense.invoice_due_date) < new Date()
  );
}
