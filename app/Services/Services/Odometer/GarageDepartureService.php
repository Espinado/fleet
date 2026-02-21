<?php

namespace App\Services\Services\Odometer;

use App\Models\Trip;
use App\Models\Truck;
use App\Models\TruckOdometerEvent;

use App\Enums\TripStatus;
use App\Enums\TripStepStatus;

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

        $odo = $this->fetcher->fetchOdometer($unitId, $truck->company);
        if (!$odo) {
            throw new RuntimeException('Не удалось получить данные из Mapon.');
        }

        if ($odo['km'] === null) {
            throw new RuntimeException('Mapon не вернул одометр (CAN и mileage пустые).');
        }

        // source int
        $sourceInt = ($odo['source'] ?? null) === 'can'
            ? TruckOdometerEvent::SOURCE_CAN
            : TruckOdometerEvent::SOURCE_MILEAGE;

        // ⚠️ Если одометр меньше предыдущего — note
        $note = null;
        $prev = TruckOdometerEvent::where('truck_id', $truck->id)
            ->whereNotNull('odometer_km')
            ->latest('occurred_at')
            ->first();

        if ($prev && (float) $odo['km'] < (float) $prev->odometer_km) {
            $note = "⚠️ Odometer меньше предыдущего ({$prev->odometer_km}).";
        }

        return DB::transaction(function () use ($trip, $truck, $driverId, $odo, $sourceInt, $note) {

            $event = TruckOdometerEvent::create([
                'truck_id'      => $truck->id,
                'driver_id'     => $driverId,
                'type'          => TruckOdometerEvent::TYPE_DEPARTURE,
                'odometer_km'   => (float) $odo['km'],
                'source'        => $sourceInt,
                'occurred_at'   => now(),
                'mapon_at'      => $odo['mapon_at'] ?? null,
                'is_stale'      => (bool) ($odo['is_stale'] ?? false),
                'stale_minutes' => $odo['stale_minutes'] ?? null,
                'raw'           => is_array($odo['raw'] ?? null) ? $odo['raw'] : null,
                'note'          => $note,
            ]);

            $this->runs->openRun(
                trip: $trip,
                truck: $truck,
                driverId: $driverId,
                startKm: (float) $event->odometer_km
            );

            // ✅ если рейс был AWAITING_GARAGE и водитель снова выехал — вернём в IN_PROGRESS
            $trip->refresh();
            if ($trip->status === TripStatus::AWAITING_GARAGE) {
                $trip->forceFill(['status' => TripStatus::IN_PROGRESS])->save();
            }

            return $event;
        });
    }

    public function recordReturn(Trip $trip, Truck $truck, ?int $driverId = null): TruckOdometerEvent
    {
        $unitId = $truck->mapon_unit_id;
        if (!$unitId) {
            throw new RuntimeException('mapon_unit_id не задан для данного трака.');
        }

        $last = TruckOdometerEvent::where('truck_id', $truck->id)
            ->latest('occurred_at')
            ->first();

        if (!$last || (int) $last->type !== TruckOdometerEvent::TYPE_DEPARTURE) {
            throw new RuntimeException('Нельзя отметить возврат: нет открытого выезда.');
        }

        $odo = $this->fetcher->fetchOdometer($unitId, $truck->company);
        if (!$odo) {
            throw new RuntimeException('Не удалось получить данные из Mapon.');
        }

        if ($odo['km'] === null) {
            throw new RuntimeException('Mapon не вернул одометр (CAN и mileage пустые).');
        }

        $sourceInt = ($odo['source'] ?? null) === 'can'
            ? TruckOdometerEvent::SOURCE_CAN
            : TruckOdometerEvent::SOURCE_MILEAGE;

        $note = null;
        if ($last->odometer_km !== null && (float) $odo['km'] < (float) $last->odometer_km) {
            $note = "⚠️ Odometer меньше odometer выезда ({$last->odometer_km}).";
        }

        return DB::transaction(function () use ($trip, $truck, $driverId, $odo, $sourceInt, $note) {

            $event = TruckOdometerEvent::create([
                'truck_id'      => $truck->id,
                'driver_id'     => $driverId,
                'type'          => TruckOdometerEvent::TYPE_RETURN,
                'odometer_km'   => (float) $odo['km'],
                'source'        => $sourceInt,
                'occurred_at'   => now(),
                'mapon_at'      => $odo['mapon_at'] ?? null,
                'is_stale'      => (bool) ($odo['is_stale'] ?? false),
                'stale_minutes' => $odo['stale_minutes'] ?? null,
                'raw'           => is_array($odo['raw'] ?? null) ? $odo['raw'] : null,
                'note'          => $note,
            ]);

            $this->runs->closeRun(
                trip: $trip,
                truck: $truck,
                driverId: $driverId,
                endKm: (float) $event->odometer_km,
                reason: 'manual'
            );

            // ✅ После закрытия смены проверяем: все ли шаги завершены
            $trip->refresh();

            $allStepsCompleted = !$trip->steps()
                ->where('status', '!=', TripStepStatus::COMPLETED->value)
                ->exists();

            if ($allStepsCompleted && $trip->status !== TripStatus::COMPLETED) {
                $trip->forceFill(['status' => TripStatus::COMPLETED])->save();
            }

            return $event;
        });
    }
}
