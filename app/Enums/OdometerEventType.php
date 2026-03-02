<?php

namespace App\Enums;

use Illuminate\Support\Facades\Lang;

enum OdometerEventType: int
{
    case RUN_START      = 1;
    case STEP_ARRIVED   = 2;
    case STEP_COMPLETED = 3;
    case RUN_END        = 4;
    case MANUAL         = 99;

    public function label(): string
    {
        return Lang::get('app.enums.odometer_event_type.' . strtolower($this->name));
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::RUN_START => 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-900/20 dark:text-amber-200 dark:border-amber-800',
            self::STEP_ARRIVED,
            self::STEP_COMPLETED => 'bg-sky-50 text-sky-800 border-sky-200 dark:bg-sky-900/20 dark:text-sky-200 dark:border-sky-800',
            self::RUN_END => 'bg-violet-50 text-violet-800 border-violet-200 dark:bg-violet-900/20 dark:text-violet-200 dark:border-violet-800',
            self::MANUAL => 'bg-gray-50 text-gray-800 border-gray-200 dark:bg-gray-800/40 dark:text-gray-200 dark:border-gray-700',
        };
    }
}
