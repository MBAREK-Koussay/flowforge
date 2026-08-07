<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Invoice\Models\Invoice;
use App\Domain\Invoice\Services\InvoiceService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\InvoiceStoreRequest;
use App\Http\Requests\Invoice\InvoiceUpdateRequest;
use App\Http\Resources\InvoiceResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $invoiceService) {}

    public function index(Request $request): JsonResponse
    {
        $invoices = $this->invoiceService->paginate(
            $request->only(['status', 'customer_id', 'search']),
            $request->integer('per_page', 15),
        );

        return ApiResponse::success(InvoiceResource::collection($invoices));
    }

    public function store(InvoiceStoreRequest $request): JsonResponse
    {
        $invoice = $this->invoiceService->create($request->validated());

        return ApiResponse::success(new InvoiceResource($invoice->load('customer')), 'Invoice created.', 201);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        return ApiResponse::success(new InvoiceResource($this->invoiceService->find($invoice)));
    }

    public function update(Invoice $invoice, InvoiceUpdateRequest $request): JsonResponse
    {
        try {
            $invoice = $this->invoiceService->update($invoice, $request->validated());
        } catch (InvalidArgumentException $exception) {
            return ApiResponse::error($exception->getMessage(), 422);
        }

        return ApiResponse::success(new InvoiceResource($invoice->load('customer')), 'Invoice updated.');
    }

    public function markPaid(Invoice $invoice): JsonResponse
    {
        $invoice = $this->invoiceService->markPaid($invoice);

        return ApiResponse::success(new InvoiceResource($invoice->load('customer')), 'Invoice marked as paid.');
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        try {
            $this->invoiceService->delete($invoice);
        } catch (InvalidArgumentException $exception) {
            return ApiResponse::error($exception->getMessage(), 422);
        }

        return ApiResponse::success(null, 'Invoice deleted.');
    }
}