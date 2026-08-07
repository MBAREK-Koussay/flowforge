<?php

namespace App\Domain\Customer\Services;

use App\Domain\Customer\Enums\CustomerStatus;
use App\Domain\Customer\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class CustomerService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Customer::query()
            ->when(
                filled($filters['search'] ?? null),
                fn ($query) => $query->where(function ($query) use ($filters): void {
                    foreach (['company_name', 'contact_name', 'email'] as $column) {
                        $query->orWhere($column, 'like', "%{$filters['search']}%");
                    }
                })
            )
            ->when(
                filled($filters['status'] ?? null),
                fn ($query) => $query->where('status', $filters['status'])
            )
            ->orderBy('company_name')
            ->paginate($perPage);
    }

    public function find(int|Customer $customer): Customer
    {
        $customer = $customer instanceof Customer ? $customer : Customer::find($customer);

        if ($customer === null) {
            throw (new ModelNotFoundException)->setModel(Customer::class);
        }

        return $customer;
    }

    public function create(array $data): Customer
    {
        $data['status'] = CustomerStatus::from($data['status'] ?? CustomerStatus::ACTIVE->value);

        return Customer::create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        if (isset($data['status'])) {
            $data['status'] = CustomerStatus::from($data['status']);
        }

        $customer->update($data);

        return $customer;
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }
}