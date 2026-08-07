<?php

namespace Database\Seeders;

use App\Domain\Customer\Models\Customer;
use App\Domain\Invoice\Enums\InvoiceStatus;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\Product\Enums\StockMovementType;
use App\Domain\Product\Models\Product;
use App\Domain\PurchaseRequest\Enums\PurchaseRequestStatus;
use App\Domain\PurchaseRequest\Models\PurchaseRequest;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCustomers();
        $this->seedProducts();
        $this->seedInvoices();
        $this->seedPurchaseRequests();
    }

    private function seedCustomers(): void
    {
        if (Customer::query()->exists()) {
            return;
        }

        Customer::factory()->count(50)->create();
    }

    private function seedProducts(): void
    {
        if (Product::query()->exists()) {
            return;
        }

        $referenceUserId = User::query()->value('id');

        $products = Product::factory()->count(100)->create();

        // Historical stock movements keep the inventory-history screens populated.
        foreach ($products as $product) {
            for ($i = 0, $count = random_int(0, 5); $i < $count; $i++) {
                $type = collect(StockMovementType::cases())->random();

                $product->stockMovements()->create([
                    'type' => $type->value,
                    'quantity' => match ($type) {
                        StockMovementType::IN => random_int(10, 200),
                        StockMovementType::OUT => random_int(1, 80),
                        StockMovementType::ADJUSTMENT => random_int(-20, 20),
                    },
                    'reason' => fake()->randomElement([
                        'Initial stock',
                        'Restock',
                        'Manual correction',
                        'Damaged goods',
                        'Return refill',
                        null,
                    ]),
                    'user_id' => $referenceUserId,
                    'created_at' => now()->subDays(random_int(1, 90)),
                ]);
            }
        }
    }

    private function seedInvoices(): void
    {
        if (Invoice::query()->exists()) {
            return;
        }

        $customers = Customer::pluck('id')->all();

        $invoices = Invoice::factory()
            ->count(100)
            ->create([
                'customer_id' => fn () => $customers[array_rand($customers)],
            ]);

        foreach ($invoices as $invoice) {
            $roll = rand(1, 100);

            if ($roll <= 30) {
                $invoice->update([
                    'status' => InvoiceStatus::PAID->value,
                    'paid_at' => now()->subDays(rand(1, 15)),
                ]);
            } elseif ($roll <= 55) {
                $invoice->update(['status' => InvoiceStatus::OVERDUE->value]);
            }
        }
    }

    private function seedPurchaseRequests(): void
    {
        if (PurchaseRequest::query()->exists()) {
            return;
        }

        $employees = User::query()
            ->whereHas('roles', fn ($query) => $query->where('slug', 'employee'))
            ->pluck('id')
            ->all();

        $managerId = User::query()
            ->whereHas('roles', fn ($query) => $query->where('slug', 'manager'))
            ->pluck('id')
            ->first();

        PurchaseRequest::factory()
            ->count(50)
            ->create(['employee_id' => fn () => $employees[array_rand($employees)]])
            ->each(function (PurchaseRequest $request) use ($managerId): void {
                $roll = rand(1, 100);

                if ($roll <= 20) {
                    $request->update([
                        'status' => PurchaseRequestStatus::APPROVED->value,
                        'approved_by' => $managerId,
                        'approved_at' => now()->subDays(rand(0, 5)),
                    ]);
                } elseif ($roll <= 30) {
                    $request->update([
                        'status' => PurchaseRequestStatus::REJECTED->value,
                        'approved_by' => $managerId,
                        'approved_at' => now()->subDays(rand(0, 5)),
                    ]);
                }
            });
    }
}