import { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Pencil, Plus, Search, Trash2 } from 'lucide-react';
import api, { apiError } from '@/lib/api';
import type { ApiEnvelope, Customer } from '@/types/models';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Modal } from '@/components/ui/modal';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Pagination } from '@/components/Pagination';

type CustomerForm = {
  company_name: string;
  contact_name: string;
  email: string;
  phone: string;
  status: Customer['status'];
};

const EMPTY_FORM: CustomerForm = {
  company_name: '',
  contact_name: '',
  email: '',
  phone: '',
  status: 'active',
};

function statusBadge(status: Customer['status']) {
  return status === 'active' ? (
    <Badge variant="success">Active</Badge>
  ) : (
    <Badge variant="secondary">Inactive</Badge>
  );
}

export function CustomersPage() {
  const queryClient = useQueryClient();
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [editing, setEditing] = useState<Customer | null>(null);
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState<CustomerForm>(EMPTY_FORM);
  const [error, setError] = useState<string | null>(null);

  const { data, isPending, isError, refetch } = useQuery({
    queryKey: ['customers', page, search],
    queryFn: async () => {
      const { data } = await api.get<ApiEnvelope<Customer[]>>('/customers', {
        params: { per_page: 15, page, search },
      });
      return data;
    },
  });

  const invalidate = () => void queryClient.invalidateQueries({ queryKey: ['customers'] });

  const saveMutation = useMutation({
    mutationFn: async (payload: CustomerForm) => {
      if (editing) {
        return api.put(`/customers/${editing.id}`, payload);
      }
      return api.post('/customers', payload);
    },
    onSuccess: () => {
      setShowForm(false);
      setEditing(null);
      setForm(EMPTY_FORM);
      invalidate();
    },
    onError: (err) => setError(apiError(err)),
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => api.delete(`/customers/${id}`),
    onSuccess: invalidate,
    onError: (err) => setError(apiError(err)),
  });

  const startCreate = () => {
    setEditing(null);
    setForm(EMPTY_FORM);
    setError(null);
    setShowForm(true);
  };

  const startEdit = (customer: Customer) => {
    setEditing(customer);
    setForm({
      company_name: customer.company_name,
      contact_name: customer.contact_name,
      email: customer.email,
      phone: customer.phone ?? '',
      status: customer.status,
    });
    setError(null);
    setShowForm(true);
  };

  const set = <K extends keyof CustomerForm>(key: K, value: CustomerForm[K]) =>
    setForm((prev) => ({ ...prev, [key]: value }));

  const meta = data?.meta;

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Customers</h1>
          <p className="text-muted-foreground">Manage your customer base.</p>
        </div>
        <Button onClick={startCreate}>
          <Plus className="h-4 w-4" />
          New customer
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Customer list</CardTitle>
          <div className="relative mb-4 max-w-xs">
            <Search className="absolute left-2 top-2 h-4 w-4 text-muted-foreground" />
            <Input
              placeholder="Search by name, contact or email…"
              className="pl-8"
              value={search}
              onChange={(e) => {
                setSearch(e.target.value);
                setPage(1);
              }}
            />
          </div>
        </CardHeader>
        <CardContent>
          {isPending && <p className="text-sm text-muted-foreground">Loading customers…</p>}
          {isError && (
            <div className="space-y-2">
              <p className="text-sm text-destructive">Could not load customers.</p>
              <Button size="sm" variant="outline" onClick={() => void refetch()}>
                Retry
              </Button>
            </div>
          )}

          {data && (
            <>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Company</TableHead>
                    <TableHead>Contact</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead className="text-right">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {data.data.length === 0 && (
                    <TableRow>
                      <TableCell colSpan={5} className="text-center text-muted-foreground">
                        No customers found.
                      </TableCell>
                    </TableRow>
                  )}
                  {data.data.map((customer) => (
                    <TableRow key={customer.id}>
                      <TableCell className="font-medium">{customer.company_name}</TableCell>
                      <TableCell>{customer.contact_name}</TableCell>
                      <TableCell>{customer.email}</TableCell>
                      <TableCell>{statusBadge(customer.status)}</TableCell>
                      <TableCell className="text-right">
                        <div className="flex justify-end gap-1">
                          <Button
                            size="sm"
                            variant="ghost"
                            onClick={() => startEdit(customer)}
                            aria-label="Edit customer"
                          >
                            <Pencil className="h-4 w-4" />
                          </Button>
                          <Button
                            size="sm"
                            variant="ghost"
                            className="text-destructive"
                            aria-label="Delete customer"
                            onClick={() => {
                              if (window.confirm(`Delete "${customer.company_name}"?`)) {
                                void deleteMutation.mutate(customer.id);
                              }
                            }}
                          >
                            <Trash2 className="h-4 w-4" />
                          </Button>
                        </div>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
              <Pagination page={page} meta={meta} onPage={setPage} />
            </>
          )}
        </CardContent>
      </Card>

      <Modal
        open={showForm}
        onClose={() => setShowForm(false)}
        title={editing ? 'Edit customer' : 'New customer'}
      >
        <form
          className="grid gap-4 sm:grid-cols-2"
          onSubmit={(e) => {
            e.preventDefault();
            setError(null);
            saveMutation.mutate(form);
          }}
        >
          <div className="space-y-1.5">
            <Label htmlFor="company_name">Company name</Label>
            <Input
              id="company_name"
              required
              value={form.company_name}
              onChange={(e) => set('company_name', e.target.value)}
            />
          </div>
          <div className="space-y-1.5">
            <Label htmlFor="contact_name">Contact name</Label>
            <Input
              id="contact_name"
              required
              value={form.contact_name}
              onChange={(e) => set('contact_name', e.target.value)}
            />
          </div>
          <div className="space-y-1.5">
            <Label htmlFor="email">Email</Label>
            <Input
              id="email"
              type="email"
              value={form.email}
              onChange={(e) => set('email', e.target.value)}
            />
          </div>
          <div className="space-y-1.5">
            <Label htmlFor="phone">Phone</Label>
            <Input
              id="phone"
              value={form.phone}
              onChange={(e) => set('phone', e.target.value)}
            />
          </div>
          <div className="space-y-1.5">
            <Label htmlFor="status">Status</Label>
            <select
              id="status"
              className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
              value={form.status}
              onChange={(e) => set('status', e.target.value as Customer['status'])}
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          {error && <p className="text-sm text-destructive sm:col-span-2">{error}</p>}
          <div className="flex justify-end gap-2 sm:col-span-2">
            <Button type="button" variant="outline" onClick={() => setShowForm(false)}>
              Cancel
            </Button>
            <Button type="submit" disabled={saveMutation.isPending}>
              {saveMutation.isPending ? 'Saving…' : editing ? 'Save changes' : 'Create customer'}
            </Button>
          </div>
        </form>
      </Modal>
    </div>
  );
}