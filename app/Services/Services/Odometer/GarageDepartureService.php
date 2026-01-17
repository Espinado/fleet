<?php

namespace App\Services\Services\Odometer;

use App\Models\Truck;
use App\Models\TruckOdometerEvent;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GarageDepartureService
{
    public function __construct(
        protected MaponOdometerFetcher $fetcher
    ) {}

    public function recordDeparture(Truck $truck, ?int $driverId = null): TruckOdometerEvent
    {
        $unitId = $truck->mapon_unit_id;
        if (!$unitId) {
            throw new RuntimeException('mapon_unit_id не задан для данного трака.');
        }

        // 🔒 защита от двойного выезда
        $last = TruckOdometerEvent::where('truck_id', $truck->id)
            ->latest('occurred_at')
            ->first();

        if ($last && (int)$last->type === TruckOdometerEvent::TYPE_DEPARTURE) {
            throw new RuntimeException('Выезд уже зафиксирован. Сначала отметьте возврат в гараж.');
        }

        $can = $this->fetcher->fetchCanOdometer($unitId);
        if (!$can) {
            throw new RuntimeException('Не удалось получить данные из Mapon.');
        }

        if ($can['km'] === null) {
            throw new RuntimeException('Mapon не вернул CAN odometer (can.odom.value).');
        }

        // ⚠️ Если CAN меньше предыдущего — не блокируем, но пишем note
        $note = null;
        $prev = TruckOdometerEvent::where('truck_id', $truck->id)
            ->whereNotNull('odometer_km')
            ->latest('occurred_at')
            ->first();

        if ($prev && (float)$can['km'] < (float)$prev->odometer_km) {
            $note = "⚠️ CAN odometer меньше предыдущего ({$prev->odometer_km}).";
        }

        return DB::transaction(function () use ($truck, $driverId, $can, $note) {
            return TruckOdometerEvent::create([
                'truck_id' => $truck->id,
                'driver_id' => $driverId,
                'type' => TruckOdometerEvent::TYPE_DEPARTURE,
                'odometer_km' => $can['km'],
                'source' => TruckOdometerEvent::SOURCE_CAN,
                'occurred_at' => now(),
                'mapon_at' => $can['mapon_at'] ?? null,
                'is_stale' => (bool) ($can['is_stale'] ?? false),
                'stale_minutes' => $can['stale_minutes'] ?? null,
               'raw' => is_array($can['raw'] ?? null) ? $can['raw'] : null,
                'note' => $note,
            ]);
        });
    }

    public function recordReturn(Truck $truck, ?int $driverId = null): TruckOdometerEvent
{
    $unitId = $truck->mapon_unit_id;
    if (!$unitId) {
        throw new RuntimeException('mapon_unit_id не задан для данного трака.');
    }

    // Должен быть открытый выезд
    $last = TruckOdometerEvent::where('truck_id', $truck->id)
        ->latest('occurred_at')
        ->first();

    if (!$last || (int)$last->type !== TruckOdometerEvent::TYPE_DEPARTURE) {
        throw new RuntimeException('Нельзя отметить возврат: нет открытого выезда.');
    }

    $can = $this->fetcher->fetchCanOdometer($unitId);
    if (!$can) {
        throw new RuntimeException('Не удалось получить данные из Mapon.');
    }

    if ($can['km'] === null) {
        throw new RuntimeException('Mapon не вернул CAN odometer (can.odom.value).');
    }

    $note = null;

    // Возвратный одометр не должен быть меньше выездного
    if ($last->odometer_km !== null && (float)$can['km'] < (float)$last->odometer_km) {
        $note = "⚠️ CAN odometer меньше odometer выезда ({$last->odometer_km}).";
    }

    return DB::transaction(function () use ($truck, $driverId, $can, $note) {
        return TruckOdometerEvent::create([
            'truck_id' => $truck->id,
            'driver_id' => $driverId,
            'type' => TruckOdometerEvent::TYPE_RETURN,
            'odometer_km' => $can['km'],
            'source' => TruckOdometerEvent::SOURCE_CAN,
            'occurred_at' => now(),
            'mapon_at' => $can['mapon_at'] ?? null,
            'is_stale' => (bool) ($can['is_stale'] ?? false),
            'stale_minutes' => $can['stale_minutes'] ?? null,
            'raw' => is_array($can['raw'] ?? null) ? $can['raw'] : null,
            'note' => $note,
        ]);
    });
}

}
