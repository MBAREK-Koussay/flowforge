<?php

namespace App\Domain\PurchaseRequest\Models;

use App\Domain\PurchaseRequest\Enums\PurchaseRequestStatus;
use App\Domain\User\Models\User;
use Database\Factories\PurchaseRequestFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequest extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return PurchaseRequestFactory::new();
    }

    protected $fillable = [
        'employee_id',
        'amount',
        'description',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => PurchaseRequestStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}