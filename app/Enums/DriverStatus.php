<?php

namespace App\Enums;

enum DriverStatus: int
{
    case ON_WORK   = 1;
    case ON_VACATION = 2;
    case ILL       = 3;
    case STOPPED       = 4;
   public function label(): string
{
    return match ($this) {
        self::ON_WORK     => '👷 On work',
        self::ON_VACATION => '🏖️ On vacation',
        self::ILL         => '🤒 Ill',
        self::STOPPED     => '🛑 Stopped',
    };
}

    public static function options(): array
    {
        return [
            self::ON_WORK->value    => self::ON_WORK->label(),
            self::ON_VACATION->value => self::ON_VACATION->label(),
            self::ILL->value        => self::ILL->label(),
             self::STOPPED->value        => self::STOPPED->label(),
        ];
    }


}
