<?php

namespace App\Domain\Product\Enums;

enum StockMovementType: string
{
    case IN = 'in';
    case OUT = 'out';
    case ADJUSTMENT = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::IN => 'Stock in',
            self::OUT => 'Stock out',
            self::ADJUSTMENT => 'Adjustment',
        };
    }
}