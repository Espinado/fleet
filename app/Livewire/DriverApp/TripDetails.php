<?php

namespace App\Livewire\DriverApp;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Trip;
use App\Models\TripStatusHistory;
use App\Models\TripStep;

use App\Enums\TripStatus;
use App\Enums\TripStepStatus;

class TripDetails extends Component
{
    public Trip $trip;
    public $steps;
    public $history;
    public ?int $odo_start_km = null;
    public ?int $odo_end_km = null;

    public bool $showOdoStart = false;
    public bool $showOdoEnd = false;

    // ID шага, на котором произошла ошибка
    public $errorStepId = null;

    public function mount(Trip $trip)
    {
        $user = Auth::user();

        if (!$user || !$user->driver) {
            return redirect()->route('driver.login');
        }

        $this->trip = $trip;
        $this->trip->load('truck');

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

   public function startTrip(): void
{
    // свежие данные
    $this->reloadTrip();

    // уже стартовали — ничего не делаем
    if ($this->trip->started_at) {
        return;
    }

    // если CAN нет — ручной ввод
    if ($this->manualOdoRequired()) {
        $this->odo_start_km = $this->trip->odo_start_km;
        $this->showOdoStart = true;
        return;
    }

    // CAN есть — попробуем взять последнее значение одометра
    $odo = $this->latestTruckOdoKm();

    // если по CAN данных нет/не пришли — fallback на ручной ввод
    if ($odo === null) {
        $this->odo_start_km = $this->trip->odo_start_km;
        $this->showOdoStart = true;
        return;
    }

    $this->trip->update([
        'started_at'   => now(),
        'status'       => TripStatus::IN_PROGRESS,
        'odo_start_km' => $odo,
    ]);

    TripStatusHistory::create([
        'trip_id'   => $this->trip->id,
        'driver_id' => Auth::user()->driver->id,
        'status'    => 'trip_started_garage',
        'time'      => now(),
        'comment'   => 'Trip uzsākts (izbrauca no garāžas)',
    ]);

    // важное: переприсвоить модель, чтобы кнопка/статус обновились в UI
    $this->reloadTrip();

    $this->dispatch('success', 'Reiss uzsākts!');
}

public function endTrip(): void
{
    $this->reloadTrip();

    if (!$this->trip->started_at) {
        $this->dispatch('error', 'Vispirms uzsāciet reisu (izbraukšana no garāžas).');
        return;
    }

    if ($this->trip->ended_at) {
        return;
    }

    // если CAN нет — ручной ввод
    if ($this->manualOdoRequired()) {
        $this->odo_end_km = $this->trip->odo_end_km;
        $this->showOdoEnd = true;
        return;
    }

    // CAN есть — берём последнее значение одометра
    $odo = $this->latestTruckOdoKm();

    // если данных нет — fallback на ручной ввод
    if ($odo === null) {
        $this->odo_end_km = $this->trip->odo_end_km;
        $this->showOdoEnd = true;
        return;
    }

    // защита от "конец меньше старта" (бывает при stale)
    if ($this->trip->odo_start_km !== null && $odo < (int) $this->trip->odo_start_km) {
        $this->odo_end_km = $this->trip->odo_end_km;
        $this->showOdoEnd = true;

        $this->dispatch('error', 'CAN odometrs ir mazāks par starta. Ievadiet beigu odometru manuāli.');
        return;
    }

    $this->trip->update([
        'ended_at'       => now(),
        'status'         => TripStatus::COMPLETED,
        'vehicle_run_id' => null,
        'odo_end_km'     => $odo,
    ]);

    TripStatusHistory::create([
        'trip_id'   => $this->trip->id,
        'driver_id' => Auth::user()->driver->id,
        'status'    => 'trip_ended_garage',
        'time'      => now(),
        'comment'   => 'Trip pabeigts (atgriezās garāžā)',
    ]);

    $this->reloadTrip();

    $this->dispatch('success', 'Reiss pabeigts!');
}

public function saveOdoEnd(): void
{
    $this->validate([
        'odo_end_km' => ['required', 'integer', 'min:0'],
    ]);

    $this->trip->refresh()->load('truck');

    if (!$this->manualOdoRequired()) {
        $this->showOdoEnd = false;
        return;
    }

    if ($this->trip->odo_start_km === null) {
        $this->dispatch('error', 'Nav starta odometra. Vispirms ievadiet starta odometru.');
        return;
    }

    $start = (int)$this->trip->odo_start_km;
    $end   = (int)$this->odo_end_km;

    if ($end < $start) {
        $this->addError('odo_end_km', 'Beigu rādījums nevar būt mazāks par starta.');
        return;
    }

    $this->trip->update([
        'ended_at'   => now(),
        'odo_end_km' => $end,
        'status'     => TripStatus::COMPLETED,
        'vehicle_run_id' => null,
    ]);

    TripStatusHistory::create([
        'trip_id'   => $this->trip->id,
        'driver_id' => Auth::user()->driver->id,
        'status'    => 'trip_ended_garage_manual_odo',
        'time'      => now(),
        'comment'   => 'Trip pabeigts + beigu odometrs ievadīts',
    ]);

    $this->showOdoEnd = false;

    $this->trip->refresh();
    $this->dispatch('success', 'Beigu odometrs saglabāts, reiss pabeigts!');
}

public function saveOdoStart(): void
{
    $this->validate([
        'odo_start_km' => ['required', 'integer', 'min:0'],
    ]);

    $this->trip->refresh()->load('truck');

    // Если вдруг CAN доступен — просто закрываем форму
    if (!$this->manualOdoRequired()) {
        $this->showOdoStart = false;
        return;
    }

    $this->trip->update([
        'started_at'   => $this->trip->started_at ?? now(),
        'odo_start_km' => (int)$this->odo_start_km,
        'status'       => TripStatus::IN_PROGRESS,
    ]);

    TripStatusHistory::create([
        'trip_id'   => $this->trip->id,
        'driver_id' => Auth::user()->driver->id,
        'status'    => 'trip_started_garage_manual_odo',
        'time'      => now(),
        'comment'   => 'Trip uzsākts + starta odometrs ievadīts',
    ]);

    $this->showOdoStart = false;

    $this->trip->refresh();
    $this->dispatch('success', 'Starta odometrs saglabāts, reiss uzsākts!');
}

    /**
     * Обновление статуса шага
     */
   public function updateStepStatus(int $stepId, int $newStatusInt): void
{
    // ✅ обновим trip + truck
    $this->trip->refresh()->load('truck');

    // 🚫 Нельзя менять шаги, пока водитель не выехал из гаража
    if (!$this->trip->started_at) {
        $this->dispatch('error', 'Vispirms uzsāciet reisu (izbraukšana no garāžas).');
        return;
    }

    // 🚫 Нельзя менять шаги после завершения рейса
    if ($this->trip->ended_at) {
        $this->dispatch('error', 'Reiss jau ir pabeigts.');
        return;
    }

    // ✅ Валидация статуса
    try {
        $newStatus = TripStepStatus::from($newStatusInt);
    } catch (\ValueError $e) {
        $this->dispatch('error', 'Nederīgs status.');
        return;
    }

    $tripId = $this->trip->id;

    DB::beginTransaction();

    try {
        // Сбрасываем ошибочный шаг
        $this->errorStepId = null;

        // Берём шаг (лучше блокируем на обновление, чтобы не было гонок)
        $step = TripStep::query()->whereKey($stepId)->lockForUpdate()->firstOrFail();

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

        // 3) История статусов (шага)
        TripStatusHistory::create([
            'trip_id'   => $tripId,
            'driver_id' => Auth::user()->driver->id,
            'status'    => "step_{$newStatus->value}",
            'time'      => now(),
            'comment'   => "Step #{$step->id} → {$newStatus->label()}",
        ]);

        // 4) Логика смены статуса рейса
        $this->updateTripStatusBasedOnSteps();

        DB::commit();

        // ✅ Обновляем отображение шагов/истории/рейса
        $this->steps = TripStep::where('trip_id', $tripId)
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        $this->history = TripStatusHistory::where('trip_id', $tripId)
            ->orderBy('time', 'desc')
            ->get();

        $this->trip->refresh()->load('truck');

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
     * Логика статуса всего рейса:
     * - IN_PROGRESS: если первый шаг уже тронут
     * - AWAITING_GARAGE: все шаги завершены, но vehicle_run_id ещё открыт (не вернулся в гараж)
     * - COMPLETED: все шаги завершены + vehicle_run_id пуст (вернулся в гараж)
     */
    private function updateTripStatusBasedOnSteps(): void
    {
        // ✅ свежий trip (vehicle_run_id мог поменяться из Dashboard)
        $trip = Trip::query()->findOrFail($this->trip->id);

        $steps = $trip->steps()->get();

        $first = $steps->first();
        $last  = $steps->last();

        // Если шагов нет — ничего не делаем
        if (!$first || !$last) {
            $this->trip = $trip;
            return;
        }

        // 1) Trip → IN_PROGRESS
        if ($first->status !== TripStepStatus::NOT_STARTED) {
            if ($trip->status !== TripStatus::IN_PROGRESS
                && $trip->status !== TripStatus::AWAITING_GARAGE
                && $trip->status !== TripStatus::COMPLETED
            ) {
                $trip->update(['status' => TripStatus::IN_PROGRESS]);

                TripStatusHistory::create([
                    'trip_id'   => $trip->id,
                    'driver_id' => Auth::user()->driver->id,
                    'status'    => 'trip_in_progress',
                    'time'      => now(),
                    'comment'   => 'Trip sākts',
                ]);
            }

            // Если был PLANNED, тоже переводим в IN_PROGRESS
            if ($trip->status === TripStatus::PLANNED) {
                $trip->update(['status' => TripStatus::IN_PROGRESS]);

                TripStatusHistory::create([
                    'trip_id'   => $trip->id,
                    'driver_id' => Auth::user()->driver->id,
                    'status'    => 'trip_in_progress',
                    'time'      => now(),
                    'comment'   => 'Trip sākts',
                ]);
            }
        }

        // 2) Если последний шаг завершён — проверяем гараж
        if ($last->status === TripStepStatus::COMPLETED) {

            $returnedToGarage = empty($trip->vehicle_run_id);

            if ($returnedToGarage) {

                if ($trip->status !== TripStatus::COMPLETED) {
                    $trip->update(['status' => TripStatus::COMPLETED]);

                    TripStatusHistory::create([
                        'trip_id'   => $trip->id,
                        'driver_id' => Auth::user()->driver->id,
                        'status'    => 'trip_completed',
                        'time'      => now(),
                        'comment'   => 'Trip pabeigts (pēc atgriešanās garāžā)',
                    ]);
                }
            } else {

                if ($trip->status !== TripStatus::AWAITING_GARAGE) {
                    $trip->update(['status' => TripStatus::AWAITING_GARAGE]);

                    TripStatusHistory::create([
                        'trip_id'   => $trip->id,
                        'driver_id' => Auth::user()->driver->id,
                        'status'    => 'trip_awaiting_garage',
                        'time'      => now(),
                        'comment'   => 'Visi soļi pabeigti — gaidām atgriešanos garāžā',
                    ]);
                }
            }
        }

        $this->trip = $trip;
    }

    private function manualOdoRequired(): bool
{
    return !($this->trip->truck?->can_available ?? false);
}
private function latestTruckOdoKm(): ?int
{
    $e = \App\Models\TruckOdometerEvent::query()
        ->where('truck_id', $this->trip->truck_id)
        ->orderByDesc('occurred_at')
        ->first();

    return $e ? (int) round($e->odometer_km) : null;
}

private function reloadTrip(): void
{
    $this->trip = Trip::query()
        ->with('truck')
        ->findOrFail($this->trip->id);
}
}
