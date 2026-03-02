<?php

namespace App\Services\Expenses;

use App\Models\TripExpense;
use App\Models\TruckOdometerEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExpenseEventService
{
    public function record(
        TripExpense $expense,
        ?float $odometerKm,
        int $odometerSource,
        ?Carbon $occurredAt = null,
        array $raw = []
    ): TruckOdometerEvent {

        $occurredAt ??= $expense->expense_date
            ? Carbon::parse($expense->expense_date)->startOfDay()
            : now();

        return DB::transaction(function () use ($expense, $odometerKm, $odometerSource, $occurredAt, $raw) {

            $expense->loadMissing('trip');

            $trip = $expense->trip;
            $truckId  = (int) ($trip?->truck_id ?? 0) ?: null;
            $driverId = (int) ($trip?->driver_id ?? 0) ?: null;

            $categoryValue = is_object($expense->category)
                ? ($expense->category->value ?? null)
                : (string) $expense->category;

            $categoryLabel = is_object($expense->category) && method_exists($expense->category, 'label')
                ? $expense->category->label()
                : (string) $categoryValue;

            // 1) Try to reuse existing event (avoid duplicates)
            $event = null;

            if (!empty($expense->truck_odometer_event_id)) {
                $event = TruckOdometerEvent::query()
                    ->whereKey((int) $expense->truck_odometer_event_id)
                    ->lockForUpdate()
                    ->first();

                // safety: if linked event is not expense type, ignore it and create new
                if ($event && (int)$event->type !== TruckOdometerEvent::TYPE_EXPENSE) {
                    $event = null;
                }
            }

            // Also: if event exists by trip_expense_id, reuse it (extra safety)
            if (!$event) {
                $event = TruckOdometerEvent::query()
                    ->where('type', TruckOdometerEvent::TYPE_EXPENSE)
                    ->where('trip_expense_id', (int) $expense->id)
                    ->lockForUpdate()
                    ->latest('id')
                    ->first();
            }

            $payload = [
                'truck_id' => $truckId,
                'driver_id' => $driverId,

                'trip_id' => (int) $expense->trip_id,
                'trip_expense_id' => (int) $expense->id,

                'type' => TruckOdometerEvent::TYPE_EXPENSE,

                'expense_category' => $categoryValue,
                'expense_amount' => (float) $expense->amount,

                'odometer_km' => $odometerKm,
                'source' => $odometerSource,

                'occurred_at' => $occurredAt,

                'note' => sprintf(
                    'Expense #%d: %s (%.2f %s)',
                    $expense->id,
                    $categoryLabel ?: ($categoryValue ?: 'expense'),
                    (float) $expense->amount,
                    (string) ($expense->currency ?? 'EUR')
                ),

                'raw' => array_merge([
                    'expense' => [
                        'id' => $expense->id,
                        'category' => $categoryValue,
                        'amount' => $expense->amount,
                        'currency' => $expense->currency,
                    ],
                ], $raw),
            ];

            if ($event) {
                // 2) Update existing event
                $event->fill($payload)->save();
            } else {
                // 3) Create new event
                $event = TruckOdometerEvent::create($payload);

                // Log creation of TYPE_EXPENSE event with key details
                Log::info('TruckOdometerEvent TYPE_EXPENSE created', [
                    'trip_id'     => $payload['trip_id'] ?? null,
                    'driver_id'   => $payload['driver_id'] ?? null,
                    'liters'      => $expense->liters ?? null,
                    'odometer_km' => $payload['odometer_km'] ?? null,
                ]);
            }

            // 4) Link back to expense (always keep in sync)
            $expense->update([
                'truck_odometer_event_id' => $event->id,
                'odometer_km' => $odometerKm,
                'odometer_source' => $odometerSource,
            ]);

            return $event;
        });
    }
}
