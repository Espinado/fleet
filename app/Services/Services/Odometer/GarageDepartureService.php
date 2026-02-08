<?php

namespace App\Services\Services\Odometer;

use App\Models\Trip;
use App\Models\Truck;
use App\Models\TruckOdometerEvent;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GarageDepartureService
{
    public function __construct(
        protected MaponOdometerFetcher $fetcher,
        protected VehicleRunService $runs,
    ) {}

    public function recordDeparture(Trip $trip, Truck $truck, ?int $driverId = null): TruckOdometerEvent
    {
        $unitId = $truck->mapon_unit_id;
        if (!$unitId) {
            throw new RuntimeException('mapon_unit_id не задан для данного трака.');
        }

        // 🔒 защита от двойного выезда по событиям
        $last = TruckOdometerEvent::where('truck_id', $truck->id)
            ->latest('occurred_at')
            ->first();

        if ($last && (int) $last->type === TruckOdometerEvent::TYPE_DEPARTURE) {
            throw new RuntimeException('Выезд уже зафиксирован. Сначала отметьте возврат в гараж.');
        }

        $companyId = (int) ($truck->company ?? 0);

        // ✅ CAN -> mileage
        $odo = $this->fetcher->fetchOdometer((int) $unitId, $companyId);
        if (!$odo) {
            throw new RuntimeException('Не удалось получить данные из Mapon.');
        }

        if (($odo['km'] ?? null) === null) {
            throw new RuntimeException('Mapon не вернул данные одометра (CAN/mileage).');
        }

        $km = (float) $odo['km'];

        // ⚠️ Если odometer меньше предыдущего — не блокируем, но пишем note
        $note = null;
        $prev = TruckOdometerEvent::where('truck_id', $truck->id)
            ->whereNotNull('odometer_km')
            ->latest('occurred_at')
            ->first();

        if ($prev && (float) $km < (float) $prev->odometer_km) {
            $note = "⚠️ Odometer меньше предыдущего ({$prev->odometer_km}).";
        }

        return DB::transaction(function () use ($trip, $truck, $driverId, $odo, $km, $note) {

        $source = (($odo['source'] ?? null) === 'can')
    ? TruckOdometerEvent::SOURCE_CAN
    : TruckOdometerEvent::SOURCE_MILEAGE;

            $event = TruckOdometerEvent::create([
                'truck_id'      => $truck->id,
                'driver_id'     => $driverId,
                'type'          => TruckOdometerEvent::TYPE_DEPARTURE,
                'odometer_km'   => $km,
                'source'        => $source,
                'occurred_at'   => now(),
                'mapon_at'      => $odo['mapon_at'] ?? null,
                'is_stale'      => (bool) ($odo['is_stale'] ?? false),
                'stale_minutes' => $odo['stale_minutes'] ?? null,
                'raw'           => is_array($odo['raw'] ?? null) ? $odo['raw'] : null,
                'note'          => $note,
            ]);

            // ✅ Открываем смену и привязываем её к Trip
            $this->runs->openRun(
                trip: $trip,
                truck: $truck,
                driverId: $driverId,
                startKm: (float) $event->odometer_km
            );

            return $event;
        });
    }

    public function recordReturn(Trip $trip, Truck $truck, ?int $driverId = null): TruckOdometerEvent
    {
        $unitId = $truck->mapon_unit_id;
        if (!$unitId) {
            throw new RuntimeException('mapon_unit_id не задан для данного трака.');
        }

        // Должен быть открытый выезд
        $last = TruckOdometerEvent::where('truck_id', $truck->id)
            ->latest('occurred_at')
            ->first();

        if (!$last || (int) $last->type !== TruckOdometerEvent::TYPE_DEPARTURE) {
            throw new RuntimeException('Нельзя отметить возврат: нет открытого выезда.');
        }

        $companyId = (int) ($truck->company ?? 0);

        // ✅ CAN -> mileage
        $odo = $this->fetcher->fetchOdometer((int) $unitId, $companyId);
        if (!$odo) {
            throw new RuntimeException('Не удалось получить данные из Mapon.');
        }

        if (($odo['km'] ?? null) === null) {
            throw new RuntimeException('Mapon не вернул данные одометра (CAN/mileage).');
        }

        $km = (float) $odo['km'];

        $note = null;

        // Возвратный одометр не должен быть меньше выездного
        if ($last->odometer_km !== null && (float) $km < (float) $last->odometer_km) {
            $note = "⚠️ Odometer меньше odometer выезда ({$last->odometer_km}).";
        }

        return DB::transaction(function () use ($trip, $truck, $driverId, $odo, $km, $note) {

            $event = TruckOdometerEvent::create([
                'truck_id'      => $truck->id,
                'driver_id'     => $driverId,
                'type'          => TruckOdometerEvent::TYPE_RETURN,
                'odometer_km'   => $km,
                'source'        => $odo['source'] ?? null, // 'can' | 'mileage'
                'occurred_at'   => now(),
                'mapon_at'      => $odo['mapon_at'] ?? null,
                'is_stale'      => (bool) ($odo['is_stale'] ?? false),
                'stale_minutes' => $odo['stale_minutes'] ?? null,
                'raw'           => is_array($odo['raw'] ?? null) ? $odo['raw'] : null,
                'note'          => $note,
            ]);

            // ✅ Закрываем смену и отвязываем Trip
            $this->runs->closeRun(
                trip: $trip,
                truck: $truck,
                driverId: $driverId,
                endKm: (float) $event->odometer_km,
                reason: 'manual'
            );

            return $event;
        });
    }
}
