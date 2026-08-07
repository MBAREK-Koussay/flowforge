<?php

namespace App\Domain\Invoice\Services;

use App\Domain\Invoice\Enums\InvoiceStatus;
use App\Domain\Invoice\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

final class InvoiceService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Invoice::query()
            ->with('customer')
            ->when(
                filled($filters['customer_id'] ?? null),
                fn (Builder $query) => $query->where('customer_id', $filters['customer_id'])
            )
            ->when(
                filled($filters['status'] ?? null),
                fn (Builder $query) => $query->where('status', $filters['status'])
            )
            ->when(
                filled($filters['search'] ?? null),
                fn (Builder $query) => $query->whereHas('customer', fn (Builder $query) => $query->where('company_name', 'like', "%{$filters['search']}%"))
            )
            ->orderBy('due_date')
            ->paginate($perPage);
    }

    public function find(int|Invoice $invoice): Invoice
    {
        $invoice = $invoice instanceof Invoice
            ? $invoice
            : Invoice::with('customer')->find($invoice);

        if ($invoice === null) {
            throw (new ModelNotFoundException)->setModel(Invoice::class);
        }

        return $invoice;
    }

    public function create(array $data): Invoice
    {
        return Invoice::create([...$data, 'status' => InvoiceStatus::PENDING->value]);
    }

    public function update(Invoice $invoice, array $data): Invoice
    {
        if ($invoice->status === InvoiceStatus::PAID) {
            throw new InvalidArgumentException('Paid invoices cannot be edited.');
        }

        $invoice->update($data);

        return $invoice;
    }

    public function markPaid(Invoice $invoice): Invoice
    {
        $invoice->update([
            'status' => InvoiceStatus::PAID->value,
            'paid_at' => now(),
        ]);

        return $invoice;
    }

    public function delete(Invoice $invoice): void
    {
        if ($invoice->status === InvoiceStatus::PAID) {
            throw new InvalidArgumentException('Paid invoices cannot be deleted.');
        }

        $invoice->delete();
    }

    /**
     * Marks pending invoices whose due date has passed as overdue.
     */
    public function markExpiredOverdue(int|array $limit = 500): int
    {
        $count = 0;

        Invoice::query()
            ->where('status', InvoiceStatus::PENDING->value)
            ->where('due_date', '<', now())
            ->limit(is_array($limit) ? 500 : $limit)
            ->chunkById(200, function ($invoices) use (&$count): void {
                foreach ($invoices as $invoice) {
                    $invoice->update(['status' => InvoiceStatus::OVERDUE->value]);
                    $count++;
                }
            });

        return $count;
    }
}