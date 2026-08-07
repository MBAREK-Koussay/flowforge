<?php

namespace Tests\Feature;

use App\Domain\Customer\Enums\CustomerStatus;
use App\Domain\Customer\Models\Customer;
use App\Domain\User\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function adminUser(): User
    {
        return User::factory()->create(['password' => bcrypt('password')]);
    }

    private function actingAsAdmin(): void
    {
        $user = $this->adminUser();
        $user->roles()->attach(\App\Domain\User\Models\Role::where('slug', 'admin')->firstOrFail());
        $this->actingAs($user, 'sanctum');
    }

    public function test_admin_can_create_customer(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/v1/customers', [
            'company_name' => 'TechCorp Tunisia',
            'contact_name' => 'Amira Ben Ali',
            'email' => 'contact@techcorp.tn',
            'phone' => '+216 71 000 000',
            'status' => CustomerStatus::ACTIVE->value,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.company_name', 'TechCorp Tunisia')
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('customers', ['email' => 'contact@techcorp.tn']);
    }

    public function test_customer_validation_requires_company_name(): void
    {
        $this->actingAsAdmin();

        $this->postJson('/api/v1/customers', ['company_name' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['company_name']);
    }

    public function test_customers_list_can_be_searched(): void
    {
        Customer::factory()->create(['company_name' => 'Tunisie Telecom']);
        Customer::factory()->create(['company_name' => 'Blue Shell Co']);

        $this->actingAsAdmin();

        $response = $this->getJson('/api/v1/customers?search=telecom&per_page=15');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.company_name', 'Tunisie Telecom');
    }

    public function test_employee_cannot_create_customer(): void
    {
        $employee = User::factory()->create();
        $employee->roles()->attach(\App\Domain\User\Models\Role::where('slug', 'employee')->firstOrFail());
        $this->actingAs($employee, 'sanctum');

        $this->postJson('/api/v1/customers', [
            'company_name' => 'X',
            'contact_name' => 'Y',
        ])->assertStatus(403);
    }
}