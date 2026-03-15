<?php

namespace App\Enums;

use Illuminate\Support\Facades\Lang;

enum OrderStatus: string
{
    case DRAFT     = 'draft';
    case QUOTED    = 'quoted';
    case CONFIRMED = 'confirmed';
    case CONVERTED = 'converted';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return Lang::get('app.enums.order_status.' . $this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT     => 'bg-gray-200 text-gray-800',
            self::QUOTED    => 'bg-blue-100 text-blue-800',
            self::CONFIRMED => 'bg-green-100 text-green-800',
            self::CONVERTED => 'bg-emerald-200 text-emerald-900',
            self::CANCELLED => 'bg-red-100 text-red-800',
        };
    }
}
