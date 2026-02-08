<?php

namespace App\Livewire\DriverApp;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

use App\Models\Trip;
use App\Models\VehicleRun;
use App\Models\TruckOdometerEvent;
use App\Services\Services\Odometer\GarageDepartureService;

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

        $this->trip = Trip::query()
            ->where('driver_id', $this->driver->id)
            ->where('status', '!=', 'completed')
            ->latest('id')
            ->first();

        $this->syncGarageFlags();
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

            // ✅ надежно перезагружаем trip из БД (vehicle_run_id мог измениться)
            $this->trip = Trip::query()->find($this->trip->id);

        } catch (\Throwable $e) {
            $this->garageError = $e->getMessage();
            // тоже перезагрузим на всякий случай
            $this->trip = $this->trip?->id ? Trip::query()->find($this->trip->id) : $this->trip;
        }

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

        // ✅ на всякий случай заново берем текущий рейс
        $this->trip = Trip::query()
            ->where('driver_id', $this->driver->id)
            ->where('status', '!=', 'completed')
            ->latest('id')
            ->first();

        $this->syncGarageFlags();
    }

    private function syncGarageFlags(): void
    {
        $this->canDepart = false;
        $this->canReturn = false;

        if (!$this->trip || !$this->trip->truck_id) {
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
