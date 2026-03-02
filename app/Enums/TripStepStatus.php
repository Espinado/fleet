<?php

namespace App\Enums;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

enum TripStepStatus: int
{
    case NOT_STARTED = 1;
    case ON_THE_WAY  = 2;
    case ARRIVED     = 3;
    case PROCESSING  = 4;
    case COMPLETED   = 5;

    public function label(): string
    {
        return Lang::get('app.enums.trip_step_status.' . Str::snake($this->name));
    }

    public static function options(): array
    {
        return [
            self::NOT_STARTED->value => self::NOT_STARTED->label(),
            self::ON_THE_WAY->value  => self::ON_THE_WAY->label(),
            self::ARRIVED->value     => self::ARRIVED->label(),
            self::PROCESSING->value  => self::PROCESSING->label(),
            self::COMPLETED->value   => self::COMPLETED->label(),
        ];
    }
}
