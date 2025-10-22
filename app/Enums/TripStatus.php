<?php

namespace App\Enums;

enum TripStatus: string
{
    case PLANNED     = 'planned';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED   = 'completed';
    case CANCELLED   = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PLANNED     => 'Planned',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED   => 'Completed',
            self::CANCELLED   => 'Cancelled',
        };
    }

    // ✅ Цвет бейджа для Tailwind CSS
    public function color(): string
    {
        return match ($this) {
            self::PLANNED     => 'bg-gray-200 text-gray-800',
            self::IN_PROGRESS => 'bg-blue-100 text-blue-800',
            self::COMPLETED   => 'bg-green-100 text-green-800',
            self::CANCELLED   => 'bg-red-100 text-red-800',
        };
    }
}
