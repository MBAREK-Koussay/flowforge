<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\PurchaseRequest\Models\PurchaseRequest;
use App\Domain\PurchaseRequest\Services\PurchaseRequestService;
use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseRequest\PurchaseRequestDecideRequest;
use App\Http\Requests\PurchaseRequest\PurchaseRequestStoreRequest;
use App\Http\Requests\PurchaseRequest\PurchaseRequestUpdateRequest;
use App\Http\Resources\PurchaseRequestResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class PurchaseRequestController extends Controller
{
    public function __construct(private readonly PurchaseRequestService $purchaseRequestService) {}

    public function index(Request $request): JsonResponse
    {
        $requests = $this->purchaseRequestService->paginate(
            $request->only(['status', 'employee_id', 'search']),
            $request->integer('per_page', 15),
        );

        return ApiResponse::success(PurchaseRequestResource::collection($requests));
    }

    public function store(PurchaseRequestStoreRequest $request): JsonResponse
    {
        $purchaseRequest = $this->purchaseRequestService->create($request->user(), $request->validated());

        return ApiResponse::success(new PurchaseRequestResource($purchaseRequest), 'Purchase request created.', 201);
    }

    public function show(PurchaseRequest $purchaseRequest): JsonResponse
    {
        return ApiResponse::success(new PurchaseRequestResource($this->purchaseRequestService->find($purchaseRequest)));
    }

    public function update(PurchaseRequest $purchaseRequest, PurchaseRequestUpdateRequest $request): JsonResponse
    {
        try {
            $purchaseRequest = $this->purchaseRequestService->update($purchaseRequest, $request->validated());
        } catch (InvalidArgumentException $exception) {
            return ApiResponse::error($exception->getMessage(), 422);
        }

        return ApiResponse::success(new PurchaseRequestResource($purchaseRequest), 'Purchase request updated.');
    }

    public function approve(PurchaseRequest $purchaseRequest, PurchaseRequestDecideRequest $request): JsonResponse
    {
        try {
            $purchaseRequest = $this->purchaseRequestService->approve(
                $purchaseRequest,
                $request->user(),
                $request->validated('note'),
            );
        } catch (InvalidArgumentException $exception) {
            return ApiResponse::error($exception->getMessage(), 422);
        }

        return ApiResponse::success(new PurchaseRequestResource($purchaseRequest), 'Purchase request approved.');
    }

    public function reject(PurchaseRequest $purchaseRequest, PurchaseRequestDecideRequest $request): JsonResponse
    {
        try {
            $purchaseRequest = $this->purchaseRequestService->reject(
                $purchaseRequest,
                $request->user(),
                $request->validated('note'),
            );
        } catch (InvalidArgumentException $exception) {
            return ApiResponse::error($exception->getMessage(), 422);
        }

        return ApiResponse::success(new PurchaseRequestResource($purchaseRequest), 'Purchase request rejected.');
    }
}