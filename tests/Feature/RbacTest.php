<?php

namespace Tests\Feature;

use App\Domain\User\Enums\RoleEnum;
use App\Domain\User\Models\Role;
use App\Domain\User\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function actingAsUser(RoleEnum $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', $role->value)->firstOrFail());

        return $user;
    }

    public function test_admin_can_list_users(): void
    {
        $admin = $this->actingAsUser(RoleEnum::ADMIN);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/users')
            ->assertOk()
            ->assertJsonStructure(['data' => [], 'meta' => ['total']]);
    }

    public function test_employee_cannot_list_users(): void
    {
        $employee = $this->actingAsUser(RoleEnum::EMPLOYEE);

        $this->actingAs($employee, 'sanctum')
            ->getJson('/api/v1/users')
            ->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_protected_route(): void
    {
        $this->getJson('/api/v1/users')->assertStatus(401);
    }

    public function test_role_permission_matrix_is_seeded(): void
    {
        $adminRole = Role::where('slug', RoleEnum::ADMIN->value)->first();

        foreach (['customers.view', 'invoices.approve', 'workflows.execute', 'ai.generate'] as $permission) {
            $this->assertTrue(
                $adminRole->permissions()->where('slug', $permission)->exists()
            );
        }

        $employeeRole = Role::where('slug', RoleEnum::EMPLOYEE->value)->first();
        $this->assertTrue($employeeRole->permissions()->where('slug', 'purchase_requests.create')->exists());
        $this->assertFalse($employeeRole->permissions()->where('slug', 'invoices.approve')->exists());
    }
}