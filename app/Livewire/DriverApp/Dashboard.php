<?php

namespace App\Livewire\DriverApp;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Trip;
use App\Models\VehicleRun;
use App\Models\TruckOdometerEvent;

use App\Enums\TripStatus;
use App\Enums\TripStepStatus;

class Dashboard extends Component
{
    public $driver;
    public $trip;

    public ?string $garageSuccess = null;
    public ?string $garageError = null;

    public bool $canDepart = true;
    public bool $canReturn = false;

    // ✅ Manual odometer modal
    public bool $showManualOdo = false;
    public string $manualOdoMode = 'departure'; // departure|return
    public ?int $manualOdoKm = null;

    public function mount()
    {
        $userWeb    = Auth::guard('web')->user();
        $userDriver = Auth::guard('driver')->user();

        \Log::info('DriverApp mount AUTH', [
            'url' => request()->fullUrl(),
            'session_id' => session()->getId(),
            'web_user_id' => optional($userWeb)->id,
            'driver_user_id' => optional($userDriver)->id,
            'driver_role' => optional($userDriver)->role,
            'driver_model_id' => optional($userDriver?->driver)->id,
        ]);

        $user = $userDriver; // ✅ driver-guard

        if (!$user || $user->role !== 'driver' || !$user->driver) {
            \Log::warning('DriverApp mount BLOCKED', [
                'reason' => 'not authed as driver or no driver relation',
                'driver_user_id' => optional($userDriver)->id,
                'driver_model_id' => optional($userDriver?->driver)->id,
            ]);

            redirect()->route('driver.login')->send();
            return;
        }

        $this->driver = $user->driver;

        $this->loadCurrentTrip();
        $this->syncGarageFlags();
    }

    private function loadCurrentTrip(): void
    {
        \Log::info('DriverApp loadCurrentTrip BEFORE', [
            'driver_id' => optional($this->driver)->id,
            'completed_value' => TripStatus::COMPLETED->value,
        ]);

        // 1) В первую очередь ищем реально "идущий" рейс:
        //    IN_PROGRESS или AWAITING_GARAGE.
        $trip = Trip::withoutGlobalScopes()
            ->where('driver_id', $this->driver->id)
            ->whereIn('status', [
                TripStatus::IN_PROGRESS->value,
                TripStatus::AWAITING_GARAGE->value,
            ])
            ->latest('id')
            ->first();

        // 2) Завершённый рейс никогда не показываем как активный — только в пути или ожидание гаража
        if ($trip && $trip->status instanceof TripStatus && $trip->status === TripStatus::COMPLETED) {
            $trip = null;
        }

        // 3) Если активного рейса нет, показываем ближайший PLANNED
        if (!$trip) {
            $trip = Trip::withoutGlobalScopes()
                ->where('driver_id', $this->driver->id)
                ->where('status', TripStatus::PLANNED->value)
                ->latest('id')
                ->first();
        }

        $this->trip = $trip;

        \Log::info('DriverApp loadCurrentTrip AFTER', [
            'trip_id' => optional($this->trip)->id,
            'trip_status' => $this->trip?->status instanceof TripStatus
                ? $this->trip->status->value
                : $this->trip?->status,
            'trip_driver_id' => optional($this->trip)->driver_id,
        ]);
    }

    /** ============================================================
     *  ✅ MANUAL ODO FLOW (Dashboard only)
     * ============================================================ */

    public function departFromGarage(): void
    {
        $this->garageSuccess = null;
        $this->garageError = null;

        if (!$this->trip) {
            $this->garageError = 'Нет активного рейса.';
            $this->dispatch('driver-toast-error');
            $this->syncGarageFlags();
            return;
        }

        if (!$this->trip->truck) {
            $this->garageError = 'В активном рейсе не найден truck.';
            $this->dispatch('driver-toast-error');
            $this->syncGarageFlags();
            return;
        }

        $this->manualOdoMode = 'departure';
        $this->manualOdoKm = $this->trip->odo_start_km ? (int) $this->trip->odo_start_km : null;

        $this->resetErrorBag('manualOdoKm');
        $this->showManualOdo = true;
    }

    public function backToGarage(): void
    {
        $this->garageSuccess = null;
        $this->garageError = null;

        if (!$this->trip) {
            $this->garageError = 'Нет активного рейса.';
            $this->dispatch('driver-toast-error');
            $this->syncGarageFlags();
            return;
        }

        if (!$this->trip->truck) {
            $this->garageError = 'В активном рейсе не найден truck.';
            $this->dispatch('driver-toast-error');
            $this->syncGarageFlags();
            return;
        }

        $this->manualOdoMode = 'return';
        $this->manualOdoKm = $this->trip->odo_end_km ? (int) $this->trip->odo_end_km : null;

        $this->resetErrorBag('manualOdoKm');
        $this->showManualOdo = true;
    }

    public function cancelManualOdo(): void
    {
        $this->showManualOdo = false;
        $this->manualOdoMode = 'departure';
        $this->manualOdoKm = null;
        $this->resetErrorBag('manualOdoKm');
    }

    public function saveManualOdo(): void
    {
        $this->validate([
            'manualOdoKm' => ['required', 'integer', 'min:0'],
        ]);

        if (!$this->trip || !$this->trip->truck_id) {
            $this->garageError = 'Нет активного рейса.';
            $this->dispatch('driver-toast-error');
            $this->cancelManualOdo();
            $this->syncGarageFlags();
            return;
        }

        $odo = (int) $this->manualOdoKm;

        // Единственное условие: при возврате в гараж показание не может быть меньше, чем при выезде
        if ($this->manualOdoMode === 'return' && $this->trip->odo_start_km !== null && $odo < (int) $this->trip->odo_start_km) {
            $this->addError('manualOdoKm', __('app.driver.odo.return_not_less_than_start'));
            return;
        }

        try {
            DB::transaction(function () use ($odo) {

                /** @var Trip $trip */
                $trip = Trip::query()->lockForUpdate()->findOrFail($this->trip->id);
                $truckId = (int) $trip->truck_id;

                // open VehicleRun по truck
                $openRun = VehicleRun::query()
                    ->where('truck_id', $truckId)
                    ->where('status', 'open')
                    ->latest('id')
                    ->first();

                if ($this->manualOdoMode === 'departure') {

                    // если уже стартовали — не дублируем
                    if ($trip->started_at) {
                        return;
                    }

                    // 1) VehicleRun OPEN
                    if (!$openRun) {
                        $openRun = VehicleRun::create([
                            'truck_id'          => $truckId,
                            'driver_id'         => $this->driver->id,
                            'started_at'        => now(),
                            'status'            => 'open',
                            'created_by'        => 'manual',
                            'start_can_odom_km' => $odo, // кладём ручной в start_can_odom_km
                        ]);
                    } else {
                        // подстрахуемся (если есть openRun, но без старта)
                        $openRun->update([
                            'driver_id'         => $openRun->driver_id ?? $this->driver->id,
                            'started_at'        => $openRun->started_at ?? now(),
                            'created_by'        => $openRun->created_by ?? 'manual',
                            'start_can_odom_km' => $openRun->start_can_odom_km ?? $odo,
                        ]);
                    }

                    // 2) Trip старт
                    $trip->update([
                        'vehicle_run_id' => $openRun->id,
                        'started_at'     => now(),
                        'status'         => TripStatus::IN_PROGRESS,
                        'odo_start_km'   => $odo,
                    ]);

                    // 3) Odometer event departure (привязываем к trip_id, чтобы видеть в Stats)
                    TruckOdometerEvent::create([
                        'truck_id'      => $truckId,
                        'driver_id'     => $this->driver->id,
                        'trip_id'       => $trip->id,
                        'type'          => TruckOdometerEvent::TYPE_DEPARTURE,
                        'odometer_km'   => $odo,
                        'source'        => TruckOdometerEvent::SOURCE_MANUAL,
                        'occurred_at'   => now(),
                        'mapon_at'      => null,
                        'is_stale'      => false,
                        'stale_minutes' => null,
                        'raw'           => null,
                        'note'          => "Trip #{$trip->id} departure (manual)",
                    ]);

                } else {
                    // ===== RETURN (проверка уже выполнена до транзакции) =====

                    // 1) VehicleRun CLOSE (по trip->vehicle_run_id, иначе fallback на openRun)
                    $runToClose = null;

                    if (!empty($trip->vehicle_run_id)) {
                        $runToClose = VehicleRun::query()->find($trip->vehicle_run_id);
                    }
                    if (!$runToClose) {
                        $runToClose = $openRun;
                    }

                    if ($runToClose && $runToClose->status === 'open') {
                        $runToClose->update([
                            'ended_at'        => now(),
                            'end_can_odom_km' => $odo,
                            'status'          => 'closed',
                            'close_reason'    => 'manual_return',
                        ]);
                    }

                    // 2) Trip: end odo + vehicle_run_id null
                    $updateTrip = [
                        'odo_end_km'     => $odo,
                        'vehicle_run_id' => null,
                    ];

                    // ✅ если все шаги завершены — закрываем рейс в COMPLETED прямо сейчас
                    $allStepsDone = $trip->steps()
                        ->where('status', '!=', TripStepStatus::COMPLETED->value)
                        ->doesntExist();

                    if ($allStepsDone) {
                        $updateTrip['ended_at'] = now();
                        $updateTrip['status']   = TripStatus::COMPLETED;
                    }

                    $trip->update($updateTrip);

                    // 3) Odometer event return (привязываем к trip_id, чтобы видеть в Stats)
                    TruckOdometerEvent::create([
                        'truck_id'      => $truckId,
                        'driver_id'     => $this->driver->id,
                        'trip_id'       => $trip->id,
                        'type'          => TruckOdometerEvent::TYPE_RETURN,
                        'odometer_km'   => $odo,
                        'source'        => TruckOdometerEvent::SOURCE_MANUAL,
                        'occurred_at'   => now(),
                        'mapon_at'      => null,
                        'is_stale'      => false,
                        'stale_minutes' => null,
                        'raw'           => null,
                        'note'          => "Trip #{$trip->id} return (manual)",
                    ]);
                }
            });

        } catch (\Throwable $e) {
            // если ошибка по полю — модалка остаётся открытой
            if ($this->getErrorBag()->has('manualOdoKm')) {
                return;
            }

            report($e);
            $this->garageError = 'Neizdevās saglabāt odometru. Sazinieties ar dispečeru.';
            $this->dispatch('driver-toast-error');
            return;
        }

        $this->garageSuccess = $this->manualOdoMode === 'departure'
            ? "✅ Выезд (вручную): {$odo} км"
            : "✅ Возврат (вручную): {$odo} км";

        $this->dispatch('driver-toast-success');

        // Push: уведомить одного получателя (email из config) о выезде из гаража
        if ($this->manualOdoMode === 'departure') {
            $this->trip->refresh()->load('truck');
            $email = config('notifications.push_recipient_email');
            if ($email) {
                $recipient = \App\Models\User::where('email', $email)->whereHas('pushSubscriptions')->first();
                if ($recipient) {
                    $recipient->notify(new \App\Notifications\DriverDepartureNotification($this->trip, $this->driver));
                }
            }
        }

        $this->cancelManualOdo();

        $this->loadCurrentTrip();
        $this->syncGarageFlags();
    }

    private function syncGarageFlags(): void
    {
        $this->canDepart = false;
        $this->canReturn = false;

        if (!$this->trip || !$this->trip->truck_id) {
            return;
        }

        // Рейс завершён — обе кнопки неактивны
        if ($this->trip->status instanceof TripStatus && $this->trip->status === TripStatus::COMPLETED) {
            return;
        }

        // Приоритет: по полям рейса (started_at / ended_at), чтобы кнопки не зависели от vehicle_run_id
        $hasStarted = !empty($this->trip->started_at);
        $hasEnded   = !empty($this->trip->ended_at);

        if ($hasStarted && !$hasEnded) {
            // Водитель выехал, ещё не вернулся — только "Возврат в гараж"
            $this->canDepart = false;
            $this->canReturn = true;
            return;
        }

        if (!$hasStarted) {
            // Ещё не выезжал — только "Выезд из гаража"
            $this->canDepart = true;
            $this->canReturn = false;
            return;
        }

        // hasStarted && hasEnded при не COMPLETED — маловероятно; оставляем обе неактивными
    }

    public function render()
    {
        return view('livewire.driver-app.dashboard')
            ->layout('driver-app.layouts.app', [
                'title' => 'Dashboard',
            ]);
    }
}
