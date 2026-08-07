<?php

namespace Database\Factories;

use App\Domain\PurchaseRequest\Enums\PurchaseRequestStatus;
use App\Domain\PurchaseRequest\Models\PurchaseRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseRequest>
 */
class PurchaseRequestFactory extends Factory
{
    protected $model = PurchaseRequest::class;

    public function definition(): array
    {
        return [
            'employee_id' => \App\Domain\User\Models\User::factory(),
            'amount' => fake()->randomFloat(2, 200, 20_000),
            'description' => fake()->sentence(10),
            'status' => PurchaseRequestStatus::PENDING->value,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => PurchaseRequestStatus::APPROVED->value,
            'approved_by' => \App\Domain\User\Models\User::factory(),
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => PurchaseRequestStatus::REJECTED->value,
            'approved_by' => \App\Domain\User\Models\User::factory(),
            'approved_at' => now(),
        ]);
    }
}