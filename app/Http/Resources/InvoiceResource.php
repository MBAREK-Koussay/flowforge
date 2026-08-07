<?php

namespace App\Http\Resources;

use App\Domain\Invoice\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Invoice */
class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => (float) $this->amount,
            'due_date' => $this->due_date?->toDateString(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer?->id,
                'company_name' => $this->customer?->company_name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}