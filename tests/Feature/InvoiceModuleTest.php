<?php

namespace Tests\Feature;

use App\Domain\Customer\Models\Customer;
use App\Domain\Invoice\Enums\InvoiceStatus;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\User\Models\Role;
use App\Domain\User\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function userWithRole(string $roleSlug): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', $roleSlug)->firstOrFail());

        return $user;
    }

    public function test_finance_can_create_invoice_with_pending_status(): void
    {
        $finance = $this->userWithRole('finance');
        $customer = Customer::factory()->create();

        $this->actingAs($finance, 'sanctum');

        $this->postJson('/api/v1/invoices', [
            'customer_id' => $customer->id,
            'amount' => 1500.75,
            'due_date' => now()->addDays(30)->toDateString(),
        ])->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.amount', 1500.75);
    }

    public function test_invoice_requires_valid_customer(): void
    {
        $finance = $this->userWithRole('finance');
        $this->actingAs($finance, 'sanctum');

        $this->postJson('/api/v1/invoices', [
            'customer_id' => 999999,
            'amount' => 100,
            'due_date' => now()->addDays(30)->toDateString(),
        ])->assertStatus(422);
    }

    public function test_finance_can_mark_pending_invoice_as_paid(): void
    {
        $finance = $this->userWithRole('finance');
        $invoice = Invoice::factory()->create(['status' => InvoiceStatus::PENDING]);

        $this->actingAs($finance, 'sanctum');

        $this->postJson("/api/v1/invoices/{$invoice->id}/mark-paid")
            ->assertOk()
            ->assertJsonPath('data.status', 'paid');

        $this->assertNotNull($invoice->fresh()->paid_at);
    }

    public function test_paid_invoice_cannot_be_edited(): void
    {
        $finance = $this->userWithRole('finance');
        $invoice = Invoice::factory()->create(['status' => InvoiceStatus::PAID]);

        $this->actingAs($finance, 'sanctum');

        $this->putJson("/api/v1/invoices/{$invoice->id}", [
            'amount' => 500,
        ])->assertStatus(422);
    }

    public function test_employee_cannot_create_invoice(): void
    {
        $employee = $this->userWithRole('employee');
        $customer = Customer::factory()->create();
        $this->actingAs($employee, 'sanctum');

        $this->postJson('/api/v1/invoices', [
            'customer_id' => $customer->id,
            'amount' => 100,
            'due_date' => now()->addDays(30)->toDateString(),
        ])->assertStatus(403);
    }

    public function test_overdue_scheduler_marks_expired_invoices(): void
    {
        $this->userWithRole('admin');

        $expired = Invoice::factory()->create([
            'status' => InvoiceStatus::PENDING,
            'due_date' => now()->subDays(10),
        ]);
        $notDue = Invoice::factory()->create([
            'status' => InvoiceStatus::PENDING,
            'due_date' => now()->addDays(10),
        ]);

        $service = app(\App\Domain\Invoice\Services\InvoiceService::class);
        $count = $service->markExpiredOverdue();

        $this->assertSame(1, $count);
        $this->assertSame(InvoiceStatus::OVERDUE, $expired->fresh()->status);
        $this->assertSame(InvoiceStatus::PENDING, $notDue->fresh()->status);
    }
}