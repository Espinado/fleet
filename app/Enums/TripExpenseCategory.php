<?php

namespace App\Enums;

enum TripExpenseCategory: string
{
    case FUEL           = 'fuel';
    case ADBLUE         = 'adblue';          // ✅ новое
    case WASHER_FLUID   = 'washer_fluid';    // ✅ новое (logu mazgāšanas šķidrums)
    case CAR_WASH       = 'car_wash';        // ✅ новое (automazgātava)

    case TOLL           = 'toll';
    case PARKING        = 'parking';
    case FINE           = 'fine';
    case PERMIT         = 'permit';
    case REPAIR         = 'repair';
    case HOTEL          = 'hotel';
    case SUBCONTRACTOR  = 'subcontractor';
    case OTHER          = 'other';

    public function label(): string
    {
        return match ($this) {
            self::FUEL           => 'Degviela',
            self::ADBLUE         => 'AdBlue',
            self::WASHER_FLUID   => 'Logu mazgāšanas šķidrums',
            self::CAR_WASH       => 'Automazgātava',

            self::TOLL           => 'Ceļa nodevas',
            self::PARKING        => 'Stāvvieta',
            self::FINE           => 'Sods',
            self::PERMIT         => 'Atļauja',
            self::REPAIR         => 'Remonts / Serviss',
            self::HOTEL          => 'Naktsmītne',
            self::SUBCONTRACTOR  => 'Apakšpārvadātājs',
            self::OTHER          => 'Cits izdevums',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => $case->label(),
            ])
            ->toArray();
    }
}
