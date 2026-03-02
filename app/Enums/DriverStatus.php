<?php

namespace App\Enums;

use Illuminate\Support\Facades\Lang;

enum DriverStatus: int
{
    case ON_WORK     = 1;
    case ON_VACATION = 2;
    case ILL         = 3;
    case STOPPED     = 4;

    public function label(): string
    {
        return Lang::get('app.enums.driver_status.' . strtolower($this->name));
    }

    public function color(): string
    {
        // хвостик для Tailwind (green/red/yellow/gray и т.п.)
        return match ($this) {
            self::ON_WORK     => 'green',
            self::ON_VACATION => 'yellow',
            self::ILL         => 'orange',
            self::STOPPED     => 'red',
        };
    }

    public static function options(): array
    {
        return [
            self::ON_WORK->value      => self::ON_WORK->label(),
            self::ON_VACATION->value  => self::ON_VACATION->label(),
            self::ILL->value          => self::ILL->label(),
            self::STOPPED->value      => self::STOPPED->label(),
        ];
    }
}
