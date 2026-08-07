import { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Check, Plus, XCircle } from 'lucide-react';
import api, { apiError } from '@/lib/api';
import type { ApiEnvelope, PurchaseRequest } from '@/types/models';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Modal } from '@/components/ui/modal';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Pagination } from '@/components/Pagination';

type PurchaseForm = {
  amount: string;
  description: string;
};

const EMPTY_FORM: PurchaseForm = { amount: '', description: '' };

function money(value: number) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
}

function statusBadge(status: PurchaseRequest['status']) {
  switch (status) {
    case 'approved':
      return <Badge variant="success">Approved</Badge>;
    case 'rejected':
      return <Badge variant="danger">Rejected</Badge>;
    default:
      return <Badge variant="warning">Pending</Badge>;
  }
}

export function PurchaseRequestsPage() {
  const queryClient = useQueryClient();
  const [page, setPage] = useState(1);
  const [status, setStatus] = useState('');
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState<PurchaseForm>(EMPTY_FORM);
  const [error, setError] = useState<string | null>(null);

  const { data, isPending, isError, refetch } = useQuery({
    queryKey: ['purchase-requests', page, status],
    queryFn: async () => {
      const { data } = await api.get<ApiEnvelope<PurchaseRequest[]>>('/purchase-requests', {
        params: { per_page: 15, page, ...(status ? { status } : {}) },
      });
      return data;
    },
  });

  const invalidate = () => void queryClient.invalidateQueries({ queryKey: ['purchase-requests'] });

  const createMutation = useMutation({
    mutationFn: async (payload: PurchaseForm) =>
      api.post('/purchase-requests', {
        amount: payload.amount,
        description: payload.description,
      }),
    onSuccess: () => {
      setShowForm(false);
      setForm(EMPTY_FORM);
      invalidate();
    },
    onError: (err) => setError(apiError(err)),
  });

  const decideMutation = useMutation({
    mutationFn: async ({ id, action }: { id: number; action: 'approve' | 'reject' }) =>
      api.post(`/purchase-requests/${id}/${action}`),
    onSuccess: invalidate,
    onError: (err) => setError(apiError(err)),
  });

  const set = <K extends keyof PurchaseForm>(key: K, value: PurchaseForm[K]) =>
    setForm((prev) => ({ ...prev, [key]: value }));

  const meta = data?.meta;

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Purchase Requests</h1>
          <p className="text-muted-foreground">Submit and approve purchase requests.</p>
        </div>
        <Button onClick={() => {
          setError(null);
          setShowForm(true);
        }}>
          <Plus className="h-4 w-4" />
          New request
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Requests</CardTitle>
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
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>
        </CardHeader>
        <CardContent>
          {isPending && <p className="text-sm text-muted-foreground">Loading requests…</p>}
          {isError && (
            <div className="space-y-2">
              <p className="text-sm text-destructive">Could not load requests.</p>
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
                    <TableHead>Description</TableHead>
                    <TableHead className="text-right">Amount</TableHead>
                    <TableHead>Requested by</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead className="text-right">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {data.data.length === 0 && (
                    <TableRow>
                      <TableCell colSpan={5} className="text-center text-muted-foreground">
                        No requests found.
                      </TableCell>
                    </TableRow>
                  )}
                  {data.data.map((request) => (
                    <TableRow key={request.id}>
                      <TableCell className="max-w-xs truncate">{request.description}</TableCell>
                      <TableCell className="text-right">{money(request.amount)}</TableCell>
                      <TableCell>{request.employee?.name ?? '—'}</TableCell>
                      <TableCell>{statusBadge(request.status)}</TableCell>
                      <TableCell className="text-right">
                        {request.status === 'pending' ? (
                          <div className="flex justify-end gap-1">
                            <Button
                              size="sm"
                              variant="ghost"
                              aria-label="Approve request"
                              onClick={() => decideMutation.mutate({ id: request.id, action: 'approve' })}
                            >
                              <Check className="h-4 w-4 text-emerald-600" />
                            </Button>
                            <Button
                              size="sm"
                              variant="ghost"
                              aria-label="Reject request"
                              onClick={() => decideMutation.mutate({ id: request.id, action: 'reject' })}
                            >
                              <XCircle className="h-4 w-4 text-red-600" />
                            </Button>
                          </div>
                        ) : (
                          <p className="pr-2 text-xs text-muted-foreground">
                            {request.status === 'approved'
                              ? `Approved by ${request.approver?.name ?? '—'}`
                              : 'Rejected'}
                          </p>
                        )}
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
        title="New purchase request"
      >
        <form
          className="grid gap-4"
          onSubmit={(e) => {
            e.preventDefault();
            setError(null);
            createMutation.mutate(form);
          }}
        >
          <div className="space-y-1.5">
            <Label htmlFor="description">Description</Label>
            <Input
              id="description"
              required
              placeholder="What do you need to purchase?"
              value={form.description}
              onChange={(e) => set('description', e.target.value)}
            />
          </div>
          <div className="space-y-1.5 sm:max-w-xs">
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
          {error && <p className="text-sm text-destructive">{error}</p>}
          <div className="flex justify-end gap-2">
            <Button type="button" variant="outline" onClick={() => setShowForm(false)}>
              Cancel
            </Button>
            <Button type="submit" disabled={createMutation.isPending}>
              {createMutation.isPending ? 'Submitting…' : 'Submit request'}
            </Button>
          </div>
        </form>
      </Modal>
    </div>
  );
}