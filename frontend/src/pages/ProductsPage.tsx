import { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Pencil, Plus, PackagePlus, Search, Trash2 } from 'lucide-react';
import api, { apiError } from '@/lib/api';
import type { ApiEnvelope, Product } from '@/types/models';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Modal } from '@/components/ui/modal';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Pagination } from '@/components/Pagination';

type ProductForm = {
  name: string;
  sku: string;
  price: string;
  stock_quantity: string;
  minimum_stock: string;
  is_active: boolean;
};

const EMPTY_FORM: ProductForm = {
  name: '',
  sku: '',
  price: '',
  stock_quantity: '',
  minimum_stock: '',
  is_active: true,
};

type StockForm = {
  type: 'in' | 'out';
  quantity: string;
  reason: string;
};

function money(value: number) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
}

export function ProductsPage() {
  const queryClient = useQueryClient();
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [editing, setEditing] = useState<Product | null>(null);
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState<ProductForm>(EMPTY_FORM);
  const [stockFor, setStockFor] = useState<Product | null>(null);
  const [stockForm, setStockForm] = useState<StockForm>({ type: 'in', quantity: '', reason: '' });
  const [error, setError] = useState<string | null>(null);

  const { data, isPending, isError, refetch } = useQuery({
    queryKey: ['products', page, search],
    queryFn: async () => {
      const { data } = await api.get<ApiEnvelope<Product[]>>('/products', {
        params: { per_page: 15, page, search },
      });
      return data;
    },
  });

  const invalidate = () => void queryClient.invalidateQueries({ queryKey: ['products'] });

  const saveMutation = useMutation({
    mutationFn: async (payload: ProductForm) => {
      const body = {
        name: payload.name,
        sku: payload.sku,
        price: payload.price,
        stock_quantity: payload.stock_quantity,
        minimum_stock: payload.minimum_stock,
        is_active: payload.is_active,
      };
      if (editing) {
        return api.put(`/products/${editing.id}`, body);
      }
      return api.post('/products', body);
    },
    onSuccess: () => {
      setShowForm(false);
      setEditing(null);
      setForm(EMPTY_FORM);
      invalidate();
    },
    onError: (err) => setError(apiError(err)),
  });

  const adjustStockMutation = useMutation({
    mutationFn: async (payload: StockForm & { id: number }) =>
      api.post(`/products/${payload.id}/stock`, {
        type: payload.type,
        quantity: payload.quantity,
        reason: payload.reason || undefined,
      }),
    onSuccess: () => {
      setStockFor(null);
      setStockForm({ type: 'in', quantity: '', reason: '' });
      invalidate();
    },
    onError: (err) => setError(apiError(err)),
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => api.delete(`/products/${id}`),
    onSuccess: invalidate,
    onError: (err) => setError(apiError(err)),
  });

  const startCreate = () => {
    setEditing(null);
    setForm(EMPTY_FORM);
    setError(null);
    setShowForm(true);
  };

  const startEdit = (product: Product) => {
    setEditing(product);
    setForm({
      name: product.name,
      sku: product.sku,
      price: String(product.price),
      stock_quantity: String(product.stock_quantity),
      minimum_stock: String(product.minimum_stock),
      is_active: product.is_active,
    });
    setError(null);
    setShowForm(true);
  };

  const set = <K extends keyof ProductForm>(key: K, value: ProductForm[K]) =>
    setForm((prev) => ({ ...prev, [key]: value }));

  const meta = data?.meta;

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Products &amp; Stock</h1>
          <p className="text-muted-foreground">Track products, prices and inventory.</p>
        </div>
        <Button onClick={startCreate}>
          <Plus className="h-4 w-4" />
          New product
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Product list</CardTitle>
          <div className="relative mb-4 max-w-xs">
            <Search className="absolute left-2 top-2 h-4 w-4 text-muted-foreground" />
            <Input
              placeholder="Search by name or SKU…"
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
          {isPending && <p className="text-sm text-muted-foreground">Loading products…</p>}
          {isError && (
            <div className="space-y-2">
              <p className="text-sm text-destructive">Could not load products.</p>
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
                    <TableHead>Name</TableHead>
                    <TableHead>SKU</TableHead>
                    <TableHead className="text-right">Price</TableHead>
                    <TableHead className="text-right">Stock</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead className="text-right">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {data.data.length === 0 && (
                    <TableRow>
                      <TableCell colSpan={6} className="text-center text-muted-foreground">
                        No products found.
                      </TableCell>
                    </TableRow>
                  )}
                  {data.data.map((product) => (
                    <TableRow key={product.id}>
                      <TableCell className="font-medium">{product.name}</TableCell>
                      <TableCell className="font-mono text-xs">{product.sku}</TableCell>
                      <TableCell className="text-right">{money(product.price)}</TableCell>
                      <TableCell className="text-right">
                        {product.stock_quantity}
                        <span className="ml-2 text-xs text-muted-foreground">min {product.minimum_stock}</span>
                      </TableCell>
                      <TableCell>
                        {product.is_low_stock ? (
                          <Badge variant="danger">Low stock</Badge>
                        ) : product.is_active ? (
                          <Badge variant="success">Active</Badge>
                        ) : (
                          <Badge variant="secondary">Inactive</Badge>
                        )}
                      </TableCell>
                      <TableCell className="text-right">
                        <div className="flex justify-end gap-1">
                          <Button
                            size="sm"
                            variant="ghost"
                            aria-label="Adjust stock"
                            onClick={() => {
                              setError(null);
                              setStockFor(product);
                              setStockForm({ type: 'in', quantity: '', reason: '' });
                            }}
                          >
                            <PackagePlus className="h-4 w-4" />
                          </Button>
                          <Button size="sm" variant="ghost" aria-label="Edit product" onClick={() => startEdit(product)}>
                            <Pencil className="h-4 w-4" />
                          </Button>
                          <Button
                            size="sm"
                            variant="ghost"
                            className="text-destructive"
                            aria-label="Delete product"
                            onClick={() => {
                              if (window.confirm(`Delete "${product.name}"?`)) {
                                void deleteMutation.mutate(product.id);
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
        title={editing ? 'Edit product' : 'New product'}
      >
        <form
          className="grid gap-4 sm:grid-cols-2"
          onSubmit={(e) => {
            e.preventDefault();
            setError(null);
            saveMutation.mutate(form);
          }}
        >
          <div className="sm:col-span-2">
            <Label htmlFor="name">Name</Label>
            <Input id="name" required value={form.name} onChange={(e) => set('name', e.target.value)} />
          </div>
          <div className="space-y-1.5">
            <Label htmlFor="sku">SKU</Label>
            <Input id="sku" required value={form.sku} onChange={(e) => set('sku', e.target.value)} />
          </div>
          <div className="space-y-1.5">
            <Label htmlFor="price">Price (USD)</Label>
            <Input
              id="price"
              type="number"
              min="0"
              step="0.01"
              required
              value={form.price}
              onChange={(e) => set('price', e.target.value)}
            />
          </div>
          <div className="space-y-1.5">
            <Label htmlFor="stock_quantity">Stock quantity</Label>
            <Input
              id="stock_quantity"
              type="number"
              min="0"
              required
              value={form.stock_quantity}
              onChange={(e) => set('stock_quantity', e.target.value)}
            />
          </div>
          <div className="space-y-1.5">
            <Label htmlFor="minimum_stock">Minimum stock</Label>
            <Input
              id="minimum_stock"
              type="number"
              min="0"
              required
              value={form.minimum_stock}
              onChange={(e) => set('minimum_stock', e.target.value)}
            />
          </div>
          {error && <p className="text-sm text-destructive sm:col-span-2">{error}</p>}
          <div className="flex justify-end gap-2 sm:col-span-2">
            <Button type="button" variant="outline" onClick={() => setShowForm(false)}>
              Cancel
            </Button>
            <Button type="submit" disabled={saveMutation.isPending}>
              {saveMutation.isPending ? 'Saving…' : editing ? 'Save changes' : 'Create product'}
            </Button>
          </div>
        </form>
      </Modal>

      <Modal
        open={stockFor !== null}
        onClose={() => setStockFor(null)}
        title={stockFor ? `Adjust stock — ${stockFor.name}` : 'Adjust stock'}
      >
        {stockFor && (
          <>
            <p className="mb-4 text-sm text-muted-foreground">
              Current stock: <strong>{stockFor.stock_quantity}</strong>
            </p>
            <form
              className="grid gap-4 sm:grid-cols-3"
              onSubmit={(e) => {
                e.preventDefault();
                setError(null);
                adjustStockMutation.mutate({ ...stockForm, id: stockFor.id });
              }}
            >
              <div className="space-y-1.5">
                <Label htmlFor="type">Type</Label>
                <select
                  id="type"
                  className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                  value={stockForm.type}
                  onChange={(e) => setStockForm((prev) => ({ ...prev, type: e.target.value as 'in' | 'out' }))}
                >
                  <option value="in">Stock in</option>
                  <option value="out">Stock out</option>
                </select>
              </div>
              <div className="space-y-1.5">
                <Label htmlFor="quantity">Quantity</Label>
                <Input
                  id="quantity"
                  type="number"
                  min="1"
                  required
                  value={stockForm.quantity}
                  onChange={(e) => setStockForm((prev) => ({ ...prev, quantity: e.target.value }))}
                />
              </div>
              <div className="space-y-1.5">
                <Label htmlFor="reason">Reason</Label>
                <Input
                  id="reason"
                  placeholder="e.g. Restock, damaged"
                  value={stockForm.reason}
                  onChange={(e) => setStockForm((prev) => ({ ...prev, reason: e.target.value }))}
                />
              </div>
              {error && <p className="text-sm text-destructive sm:col-span-3">{error}</p>}
              <div className="flex justify-end gap-2 sm:col-span-3">
                <Button type="button" variant="outline" onClick={() => setStockFor(null)}>
                  Cancel
                </Button>
                <Button type="submit" disabled={adjustStockMutation.isPending}>
                  {adjustStockMutation.isPending ? 'Updating…' : 'Apply adjustment'}
                </Button>
              </div>
            </form>
          </>
        )}
      </Modal>
    </div>
  );
}