<?php

namespace App\Livewire\DriverApp;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Trip;
use App\Models\TripStatusHistory;
use App\Models\TripStep;
use App\Enums\TripStepStatus;

class TripDetails extends Component
{
    public Trip $trip;
    public $steps;
    public $history;

    // ID шага, на котором произошла ошибка
    public $errorStepId = null;

    public function mount(Trip $trip)
    {
        $user = Auth::user();

        if (!$user || !$user->driver) {
            return redirect()->route('driver.login');
        }

        $this->trip = $trip;

        $this->steps = TripStep::where('trip_id', $trip->id)
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        $this->history = TripStatusHistory::where('trip_id', $trip->id)
            ->orderBy('time', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.driver-app.trip-details')
            ->layout('driver-app.layouts.app', [
                'title' => 'Рейс #' . $this->trip->id,
                'back'  => true,
            ]);
    }


    /**
     * Обновление статуса шага
     */
    public function updateStepStatus(int $stepId, int $newStatusInt)
    {
        $step = TripStep::findOrFail($stepId);
        $newStatus = TripStepStatus::from($newStatusInt);

        DB::beginTransaction();

        try {

            // Сбрасываем ошибочный шаг
            $this->errorStepId = null;

            // 1) Проверка — нельзя разгрузить раньше загрузки
            foreach ($step->cargos as $cargo) {

                if ($this->isUnloadingStep($step, $cargo)) {

                    $loadingSteps = $cargo->steps()
                        ->wherePivot('role', 'loading')
                        ->get();

                    $hasCompletedLoading = $loadingSteps->contains(
                        fn($s) => $s->status === TripStepStatus::COMPLETED
                    );

                    if (!$hasCompletedLoading) {

                        // 🚨 отмечаем шаг как ошибочный
                        $this->errorStepId = $step->id;

                        DB::rollBack();
                        $this->dispatch('error', 'Šo kravu vēl neesat iekraujis!');
                        return;
                    }
                }
            }

            // 2) Обновляем сам шаг
            $step->update([
                'status'       => $newStatus->value,
                'started_at'   => $newStatus === TripStepStatus::ON_THE_WAY
                                    ? now()
                                    : $step->started_at,
                'completed_at' => $newStatus === TripStepStatus::COMPLETED
                                    ? now()
                                    : $step->completed_at,
            ]);

            // 3) История статусов
            TripStatusHistory::create([
                'trip_id'   => $this->trip->id,
                'driver_id' => Auth::user()->driver->id,
                'status'    => "step_{$newStatus->value}",
                'time'      => now(),
                'comment'   => "Step #{$step->id} → {$newStatus->label()}",
            ]);

            // 4) Логика смены статуса рейса
            $this->updateTripStatusBasedOnSteps();

            DB::commit();

            // Обновляем отображение шагов
            $this->steps = TripStep::where('trip_id', $this->trip->id)
                ->orderBy('order')
                ->orderBy('id')
                ->get();

            $this->dispatch('success', 'Status veiksmīgi atjaunots!');

        } catch (\Throwable $e) {

            DB::rollBack();
            report($e);

            $this->dispatch('error', 'Radās kļūda!');
        }
    }


    /**
     * Проверяет, является ли шаг разгрузкой для этого груза
     */
    private function isUnloadingStep(TripStep $step, $cargo): bool
    {
        $pivot = $step->cargos()
            ->where('trip_cargo_id', $cargo->id)
            ->first()
            ?->pivot;

        return $pivot?->role === 'unloading';
    }


    /**
     * Логика статуса всего рейса
     */
    private function updateTripStatusBasedOnSteps()
    {
        $steps = $this->trip
            ->steps()
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        $first = $steps->first();
        $last  = $steps->last();

        // 1) Trip → IN_PROGRESS
        if ($first->status !== TripStepStatus::NOT_STARTED) {

            if ($this->trip->status !== 'in_progress') {

                $this->trip->update(['status' => 'in_progress']);

                TripStatusHistory::create([
                    'trip_id'   => $this->trip->id,
                    'driver_id' => Auth::user()->driver->id,
                    'status'    => 'trip_in_progress',
                    'time'      => now(),
                    'comment'   => 'Trip sākts',
                ]);
            }
        }

        // 2) Trip → COMPLETED
        if ($last->status === TripStepStatus::COMPLETED) {

            $this->trip->update(['status' => 'completed']);

            TripStatusHistory::create([
                'trip_id'   => $this->trip->id,
                'driver_id' => Auth::user()->driver->id,   // ← ДОБАВИЛ
                'status'    => 'trip_completed',
                'time'      => now(),
                'comment'   => 'Trip pabeigts',
            ]);
        }
    }
}
