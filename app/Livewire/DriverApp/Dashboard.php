<?php

namespace App\Livewire\DriverApp;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

use App\Models\Trip;
use App\Models\VehicleRun;
use App\Models\TruckOdometerEvent;
use App\Services\Services\Odometer\GarageDepartureService;

use App\Enums\TripStatus;

class Dashboard extends Component
{
    public $driver;
    public $trip;

    public ?string $garageSuccess = null;
    public ?string $garageError = null;

    public bool $canDepart = true;
    public bool $canReturn = false;

    public function mount()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'driver' || !$user->driver) {
            redirect()->route('driver.login')->send();
            return;
        }

        $this->driver = $user->driver;

        $this->loadCurrentTrip();

        $this->syncGarageFlags();
    }

    private function loadCurrentTrip(): void
    {
        $this->trip = Trip::query()
            ->where('driver_id', $this->driver->id)
            ->where('status', '!=', TripStatus::COMPLETED->value)
            ->latest('id')
            ->first();
    }

    public function departFromGarage(): void
    {
        $this->garageSuccess = null;
        $this->garageError = null;

        if (!$this->trip) {
            $this->garageError = 'Нет активного рейса.';
            $this->syncGarageFlags();
            return;
        }

        $truck = $this->trip->truck;
        if (!$truck) {
            $this->garageError = 'В активном рейсе не найден truck.';
            $this->syncGarageFlags();
            return;
        }

        try {
            /** @var GarageDepartureService $svc */
            $svc = app(GarageDepartureService::class);

            $event = $svc->recordDeparture($this->trip, $truck, $this->driver->id);

            $msg = "✅ Выезд: {$event->odometer_km} км";
            if ($event->is_stale && $event->stale_minutes) {
                $msg .= " ⚠️ (данные {$event->stale_minutes} мин назад)";
            }

            $this->garageSuccess = $msg;

        } catch (\Throwable $e) {
            $this->garageError = $e->getMessage();
        }

        // ✅ берём актуальный рейс/статус/vehicle_run_id
        $this->loadCurrentTrip();
        $this->syncGarageFlags();
    }

    public function backToGarage(): void
    {
        $this->garageSuccess = null;
        $this->garageError = null;

        if (!$this->trip) {
            $this->garageError = 'Нет активного рейса.';
            $this->syncGarageFlags();
            return;
        }

        $truck = $this->trip->truck;
        if (!$truck) {
            $this->garageError = 'В активном рейсе не найден truck.';
            $this->syncGarageFlags();
            return;
        }

        try {
            /** @var GarageDepartureService $svc */
            $svc = app(GarageDepartureService::class);

            $event = $svc->recordReturn($this->trip, $truck, $this->driver->id);

            $msg = "✅ Возврат: {$event->odometer_km} км";
            if ($event->is_stale && $event->stale_minutes) {
                $msg .= " ⚠️ (данные {$event->stale_minutes} мин назад)";
            }

            $this->garageSuccess = $msg;

        } catch (\Throwable $e) {
            $this->garageError = $e->getMessage();
        }

        // ✅ после возврата рейс мог стать COMPLETED (если все шаги закрыты)
        $this->loadCurrentTrip();
        $this->syncGarageFlags();
    }

    private function syncGarageFlags(): void
    {
        // дефолты
        $this->canDepart = false;
        $this->canReturn = false;

        if (!$this->trip || !$this->trip->truck_id) {
            return;
        }

        // если рейс вдруг completed (на всякий) — блокируем всё
        if ($this->trip->status instanceof TripStatus && $this->trip->status === TripStatus::COMPLETED) {
            return;
        }

        $truckId = (int) $this->trip->truck_id;

        // 1) Основной индикатор — vehicle_run_id в Trip
        $runOpenByTrip = !empty($this->trip->vehicle_run_id);

        // 2) Fallback по событиям одометра (если Trip не привязан)
        $lastEvent = TruckOdometerEvent::query()
            ->where('truck_id', $truckId)
            ->latest('occurred_at')
            ->first();

        $runOpenByEvents = $lastEvent && (int) $lastEvent->type === TruckOdometerEvent::TYPE_DEPARTURE;

        // 3) Fallback по VehicleRun (если есть открытая смена)
        $openRun = VehicleRun::query()
            ->where('truck_id', $truckId)
            ->where('status', 'open')
            ->latest('id')
            ->first();

        $runOpenByRuns = (bool) $openRun;

        $isOpen = $runOpenByTrip || $runOpenByEvents || $runOpenByRuns;

        $this->canDepart = !$isOpen;
        $this->canReturn = $isOpen;

        // 4) Автовосстановление: если есть openRun, но Trip не привязан — привяжем
        if (!$runOpenByTrip && $openRun) {
            $this->trip->forceFill(['vehicle_run_id' => $openRun->id])->save();
            $this->trip = Trip::query()->find($this->trip->id);

            $this->canDepart = false;
            $this->canReturn = true;
        }
    }

    public function render()
    {
        return view('livewire.driver-app.dashboard')
            ->layout('driver-app.layouts.app', [
                'title' => 'Dashboard',
            ]);
    }
}
