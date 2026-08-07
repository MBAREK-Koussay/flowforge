<?php

namespace Tests\Feature;

use App\Domain\Product\Enums\StockMovementType;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\StockMovement;
use App\Domain\User\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function admin(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(\App\Domain\User\Models\Role::where('slug', 'admin')->firstOrFail());
        $this->actingAs($user, 'sanctum');
    }

    public function test_product_can_be_created(): void
    {
        $this->admin();

        $this->postJson('/api/v1/products', [
            'name' => 'Laptop Stand',
            'sku' => 'SKU-LS-01',
            'price' => 89.99,
            'stock_quantity' => 25,
            'minimum_stock' => 10,
        ])->assertCreated()
            ->assertJsonPath('data.sku', 'SKU-LS-01')
            ->assertJsonPath('data.is_low_stock', false);
    }

    public function test_stock_in_movement_updates_quantity_and_records_log(): void
    {
        $this->admin();
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $this->postJson("/api/v1/products/{$product->id}/stock", [
            'type' => StockMovementType::IN->value,
            'quantity' => 5,
            'reason' => 'Restock',
        ])->assertOk()
            ->assertJsonPath('data.stock_quantity', 15);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => StockMovementType::IN->value,
            'quantity' => 5,
        ]);
    }

    public function test_stock_cannot_go_negative(): void
    {
        $this->admin();
        $product = Product::factory()->create(['stock_quantity' => 3]);

        $this->postJson("/api/v1/products/{$product->id}/stock", [
            'type' => StockMovementType::OUT->value,
            'quantity' => 10,
        ])->assertStatus(422);
    }

    public function test_low_stock_endpoint_returns_only_low_products(): void
    {
        $this->admin();
        Product::factory()->lowStock()->create();
        Product::factory()->create(['stock_quantity' => 500, 'minimum_stock' => 5]);

        $response = $this->getJson('/api/v1/products/low-stock');

        $response->assertOk();
        $this->assertTrue(collect($response->json('data'))->every(fn ($p) => $p['is_low_stock'] === true));
    }

    public function test_movements_are_listed(): void
    {
        $this->admin();
        $product = Product::factory()->create();
        StockMovement::factory()->count(3)->create(['product_id' => $product->id]);

        $this->getJson("/api/v1/products/{$product->id}/stock-movements")
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }
}