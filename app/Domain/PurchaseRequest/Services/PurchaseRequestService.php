<?php

namespace App\Domain\PurchaseRequest\Services;

use App\Domain\PurchaseRequest\Enums\PurchaseRequestStatus;
use App\Domain\PurchaseRequest\Events\PurchaseRequestCreated;
use App\Domain\PurchaseRequest\Models\PurchaseRequest;
use App\Domain\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

final class PurchaseRequestService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return PurchaseRequest::query()
            ->with(['employee', 'approver'])
            ->when(
                filled($filters['employee_id'] ?? null),
                fn ($query) => $query->where('employee_id', $filters['employee_id'])
            )
            ->when(
                filled($filters['status'] ?? null),
                fn ($query) => $query->where('status', $filters['status'])
            )
            ->when(
                filled($filters['search'] ?? null),
                fn ($query) => $query->whereHas('employee', fn ($query) => $query->where('name', 'like', "%{$filters['search']}%"))
            )
            ->latest('id')
            ->paginate($perPage);
    }

    public function find(int|PurchaseRequest $purchaseRequest): PurchaseRequest
    {
        $purchaseRequest = $purchaseRequest instanceof PurchaseRequest
            ? $purchaseRequest
            : PurchaseRequest::with(['employee', 'approver'])->find($purchaseRequest);

        if ($purchaseRequest === null) {
            throw (new ModelNotFoundException)->setModel(PurchaseRequest::class);
        }

        return $purchaseRequest;
    }

    public function create(User $employee, array $data): PurchaseRequest
    {
        $purchaseRequest = PurchaseRequest::create([
            ...$data,
            'employee_id' => $employee->id,
            'status' => PurchaseRequestStatus::PENDING->value,
        ]);

        PurchaseRequestCreated::dispatch($purchaseRequest);

        return $purchaseRequest;
    }

    public function update(PurchaseRequest $purchaseRequest, array $data): PurchaseRequest
    {
        if ($purchaseRequest->status !== PurchaseRequestStatus::PENDING) {
            throw new InvalidArgumentException('Only pending requests can be edited.');
        }

        $purchaseRequest->update($data);

        return $purchaseRequest;
    }

    public function approve(PurchaseRequest $purchaseRequest, User $approver, ?string $note = null): PurchaseRequest
    {
        if ($purchaseRequest->status !== PurchaseRequestStatus::PENDING) {
            throw new InvalidArgumentException('Only pending requests can be approved.');
        }

        $purchaseRequest->update([
            'status' => PurchaseRequestStatus::APPROVED->value,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        return $purchaseRequest;
    }

    public function reject(PurchaseRequest $purchaseRequest, User $approver, ?string $note = null): PurchaseRequest
    {
        if ($purchaseRequest->status !== PurchaseRequestStatus::PENDING) {
            throw new InvalidArgumentException('Only pending requests can be rejected.');
        }

        $purchaseRequest->update([
            'status' => PurchaseRequestStatus::REJECTED->value,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        return $purchaseRequest;
    }
}