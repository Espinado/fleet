<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\TripExpense;
use App\Models\TruckOdometerEvent;
use App\Services\Expenses\ExpenseEventService;

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
        $skipped = 0;
        $errors = 0;

        TripExpense::whereNotNull('trip_id')
            ->orderBy('id')
            ->chunkById(500, function ($chunk) use ($service, $apply, &$total, &$created, &$updated, &$skipped, &$errors) {
                foreach ($chunk as $expense) {
                    ++$total;

                    // Only sync expenses that conceptually can have odometer binding
                    if ($expense->odometer_km === null) {
                        ++$skipped;
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
                            $skipped++;

                            Log::info('DRY-RUN: would sync TripExpense with odometer event', [
                                'expense_id'    => $expense->id,
                                'trip_id'       => $expense->trip_id,
                                'truck_id'      => $expense->trip?->truck_id,
                                'driver_id'     => $expense->trip?->driver_id,
                                'odometer_km'   => $expense->odometer_km,
                                'odometer_src'  => $expense->odometer_source,
                                'has_event_id'  => (bool) $expense->truck_odometer_event_id,
                                'event_id'      => $expense->truck_odometer_event_id,
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
        $this->info("Skipped: {$skipped}");

        if ($errors > 0) {
            $this->error("Errors: {$errors}");
        }

        Log::info('SyncTripExpensesWithOdometerEvents finished', [
            'apply'   => $apply,
            'total'   => $total,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors'  => $errors,
        ]);

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}

