<?php

namespace App\Enums;

use Illuminate\Support\Facades\Lang;

enum TripStepStatus: int
{
    case NOT_STARTED = 1;
    case ON_THE_WAY  = 2;
    case ARRIVED     = 3;
    case PROCESSING  = 4;
    case COMPLETED   = 5;

    public function label(): string
    {
        return Lang::get('app.enums.trip_step_status.' . strtolower($this->name));
    }

    public function color(): string
    {
        return match ($this) {
            self::NOT_STARTED => 'bg-gray-100 text-gray-700',
            self::ON_THE_WAY => 'bg-blue-100 text-blue-800',
            self::ARRIVED => 'bg-sky-100 text-sky-800',
            self::PROCESSING => 'bg-amber-100 text-amber-800',
            self::COMPLETED => 'bg-green-100 text-green-800',
        };
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
