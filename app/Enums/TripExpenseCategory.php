<?php

namespace App\Enums;

use Illuminate\Support\Facades\Lang;

enum TripExpenseCategory: string
{
    case FUEL           = 'fuel';
    case ADBLUE         = 'adblue';
    case WASHER_FLUID   = 'washer_fluid';
    case CAR_WASH       = 'car_wash';
    case SPARE_PARTS    = 'spare_parts';

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
        return Lang::get('app.enums.trip_expense_category.' . $this->value);
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
