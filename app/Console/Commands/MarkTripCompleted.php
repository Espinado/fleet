<?php

namespace App\Console\Commands;

use App\Models\Trip;
use App\Enums\TripStatus;
use Illuminate\Console\Command;

class MarkTripCompleted extends Command
{
    protected $signature = 'trip:mark-completed {id : Trip ID}';

    protected $description = 'Пометить рейс как завершённый (status=completed, ended_at, vehicle_run_id=null), чтобы он не показывался водителю как активный.';

    public function handle(): int
    {
        $id = (int) $this->argument('id');
        $trip = Trip::withoutGlobalScopes()->find($id);

        if (!$trip) {
            $this->error("Рейс #{$id} не найден.");
            return self::FAILURE;
        }

        $trip->update([
            'status'         => TripStatus::COMPLETED,
            'ended_at'       => $trip->ended_at ?? now(),
            'vehicle_run_id' => null,
        ]);

        $this->info("Рейс #{$id} помечен как завершённый. Статус: {$trip->fresh()->status->value}");
        return self::SUCCESS;
    }
}
