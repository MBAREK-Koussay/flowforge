<?php

namespace Database\Factories;

use App\Domain\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => Str::title(fake()->words(2, true)),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-###?-??')),
            'price' => fake()->randomFloat(2, 5, 5000),
            'stock_quantity' => fake()->numberBetween(0, 250),
            'minimum_stock' => fake()->numberBetween(5, 30),
            'is_active' => true,
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn () => [
            'stock_quantity' => fake()->numberBetween(0, 5),
            'minimum_stock' => fake()->numberBetween(20, 50),
        ]);
    }
}