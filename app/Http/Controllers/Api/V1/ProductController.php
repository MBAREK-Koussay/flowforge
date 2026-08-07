<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Product\Enums\StockMovementType;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Services\ProductService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductAdjustStockRequest;
use App\Http\Requests\Product\ProductStoreRequest;
use App\Http\Requests\Product\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\StockMovementResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'low', 'active']);
        $filters['low'] = $request->boolean('low');

        $products = $this->productService->paginate($filters, $request->integer('per_page', 15));

        return ApiResponse::success(ProductResource::collection($products));
    }

    public function store(ProductStoreRequest $request): JsonResponse
    {
        $product = $this->productService->create($request->validated());

        return ApiResponse::success(new ProductResource($product), 'Product created.', 201);
    }

    public function show(Product $product): JsonResponse
    {
        return ApiResponse::success(new ProductResource($this->productService->find($product)));
    }

    public function update(Product $product, ProductUpdateRequest $request): JsonResponse
    {
        $product = $this->productService->update($product, $request->validated());

        return ApiResponse::success(new ProductResource($product), 'Product updated.');
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->productService->delete($product);

        return ApiResponse::success(null, 'Product deleted.');
    }

    public function lowStock(): JsonResponse
    {
        $products = $this->productService->lowStock();

        return ApiResponse::success(ProductResource::collection($products));
    }

    public function movements(Product $product, Request $request): JsonResponse
    {
        $movements = $this->productService->movements($product, $request->integer('per_page', 15));

        return ApiResponse::success(StockMovementResource::collection($movements));
    }

    public function adjustStock(Product $product, ProductAdjustStockRequest $request): JsonResponse
    {
        try {
            $product = $this->productService->adjustStock(
                $product,
                StockMovementType::from($request->validated('type')),
                (int) $request->validated('quantity'),
                $request->validated('reason'),
                user: $request->user(),
            );
        } catch (InvalidArgumentException $exception) {
            return ApiResponse::error($exception->getMessage(), 422);
        }

        return ApiResponse::success(new ProductResource($product), 'Stock adjusted.');
    }
}