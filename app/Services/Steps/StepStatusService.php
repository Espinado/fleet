<?php

namespace App\Services\Steps;

use App\Models\TripStep;
use App\Models\TruckOdometerEvent;
use App\Enums\TripStepStatus;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StepStatusService
{
    /**
     * Записывает смену статуса шага:
     * - обновляет timeline поля (on_the_way_at/arrived_at/processing_at/completed_at)
     * - пишет odometer snapshot в соответствующее поле
     * - создаёт TruckOdometerEvent TYPE_STEP
     *
     * $odometerKm может быть null (если пока не удалось получить)
     */
    public function setStatus(
        TripStep $step,
        TripStepStatus $toStatus,
        ?float $odometerKm,
        int $odometerSource,
        ?Carbon $occurredAt = null,
        array $raw = []
    ): TripStep {
        $occurredAt ??= now();

        return DB::transaction(function () use ($step, $toStatus, $odometerKm, $odometerSource, $occurredAt, $raw) {

            $fromStatus = $step->status instanceof TripStepStatus
                ? $step->status
                : TripStepStatus::from((int)$step->status);

            // === MONOTONICITY GUARD FOR ODOMETER ===
            // If odometer is provided for this status change, make sure it does not go backwards
            // compared to any previously known odometer snapshot for this trip/truck.
            if ($odometerKm !== null) {
                $trip = $step->trip()->with('odometerEvents')->first();

                $lastKm = null;

                // 1) Trip-level snapshots (garage start/end)
                if ($trip) {
                    if ($trip->odo_start_km !== null) {
                        $lastKm = max($lastKm ?? (float) $trip->odo_start_km, (float) $trip->odo_start_km);
                    }
                    if ($trip->odo_end_km !== null) {
                        $lastKm = max($lastKm ?? (float) $trip->odo_end_km, (float) $trip->odo_end_km);
                    }
                }

                // 2) Existing odometer snapshots on steps of this trip
                $existingStepKm = [
                    $step->odo_on_the_way_km,
                    $step->odo_arrived_km,
                    $step->odo_completed_km,
                ];

                foreach ($existingStepKm as $km) {
                    if ($km !== null) {
                        $lastKm = max($lastKm ?? (float) $km, (float) $km);
                    }
                }

                // 3) TruckOdometerEvent history for this trip/truck
                if ($trip && $trip->odometerEvents) {
                    foreach ($trip->odometerEvents as $e) {
                        if ($e->odometer_km !== null) {
                            $lastKm = max($lastKm ?? (float) $e->odometer_km, (float) $e->odometer_km);
                        }
                    }
                }

                if ($lastKm !== null && $odometerKm < $lastKm) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Step odometer (%.1f) cannot be less than last known (%.1f).',
                            $odometerKm,
                            $lastKm
                        )
                    );
                }
            }

            // 1) Обновляем статус
            $step->status = $toStatus;

            // 2) Обновляем timeline + odometer поля в зависимости от статуса
            //    started_at/completed_at оставляем совместимыми:
            //    - PROCESSING: можно ставить processing_at и started_at (если started_at пустой)
            //    - COMPLETED: ставим completed_at
            switch ($toStatus) {
                case TripStepStatus::ON_THE_WAY:
                    $step->on_the_way_at ??= $occurredAt;
                    if ($odometerKm !== null) {
                        $step->odo_on_the_way_km = $odometerKm;
                        $step->odo_on_the_way_source = $odometerSource;
                    }
                    break;

                case TripStepStatus::ARRIVED:
                    $step->arrived_at ??= $occurredAt;
                    if ($odometerKm !== null) {
                        $step->odo_arrived_km = $odometerKm;
                        $step->odo_arrived_source = $odometerSource;
                    }
                    break;

                case TripStepStatus::PROCESSING:
                    $step->processing_at ??= $occurredAt;
                    // backward-compat: started_at часто использовался как "начал обработку"
                    $step->started_at ??= $occurredAt;

                    // Если arrived не был зафиксирован — можем “подтянуть”
                    $step->arrived_at ??= $occurredAt;

                    if ($odometerKm !== null) {
                        // если odo_arrived пуст — заполним хотя бы его
                        if ($step->odo_arrived_km === null) {
                            $step->odo_arrived_km = $odometerKm;
                            $step->odo_arrived_source = $odometerSource;
                        }
                    }
                    break;

                case TripStepStatus::COMPLETED:
                    $step->completed_at ??= $occurredAt;
                    if ($odometerKm !== null) {
                        $step->odo_completed_km = $odometerKm;
                        $step->odo_completed_source = $odometerSource;
                    }
                    break;

                case TripStepStatus::NOT_STARTED:
                    // обычно вручную назад не откатываем; ничего не делаем
                    break;
            }

            $step->save();

            // 3) Пишем событие (audit log)
            TruckOdometerEvent::create([
                'truck_id' => (int) $step->trip->truck_id,
                'driver_id' => (int) ($step->trip->driver_id ?? 0) ?: null,

                'trip_id' => (int) $step->trip_id,
                'trip_step_id' => (int) $step->id,

                'type' => TruckOdometerEvent::TYPE_STEP,
                'step_status' => $toStatus->value,

                'odometer_km' => $odometerKm,
                'source' => $odometerSource,

                'occurred_at' => $occurredAt,

                'note' => sprintf(
                    'Step #%d %s: %s → %s',
                    $step->id,
                    $step->type,
                    $fromStatus->label(),
                    $toStatus->label()
                ),

                'raw' => array_merge([
                    'step' => [
                        'from' => $fromStatus->value,
                        'to' => $toStatus->value,
                        'from_label' => $fromStatus->label(),
                        'to_label' => $toStatus->label(),
                        'type' => $step->type,
                        'order' => $step->order,
                    ],
                ], $raw),
            ]);

            return $step->fresh();
        });
    }
}
