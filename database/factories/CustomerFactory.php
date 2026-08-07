<?php

namespace Database\Factories;

use App\Domain\Customer\Enums\CustomerStatus;
use App\Domain\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'company_name' => fake()->unique()->company(),
            'contact_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->phoneNumber(),
            'status' => fake()->randomElement(array_column(CustomerStatus::cases(), 'value')),
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => CustomerStatus::ACTIVE->value]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => CustomerStatus::INACTIVE->value]);
    }
}