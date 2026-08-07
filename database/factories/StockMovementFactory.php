<?php

namespace Database\Factories;

use App\Domain\Product\Enums\StockMovementType;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\StockMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'type' => fake()->randomElement(array_column(StockMovementType::cases(), 'value')),
            'quantity' => fake()->numberBetween(1, 100),
            'reason' => fake()->optional(0.6)->sentence(),
        ];
    }

    public function ofType(StockMovementType $type): static
    {
        return $this->state(fn () => ['type' => $type->value]);
    }
}