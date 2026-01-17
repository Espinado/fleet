<?php

namespace App\Services\Services;

use App\Enums\OdometerEventType;
use App\Models\{VehicleRun, OdometerEvent, Truck, Driver, Trip, TripStep};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VehicleRunService
{
    /**
     * Найти открытую смену (run) по траку.
     */
    public function findOpenRunForTruck(int $truckId): ?VehicleRun
    {
        return VehicleRun::query()
            ->where('truck_id', $truckId)
            ->where('status', 'open')
            ->orderByDesc('started_at')
            ->first();
    }

    /**
     * Открыть смену вручную/авто.
     */
    public function openRun(
        Truck $truck,
        ?Driver $driver,
        ?float $canOdomKm,
        ?string $canAt = null,
        ?float $engineHours = null,
        string $createdBy = 'manual'
    ): VehicleRun {
        return DB::transaction(function () use ($truck, $driver, $canOdomKm, $canAt, $engineHours, $createdBy) {
            // если уже есть open-run — возвращаем его (не создаём дубль)
            $existing = $this->findOpenRunForTruck($truck->id);
            if ($existing) {
                return $existing;
            }

            $run = VehicleRun::create([
                'truck_id'          => $truck->id,
                'driver_id'         => $driver?->id,
                'started_at'        => now(),
                'start_can_odom_km' => $canOdomKm,
                'start_engine_hours'=> $engineHours,
                'status'            => 'open',
                'created_by'        => $createdBy,
            ]);

            $this->recordEvent(
                truck: $truck,
                run: $run,
                type: OdometerEventType::RUN_START,
                trip: null,
                step: null,
                canOdomKm: $canOdomKm,
                canAt: $canAt,
                source: 'can'
            );

            return $run;
        });
    }

    /**
     * Закрыть смену.
     */
    public function closeRun(
        VehicleRun $run,
        ?float $canOdomKm,
        ?string $canAt = null,
        ?float $engineHours = null,
        string $closeReason = 'manual'
    ): VehicleRun {
        return DB::transaction(function () use ($run, $canOdomKm, $canAt, $engineHours, $closeReason) {
            if ($run->status === 'closed') {
                return $run;
            }

            $run->update([
                'ended_at'         => now(),
                'end_can_odom_km'  => $canOdomKm,
                'end_engine_hours' => $engineHours,
                'status'           => 'closed',
                'close_reason'     => $closeReason,
            ]);

            $this->recordEvent(
                truck: $run->truck,
                run: $run,
                type: OdometerEventType::RUN_END,
                trip: null,
                step: null,
                canOdomKm: $canOdomKm,
                canAt: $canAt,
                source: 'can'
            );

            return $run;
        });
    }

    /**
     * Главный метод для вашего кейса:
     * если смена не открыта — откроет её (system),
     * привяжет trip к run,
     * и вернёт run.
     */
    public function getOrOpenForTripStep(
        Trip $trip,
        TripStep $step,
        ?Driver $driver,
        ?float $canOdomKm,
        ?string $canAt = null,
        ?float $engineHours = null
    ): VehicleRun {
        return DB::transaction(function () use ($trip, $step, $driver, $canOdomKm, $canAt, $engineHours) {

            $truck = $trip->truck;

            $run = $this->findOpenRunForTruck($truck->id);

            if (!$run) {
                // авто-открытие смены (system)
                $run = $this->openRun(
                    truck: $truck,
                    driver: $driver,
                    canOdomKm: $canOdomKm,
                    canAt: $canAt,
                    engineHours: $engineHours,
                    createdBy: 'system'
                );
            }

            // если trip ещё не привязан — привязываем к смене
            if (!$trip->vehicle_run_id) {
                $trip->vehicle_run_id = $run->id;
                $trip->save();
            }

            return $run;
        });
    }

    /**
     * Записать событие одометра.
     */
    public function recordEvent(
        Truck $truck,
        ?VehicleRun $run,
        OdometerEventType $type,
        ?Trip $trip = null,
        ?TripStep $step = null,
        ?float $canOdomKm = null,
        ?string $canAt = null,
        string $source = 'can'
    ): OdometerEvent {
        $isStale = false;

        if ($canAt) {
            $days = Carbon::parse($canAt)->diffInDays(now());
            $threshold = (int) config('mapon.can_stale_days', 2);
            $isStale = $days >= $threshold;
        }

        return OdometerEvent::create([
            'truck_id'       => $truck->id,
            'vehicle_run_id' => $run?->id,
            'trip_id'        => $trip?->id,
            'trip_step_id'   => $step?->id,
            'event_type'     => $type->value,
            'event_at'       => now(),
            'can_odom_km'    => $canOdomKm,
            'can_at'         => $canAt,
            'source'         => $source,
            'is_stale'       => $isStale,
        ]);
    }
}
