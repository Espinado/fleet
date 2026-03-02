<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\TripExpense;
use App\Models\TruckOdometerEvent;
use App\Services\Expenses\ExpenseEventService;
use App\Enums\TripExpenseCategory;

class SyncTripExpensesWithOdometerEvents extends Command
{
    protected $signature = 'expenses:sync-odometer-events
                            {--apply : Apply changes (without this option runs in dry-run mode)}';

    protected $description = 'Mass sync TripExpense records with TruckOdometerEvent(TYPE_EXPENSE) in a production-safe way';

    public function handle(ExpenseEventService $service): int
    {
        $apply = (bool) $this->option('apply');

        $this->info('=== TripExpense ↔ TruckOdometerEvent(TYPE_EXPENSE) sync ===');
        $this->info($apply ? 'Mode: APPLY (changes will be written)' : 'Mode: DRY-RUN (no changes will be written)');

        $total = 0;
        $created = 0;
        $updated = 0;
        $skippedNoOdometer = 0;
        $skippedNotRequired = 0;
        $noopAlreadySynced = 0;
        $errors = 0;

        $odoRequiredCategories = [
            TripExpenseCategory::FUEL->value,
            TripExpenseCategory::ADBLUE->value,
        ];

        TripExpense::whereNotNull('trip_id')
            ->orderBy('id')
            ->chunkById(500, function ($chunk) use ($service, $apply, $odoRequiredCategories, &$total, &$created, &$updated, &$skippedNoOdometer, &$skippedNotRequired, &$noopAlreadySynced, &$errors) {
                foreach ($chunk as $expense) {
                    ++$total;

                    $categoryValue = is_object($expense->category)
                        ? ($expense->category->value ?? null)
                        : (string) $expense->category;

                    $isOdoRequired = in_array($categoryValue, $odoRequiredCategories, true);

                    // 1) Категория, где одометр не требуется
                    if (!$isOdoRequired) {
                        ++$skippedNotRequired;

                        if (!$apply) {
                            Log::info('DRY-RUN: skip TripExpense (odometer not required for category)', [
                                'expense_id'  => $expense->id,
                                'trip_id'     => $expense->trip_id,
                                'category'    => $categoryValue,
                                'odometer_km' => $expense->odometer_km,
                                'skip_reason' => 'not_required',
                            ]);
                        }

                        continue;
                    }

                    // 2) Категория с обязательным одометром, но он не заполнен
                    if ($expense->odometer_km === null) {
                        ++$skippedNoOdometer;

                        if (!$apply) {
                            Log::warning('DRY-RUN: skip TripExpense (odometer required but missing)', [
                                'expense_id'  => $expense->id,
                                'trip_id'     => $expense->trip_id,
                                'category'    => $categoryValue,
                                'skip_reason' => 'no_odometer',
                            ]);
                        }

                        continue;
                    }

                    try {
                        $odometerKm = (float) $expense->odometer_km;
                        $sourceRaw = (string) ($expense->odometer_source ?? '');

                        $source = match ($sourceRaw) {
                            'manual'  => TruckOdometerEvent::SOURCE_MANUAL,
                            'can'     => TruckOdometerEvent::SOURCE_CAN,
                            'mileage' => TruckOdometerEvent::SOURCE_MILEAGE,
                            default   => TruckOdometerEvent::SOURCE_MANUAL,
                        };

                        // 3) Проверка: уже есть корректное TYPE_EXPENSE-событие → noop
                        $existingEvent = null;

                        if (!empty($expense->truck_odometer_event_id)) {
                            $existingEvent = TruckOdometerEvent::query()
                                ->whereKey((int) $expense->truck_odometer_event_id)
                                ->where('type', TruckOdometerEvent::TYPE_EXPENSE)
                                ->first();
                        }

                        if (!$existingEvent) {
                            $existingEvent = TruckOdometerEvent::query()
                                ->where('type', TruckOdometerEvent::TYPE_EXPENSE)
                                ->where('trip_expense_id', (int) $expense->id)
                                ->latest('id')
                                ->first();
                        }

                        $isAlreadySynced = $existingEvent
                            && (int) $existingEvent->trip_id === (int) $expense->trip_id
                            && (int) $existingEvent->trip_expense_id === (int) $expense->id
                            && (float) $existingEvent->odometer_km === $odometerKm
                            && (int) $existingEvent->source === $source;

                        if ($isAlreadySynced) {
                            ++$noopAlreadySynced;

                            if (!$apply) {
                                Log::info('DRY-RUN: already synced TripExpense, no changes needed', [
                                    'expense_id'  => $expense->id,
                                    'trip_id'     => $expense->trip_id,
                                    'event_id'    => $existingEvent->id,
                                    'odometer_km' => $existingEvent->odometer_km,
                                    'source'      => $existingEvent->source,
                                    'skip_reason' => 'already_synced',
                                ]);
                            }

                            continue;
                        }

                        if ($apply) {
                            DB::transaction(function () use ($service, $expense, $odometerKm, $source, &$created, &$updated) {
                                $existingBefore = null;

                                if (!empty($expense->truck_odometer_event_id)) {
                                    $existingBefore = TruckOdometerEvent::find($expense->truck_odometer_event_id);
                                }

                                $event = $service->record(
                                    $expense,
                                    $odometerKm,
                                    $source,
                                    null,
                                    ['sync_command' => true]
                                );

                                if ($existingBefore && $existingBefore->id === $event->id) {
                                    ++$updated;
                                } else {
                                    ++$created;
                                }
                            });
                        } else {
                            Log::info('DRY-RUN: would sync TripExpense with odometer event', [
                                'expense_id'    => $expense->id,
                                'trip_id'       => $expense->trip_id,
                                'truck_id'      => $expense->trip?->truck_id,
                                'driver_id'     => $expense->trip?->driver_id,
                                'odometer_km'   => $expense->odometer_km,
                                'odometer_src'  => $expense->odometer_source,
                                'has_event_id'  => (bool) $expense->truck_odometer_event_id,
                                'event_id'      => $expense->truck_odometer_event_id,
                                'skip_reason'   => null,
                            ]);
                        }
                    } catch (\Throwable $e) {
                        ++$errors;

                        Log::error('TripExpense sync failed', [
                            'expense_id'  => $expense->id,
                            'trip_id'     => $expense->trip_id,
                            'message'     => $e->getMessage(),
                            'exception'   => get_class($e),
                        ]);

                        $this->error("Error on expense #{$expense->id}: {$e->getMessage()}");
                    }
                }
            });

        $this->info("Total expenses scanned: {$total}");
        $this->info("Created events: {$created}");
        $this->info("Updated events: {$updated}");
        $this->info("Skipped (odometer not required): {$skippedNotRequired}");
        $this->info("Skipped (required but no odometer): {$skippedNoOdometer}");
        $this->info("No-op (already synced): {$noopAlreadySynced}");

        if ($errors > 0) {
            $this->error("Errors: {$errors}");
        }

        Log::info('SyncTripExpensesWithOdometerEvents finished', [
            'apply'   => $apply,
            'total'   => $total,
            'created' => $created,
            'updated' => $updated,
            'skipped_not_required' => $skippedNotRequired,
            'skipped_no_odometer'  => $skippedNoOdometer,
            'noop_already_synced'  => $noopAlreadySynced,
            'errors'               => $errors,
        ]);

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}

