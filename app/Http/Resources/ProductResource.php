<?php

namespace App\Http\Resources;

use App\Domain\Product\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => (float) $this->price,
            'stock_quantity' => $this->stock_quantity,
            'minimum_stock' => $this->minimum_stock,
            'is_active' => $this->is_active,
            'is_low_stock' => $this->isLowStock(),
            'stock_movements_count' => $this->whenCounted('stockMovements'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}