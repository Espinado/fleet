<?php

namespace App\Enums;

enum OdometerEventType: int
{
    case RUN_START      = 1;
    case STEP_ARRIVED   = 2;
    case STEP_COMPLETED = 3;
    case RUN_END        = 4;
    case MANUAL         = 99;

    public function label(): string
    {
        return match ($this) {
            self::RUN_START      => 'Run started',
            self::STEP_ARRIVED   => 'Step arrived',
            self::STEP_COMPLETED => 'Step completed',
            self::RUN_END        => 'Run ended',
            self::MANUAL         => 'Manual entry',
        };
    }
}
