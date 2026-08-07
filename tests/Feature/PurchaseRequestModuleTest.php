<?php

namespace Tests\Feature;

use App\Domain\PurchaseRequest\Enums\PurchaseRequestStatus;
use App\Domain\PurchaseRequest\Models\PurchaseRequest;
use App\Domain\User\Models\Role;
use App\Domain\User\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseRequestModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function createUserWithRole(string $roleSlug): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', $roleSlug)->firstOrFail());

        return $user;
    }

    public function test_employee_can_create_purchase_request(): void
    {
        $employee = $this->createUserWithRole('employee');
        $this->actingAs($employee, 'sanctum');

        $this->postJson('/api/v1/purchase-requests', [
            'amount' => 250.50,
            'description' => 'New office chairs',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('purchase_requests', [
            'employee_id' => $employee->id,
            'amount' => 250.50,
        ]);
    }

    public function test_manager_can_approve_pending_request(): void
    {
        $manager = $this->createUserWithRole('manager');
        $request = PurchaseRequest::factory()->create(['status' => PurchaseRequestStatus::PENDING]);

        $this->actingAs($manager, 'sanctum');

        $this->postJson("/api/v1/purchase-requests/{$request->id}/approve", [
            'note' => 'Approved.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $request->id,
            'status' => 'approved',
            'approved_by' => $manager->id,
        ]);
    }

    public function test_employee_cannot_approve_request(): void
    {
        $employee = $this->createUserWithRole('employee');
        $request = PurchaseRequest::factory()->create();

        $this->actingAs($employee, 'sanctum');

        $this->postJson("/api/v1/purchase-requests/{$request->id}/approve")->assertStatus(403);
    }

    public function test_already_decided_request_cannot_be_approved_again(): void
    {
        $manager = $this->createUserWithRole('manager');
        $request = PurchaseRequest::factory()->approved()->create();

        $this->actingAs($manager, 'sanctum');

        $this->postJson("/api/v1/purchase-requests/{$request->id}/approve")
            ->assertStatus(422);
    }

    public function test_employee_can_only_see_own_initial_list_but_filtering_works(): void
    {
        $employee = $this->createUserWithRole('employee');
        PurchaseRequest::factory()->create([
            'employee_id' => $employee->id,
            'description' => 'Laser printer toner',
        ]);
        PurchaseRequest::factory()->create([
            'employee_id' => $this->createUserWithRole('employee')->id,
            'description' => 'Random item',
        ]);

        $this->actingAs($employee, 'sanctum');

        $this->getJson("/api/v1/purchase-requests?employee_id={$employee->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}