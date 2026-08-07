<?php

namespace Database\Factories;

use App\Domain\Customer\Models\Customer;
use App\Domain\Invoice\Enums\InvoiceStatus;
use App\Domain\Invoice\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'amount' => fake()->randomFloat(2, 100, 15_000),
            'due_date' => fake()->dateTimeBetween('-30 days', '+45 days'),
            'status' => InvoiceStatus::PENDING->value,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => InvoiceStatus::PAID->value,
            'paid_at' => now()->subDays(fake()->numberBetween(0, 20)),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'status' => InvoiceStatus::OVERDUE->value,
            'due_date' => fake()->dateTimeBetween('-45 days', '-1 day'),
        ]);
    }
}