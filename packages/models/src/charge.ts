export interface BudgetCategoryRef {
  id: number;
  name: string;
  type: 'income' | 'expense';
}

export interface Charge {
  id: number;
  tenant_id: number;
  title: string;
  description?: string;
  amount: number;
  budget_category_id: number;
  budget_category?: BudgetCategoryRef;
  is_active: boolean;
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

export interface CreateChargeDto {
  title: string;
  description?: string;
  amount: number;
  budget_category_id: number;
  is_active?: boolean;
}

export interface UpdateChargeDto {
  title?: string;
  description?: string;
  amount?: number;
  budget_category_id?: number;
  is_active?: boolean;
}

export interface ChargeFilters {
  budget_category_id?: number | string;
  is_active?: boolean | string;
  include_deleted?: boolean;
  search?: string;
}

export interface ChargeListResponse {
  data: Charge[];
  meta: {
    total: number;
    budget_category_id?: number;
    is_active?: boolean;
    include_deleted?: boolean;
    search?: string;
  };
}

export interface ChargeResponse {
  data: Charge;
  message?: string;
}
