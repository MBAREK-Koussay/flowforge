<?php

namespace App\Domain\Customer\Models;

use App\Domain\Customer\Enums\CustomerStatus;
use App\Domain\Invoice\Models\Invoice;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return CustomerFactory::new();
    }

    protected $fillable = [
        'company_name',
        'contact_name',
        'email',
        'phone',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => CustomerStatus::class,
        ];
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function isActive(): bool
    {
        return $this->status === CustomerStatus::ACTIVE;
    }
}