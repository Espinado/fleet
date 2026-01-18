<?php

namespace App\Services\Services\Odometer;

use App\Models\Trip;
use App\Models\Truck;
use App\Models\VehicleRun;
use Illuminate\Support\Facades\DB;

class VehicleRunService
{
    /**
     * Открываем смену (если уже есть open для этого truck — вернём её)
     */
    public function openRun(Trip $trip, Truck $truck, ?int $driverId, float $startKm): VehicleRun
    {
        return DB::transaction(function () use ($trip, $truck, $driverId, $startKm) {

            // если уже есть открытая смена по этому траку — используем её
            $existing = VehicleRun::where('truck_id', $truck->id)
                ->where('status', 'open')
                ->latest('id')
                ->first();

            if ($existing) {
                // привяжем trip к этой смене (если ещё не привязан)
                if ($trip->vehicle_run_id !== $existing->id) {
                    $trip->update(['vehicle_run_id' => $existing->id]);
                }
                return $existing;
            }

            $run = VehicleRun::create([
                'truck_id' => $truck->id,
                'driver_id' => $driverId,
                'started_at' => now(),
                'start_can_odom_km' => $startKm,
                'status' => 'open',
                'created_by' => 'manual',
            ]);

            // ✅ привязываем Trip к смене
            $trip->update(['vehicle_run_id' => $run->id]);

            return $run;
        });
    }

    /**
     * Закрываем смену (latest open по truck)
     */
    public function closeRun(Trip $trip, Truck $truck, ?int $driverId, float $endKm, string $reason = 'manual'): ?VehicleRun
    {
        return DB::transaction(function () use ($trip, $truck, $driverId, $endKm, $reason) {

            $run = VehicleRun::where('truck_id', $truck->id)
                ->where('status', 'open')
                ->latest('id')
                ->first();

            if (!$run) {
                // нечего закрывать
                return null;
            }

            $run->update([
                'ended_at' => now(),
                'end_can_odom_km' => $endKm,
                'status' => 'closed',
                'close_reason' => $reason,
            ]);

            // ✅ отвязываем Trip (теперь рейс перестанет считаться активным)
            $trip->update(['vehicle_run_id' => null]);

            return $run;
        });
    }
}
