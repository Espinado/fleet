<?php

namespace App\Enums;

enum TripExpenseCategory: string
{
    case FUEL         = 'fuel';
    case TOLL         = 'toll';
    case PARKING      = 'parking';
    case FINE         = 'fine';
    case PERMIT       = 'permit';
    case REPAIR       = 'repair';
    case HOTEL        = 'hotel';
    case SUBCONTRACTOR= 'subcontractor'; // ✅ новое
    case OTHER        = 'other';

    public function label(): string
    {
        return match ($this) {
            self::FUEL          => 'Degviela',
            self::TOLL          => 'Ceļa nodevas',
            self::PARKING       => 'Stāvvieta',
            self::FINE          => 'Sods',
            self::PERMIT        => 'Atļauja',
            self::REPAIR        => 'Remonts / Serviss',
            self::HOTEL         => 'Naktsmītne',
            self::SUBCONTRACTOR => 'Apakšpārvadātājs', // ✅ оплата третьей стороне
            self::OTHER         => 'Cits izdevums',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [
            $case->value => $case->label(),
        ])->toArray();
    }
}
