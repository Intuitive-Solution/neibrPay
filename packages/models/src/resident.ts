// Resident data models and types

export interface Resident {
  id: number;
  name: string;
  email: string;
  phone: string;
  role: 'resident';
  tenant_id: number;
  is_active: boolean;
  deleted_at?: string | null;
  created_at: string;
  updated_at: string;
  tenant?: {
    id: number;
    name: string;
    slug: string;
  };
}

export interface CreateResidentRequest {
  name: string;
  email: string;
  phone: string;
}

export interface UpdateResidentRequest {
  name?: string;
  email?: string;
  phone?: string;
}

export interface ResidentsResponse {
  data: Resident[];
  meta: {
    total: number;
    include_deleted: boolean;
  };
}

export interface ResidentResponse {
  data: Resident;
  message?: string;
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
}

// Form validation types
export interface ResidentFormData {
  name: string;
  email: string;
  phone: string;
}

export interface ResidentFormErrors {
  name?: string;
  email?: string;
  phone?: string;
  general?: string;
}
