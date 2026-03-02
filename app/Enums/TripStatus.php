<?php

namespace App\Enums;

use Illuminate\Support\Facades\Lang;

enum TripStatus: string
{
    case PLANNED         = 'planned';
    case IN_PROGRESS     = 'in_progress';
    case AWAITING_GARAGE = 'awaiting_garage';
    case COMPLETED       = 'completed';
    case CANCELLED       = 'cancelled';

    public function label(): string
    {
        return Lang::get('app.enums.trip_status.' . $this->value);
    }

    // ✅ Цвет бейджа для Tailwind CSS
    public function color(): string
    {
        return match ($this) {
            self::PLANNED         => 'bg-gray-200 text-gray-800',
            self::IN_PROGRESS     => 'bg-blue-100 text-blue-800',
            self::AWAITING_GARAGE => 'bg-yellow-100 text-yellow-800',
            self::COMPLETED       => 'bg-green-100 text-green-800',
            self::CANCELLED       => 'bg-red-100 text-red-800',
        };
    }
}
