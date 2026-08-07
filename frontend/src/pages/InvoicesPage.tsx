import { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { BadgeCheck, Plus, Trash2 } from 'lucide-react';
import api, { apiError } from '@/lib/api';
import type { ApiEnvelope, Customer, Invoice } from '@/types/models';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Modal } from '@/components/ui/modal';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Pagination } from '@/components/Pagination';

type InvoiceForm = {
  customer_id: string;
  amount: string;
  due_date: string;
};

const EMPTY_FORM: InvoiceForm = {
  customer_id: '',
  amount: '',
  due_date: '',
};

function money(value: number) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
}

function statusBadge(status: Invoice['status']) {
  switch (status) {
    case 'paid':
      return <Badge variant="success">Paid</Badge>;
    case 'overdue':
      return <Badge variant="danger">Overdue</Badge>;
    default:
      return <Badge variant="warning">Pending</Badge>;
  }
}

export function InvoicesPage() {
  const queryClient = useQueryClient();
  const [page, setPage] = useState(1);
  const [status, setStatus] = useState('');
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState<InvoiceForm>(EMPTY_FORM);
  const [error, setError] = useState<string | null>(null);

  const { data, isPending, isError, refetch } = useQuery({
    queryKey: ['invoices', page, status],
    queryFn: async () => {
      const { data } = await api.get<ApiEnvelope<Invoice[]>>('/invoices', {
        params: { per_page: 15, page, ...(status ? { status } : {}) },
      });
      return data;
    },
  });

  const customersQuery = useQuery({
    queryKey: ['customers-options'],
    queryFn: async () => {
      const { data } = await api.get<ApiEnvelope<Customer[]>>('/customers', { params: { per_page: 200 } });
      return data;
    },
  });

  const invalidate = () => void queryClient.invalidateQueries({ queryKey: ['invoices'] });

  const createMutation = useMutation({
    mutationFn: async (payload: InvoiceForm) =>
      api.post('/invoices', {
        customer_id: payload.customer_id,
        amount: payload.amount,
        due_date: payload.due_date,
      }),
    onSuccess: () => {
      setShowForm(false);
      setForm(EMPTY_FORM);
      invalidate();
    },
    onError: (err) => setError(apiError(err)),
  });

  const markPaidMutation = useMutation({
    mutationFn: (id: number) => api.post(`/invoices/${id}/mark-paid`),
    onSuccess: invalidate,
    onError: (err) => setError(apiError(err)),
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => api.delete(`/invoices/${id}`),
    onSuccess: invalidate,
    onError: (err) => setError(apiError(err)),
  });

  const set = <K extends keyof InvoiceForm>(key: K, value: InvoiceForm[K]) =>
    setForm((prev) => ({ ...prev, [key]: value }));

  const meta = data?.meta;

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Invoices</h1>
          <p className="text-muted-foreground">Track invoices, due dates and payments.</p>
        </div>
        <Button onClick={() => {
          setError(null);
          setShowForm(true);
        }}>
          <Plus className="h-4 w-4" />
          New invoice
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Invoice list</CardTitle>
          <div className="mb-4 max-w-xs">
            <select
              className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
              value={status}
              onChange={(e) => {
                setStatus(e.target.value);
                setPage(1);
              }}
            >
              <option value="">All statuses</option>
              <option value="pending">Pending</option>
              <option value="paid">Paid</option>
              <option value="overdue">Overdue</option>
            </select>
          </div>
        </CardHeader>
        <CardContent>
          {isPending && <p className="text-sm text-muted-foreground">Loading invoices…</p>}
          {isError && (
            <div className="space-y-2">
              <p className="text-sm text-destructive">Could not load invoices.</p>
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
                    <TableHead>Customer</TableHead>
                    <TableHead className="text-right">Amount</TableHead>
                    <TableHead>Due date</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead className="text-right">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {data.data.length === 0 && (
                    <TableRow>
                      <TableCell colSpan={5} className="text-center text-muted-foreground">
                        No invoices found.
                      </TableCell>
                    </TableRow>
                  )}
                  {data.data.map((invoice) => (
                    <TableRow key={invoice.id}>
                      <TableCell className="font-medium">{invoice.customer?.company_name ?? '—'}</TableCell>
                      <TableCell className="text-right">{money(invoice.amount)}</TableCell>
                      <TableCell>{invoice.due_date}</TableCell>
                      <TableCell>{statusBadge(invoice.status)}</TableCell>
                      <TableCell className="text-right">
                        <div className="flex justify-end gap-1">
                          {invoice.status !== 'paid' && (
                            <Button
                              size="sm"
                              variant="ghost"
                              aria-label="Mark as paid"
                              onClick={() => markPaidMutation.mutate(invoice.id)}
                            >
                              <BadgeCheck className="h-4 w-4 text-emerald-600" />
                            </Button>
                          )}
                          <Button
                            size="sm"
                            variant="ghost"
                            className="text-destructive"
                            aria-label="Delete invoice"
                            disabled={invoice.status === 'paid'}
                            onClick={() => {
                              if (window.confirm('Delete this invoice?')) {
                                void deleteMutation.mutate(invoice.id);
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
        title="New invoice"
      >
        <form
          className="grid gap-4 sm:grid-cols-3"
          onSubmit={(e) => {
            e.preventDefault();
            setError(null);
            createMutation.mutate(form);
          }}
        >
          <div className="space-y-1.5">
            <Label htmlFor="customer_id">Customer</Label>
            <select
              id="customer_id"
              required
              className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
              value={form.customer_id}
              onChange={(e) => set('customer_id', e.target.value)}
            >
              <option value="" disabled>
                Select a customer…
              </option>
              {(customersQuery.data?.data ?? []).map((customer) => (
                <option key={customer.id} value={customer.id}>
                  {customer.company_name}
                </option>
              ))}
            </select>
          </div>
          <div className="space-y-1.5">
            <Label htmlFor="amount">Amount (USD)</Label>
            <Input
              id="amount"
              type="number"
              min="0"
              step="0.01"
              required
              value={form.amount}
              onChange={(e) => set('amount', e.target.value)}
            />
          </div>
          <div className="space-y-1.5">
            <Label htmlFor="due_date">Due date</Label>
            <Input
              id="due_date"
              type="date"
              required
              value={form.due_date}
              onChange={(e) => set('due_date', e.target.value)}
            />
          </div>
          {error && <p className="text-sm text-destructive sm:col-span-3">{error}</p>}
          <div className="flex justify-end gap-2 sm:col-span-3">
            <Button type="button" variant="outline" onClick={() => setShowForm(false)}>
              Cancel
            </Button>
            <Button type="submit" disabled={createMutation.isPending}>
              {createMutation.isPending ? 'Creating…' : 'Create invoice'}
            </Button>
          </div>
        </form>
      </Modal>
    </div>
  );
}