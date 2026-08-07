<?php

namespace App\Domain\PurchaseRequest\Events;

use App\Domain\PurchaseRequest\Models\PurchaseRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class PurchaseRequestCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly PurchaseRequest $purchaseRequest) {}
}