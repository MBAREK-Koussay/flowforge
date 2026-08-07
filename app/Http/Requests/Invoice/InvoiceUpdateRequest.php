<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['sometimes', 'integer', 'exists:customers,id'],
            'amount' => ['sometimes', 'numeric', 'min:0', 'decimal:0,2'],
            'due_date' => ['sometimes', 'date'],
        ];
    }
}