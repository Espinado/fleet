<?php

namespace App\Enums;

enum TripStepStatus: int
{
    case NOT_STARTED = 1;   // Шаг ещё не начат
    case ON_THE_WAY  = 2;   // Водитель едет к адресу
    case ARRIVED     = 3;   // Прибыл на место
    case PROCESSING  = 4;   // Идёт загрузка/разгрузка
    case COMPLETED   = 5;   // Шаг завершён

    public function label(): string
    {
        return match ($this) {
            self::NOT_STARTED => 'Nav uzsākts',
            self::ON_THE_WAY  => 'Ceļā',
            self::ARRIVED     => 'Ieradies',
            self::PROCESSING  => 'Procesā',
            self::COMPLETED   => 'Pabeigts',
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
