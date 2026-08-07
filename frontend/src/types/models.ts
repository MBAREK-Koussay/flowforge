export interface User {
  id: number;
  name: string;
  email: string;
  department: string | null;
  job_title: string | null;
  roles: string[];
  created_at: string | null;
  updated_at: string | null;
}

export interface LoginResponse {
  user: User;
  token: string;
}

export interface ApiEnvelope<T> {
  success: boolean;
  message: string;
  data: T;
  meta?: PaginationMeta;
}

export interface PaginationMeta {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
}

export type Customer = {
  id: number;
  company_name: string;
  contact_name: string;
  email: string;
  phone: string | null;
  status: 'active' | 'inactive';
  status_label: string;
  invoices_count?: number;
  created_at: string | null;
};

export type Product = {
  id: number;
  name: string;
  sku: string;
  price: number;
  stock_quantity: number;
  minimum_stock: number;
  is_active: boolean;
  is_low_stock: boolean;
  stock_movements_count?: number;
  created_at: string | null;
};

export type StockMovement = {
  id: number;
  type: 'in' | 'out' | 'adjustment';
  type_label: string;
  quantity: number;
  reason: string | null;
  user?: { id?: number; name?: string } | null;
  created_at: string | null;
};

export type PurchaseRequest = {
  id: number;
  amount: number;
  description: string;
  status: 'pending' | 'approved' | 'rejected';
  status_label: string;
  employee?: { id: number; name: string } | null;
  approver?: { id: number; name: string } | null;
  approved_at: string | null;
  created_at: string | null;
};

export type Invoice = {
  id: number;
  amount: number;
  due_date: string;
  status: 'pending' | 'paid' | 'overdue';
  status_label: string;
  paid_at: string | null;
  customer?: { id: number; company_name: string } | null;
  created_at: string | null;
};