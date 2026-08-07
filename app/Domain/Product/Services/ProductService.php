<?php

namespace App\Domain\Product\Services;

use App\Domain\Product\Enums\StockMovementType;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\StockMovement;
use App\Domain\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Model;

final class ProductService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->withCount('stockMovements')
            ->when(
                filled($filters['search'] ?? null),
                fn ($query) => $query->where(function ($query) use ($filters): void {
                    $query->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('sku', 'like', "%{$filters['search']}%");
                })
            )
            ->when(
                ($filters['low'] ?? false) === true,
                fn ($query) => $query->whereColumn('stock_quantity', '<=', 'minimum_stock')
            )
            ->when(
                isset($filters['active']) && $filters['active'] !== null,
                fn ($query) => $query->where('is_active', $filters['active'])
            )
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function find(int|Product $product): Product
    {
        $product = $product instanceof Product ? $product : Product::withCount('stockMovements')->find($product);

        if ($product === null) {
            throw new ModelNotFoundException();
        }

        return $product;
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product;
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    public function adjustStock(
        Product $product,
        StockMovementType $type,
        int $quantity,
        ?string $reason = null,
        ?Model $reference = null,
        ?User $user = null,
    ): Product {
        $delta = match ($type) {
            StockMovementType::IN => $quantity,
            StockMovementType::OUT => -1 * $quantity,
            StockMovementType::ADJUSTMENT => $quantity - $product->stock_quantity,
        };

        $newStock = $product->stock_quantity + $delta;

        if ($newStock < 0) {
            throw new \InvalidArgumentException('Stock cannot become negative with this operation.');
        }

        $product->stock_quantity = $newStock;
        $product->save();

        StockMovement::create([
            'product_id' => $product->id,
            'type' => $type->value,
            'quantity' => $delta,
            'reason' => $reason,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'user_id' => $user?->id,
        ]);

        return $product;
    }

    public function movements(int|Product $product, int $perPage = 15): LengthAwarePaginator
    {
        $product = $this->find($product);

        return StockMovement::query()
            ->where('product_id', $product->id)
            ->with(['user'])
            ->latest()
            ->paginate($perPage);
    }

    public function lowStock(): Collection
    {
        return Product::query()
            ->where('is_active', true)
            ->whereColumn('stock_quantity', '<=', 'minimum_stock')
            ->orderByRaw('stock_quantity - minimum_stock ASC')
            ->get();
    }
}