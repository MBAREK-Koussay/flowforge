<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\Services\CustomerService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CustomerStoreRequest;
use App\Http\Requests\Customer\CustomerUpdateRequest;
use App\Http\Resources\CustomerResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $customerService) {}

    public function index(Request $request): JsonResponse
    {
        $customers = $this->customerService->paginate(
            $request->only(['search', 'status']),
            $request->integer('per_page', 15),
        );

        return ApiResponse::success(CustomerResource::collection($customers));
    }

    public function store(CustomerStoreRequest $request): JsonResponse
    {
        $customer = $this->customerService->create($request->validated());

        return ApiResponse::success(new CustomerResource($customer), 'Customer created.', 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        return ApiResponse::success(new CustomerResource($customer->loadCount('invoices')));
    }

    public function update(Customer $customer, CustomerUpdateRequest $request): JsonResponse
    {
        $customer = $this->customerService->update($customer, $request->validated());

        return ApiResponse::success(new CustomerResource($customer), 'Customer updated.');
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $this->customerService->delete($customer);

        return ApiResponse::success(null, 'Customer deleted.');
    }
}