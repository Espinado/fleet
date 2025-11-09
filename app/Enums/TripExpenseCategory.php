<?php

namespace App\Enums;

enum TripExpenseCategory: string
{
    case FUEL     = 'fuel';
    case TOLL     = 'toll';
    case PARKING  = 'parking';
    case FINE     = 'fine';
    case PERMIT   = 'permit';
    case REPAIR   = 'repair';
    case HOTEL    = 'hotel';
    case OTHER    = 'other';

    /** Человеческое название категории */
    public function label(): string
    {
        return match ($this) {
            self::FUEL     => 'Degviela',
            self::TOLL     => 'Ceļa nodevas',
            self::PARKING  => 'Stāvvieta',
            self::FINE     => 'Sods',
            self::PERMIT   => 'Atļauja',
            self::REPAIR   => 'Remonts / Serviss',
            self::HOTEL    => 'Naktsmītne',
            self::OTHER    => 'Cits izdevums',
        };
    }

    /** Возвращает массив вида ['fuel' => 'Degviela', ...] */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [
            $case->value => $case->label(),
        ])->toArray();
    }
}
