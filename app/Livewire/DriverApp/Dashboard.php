<?php

namespace App\Livewire\DriverApp;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

use App\Models\Trip;
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
            return redirect()->route('driver.login');
        }

        $this->driver = $user->driver;

        // ✅ Нам нужен "текущий рейс" (по бизнес-логике), даже если смена ещё не открыта
        $this->trip = Trip::where('driver_id', $this->driver->id)
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
            return;
        }

        $truck = $this->trip->truck;
        if (!$truck) {
            $this->garageError = 'В активном рейсе не найден truck.';
            return;
        }

        try {
            $svc = app(GarageDepartureService::class);

            // ✅ новая сигнатура: (Trip $trip, Truck $truck, ?int $driverId)
            $event = $svc->recordDeparture($this->trip, $truck, $this->driver->id);

            $msg = "✅ Выезд: {$event->odometer_km} км";
            if ($event->is_stale && $event->stale_minutes) {
                $msg .= " ⚠️ (данные {$event->stale_minutes} мин назад)";
            }

            $this->garageSuccess = $msg;

            // refresh trip (vehicle_run_id мог измениться)
            $this->trip->refresh();

        } catch (\Throwable $e) {
            $this->garageError = $e->getMessage();
        }

        $this->syncGarageFlags();
    }

    public function backToGarage(): void
    {
        $this->garageSuccess = null;
        $this->garageError = null;

        if (!$this->trip) {
            $this->garageError = 'Нет активного рейса.';
            return;
        }

        $truck = $this->trip->truck;
        if (!$truck) {
            $this->garageError = 'В активном рейсе не найден truck.';
            return;
        }

        try {
            $svc = app(GarageDepartureService::class);

            // ✅ новая сигнатура
            $event = $svc->recordReturn($this->trip, $truck, $this->driver->id);

            $msg = "✅ Возврат: {$event->odometer_km} км";
            if ($event->is_stale && $event->stale_minutes) {
                $msg .= " ⚠️ (данные {$event->stale_minutes} мин назад)";
            }

            $this->garageSuccess = $msg;

            // после возврата vehicle_run_id должен стать null
            $this->trip->refresh();

        } catch (\Throwable $e) {
            $this->garageError = $e->getMessage();
        }

        // на всякий случай перезагрузим текущий рейс (если он мог смениться)
        $this->trip = Trip::where('driver_id', $this->driver->id)
            ->where('status', '!=', 'completed')
            ->latest('id')
            ->first();

        $this->syncGarageFlags();
    }

    private function syncGarageFlags(): void
    {
        // по умолчанию
        $this->canDepart = false;
        $this->canReturn = false;

        if (!$this->trip || !$this->trip->truck_id) {
            return;
        }

        // ✅ главный индикатор смены: vehicle_run_id в Trip
        $runOpen = !empty($this->trip->vehicle_run_id);

        $this->canDepart = !$runOpen;
        $this->canReturn = $runOpen;

        // (дополнительно) можно свериться по событиям, но не обязательно:
        // $last = TruckOdometerEvent::where('truck_id', $this->trip->truck_id)->latest('occurred_at')->first();
        // $runOpen = $last && (int)$last->type === TruckOdometerEvent::TYPE_DEPARTURE;
    }

    public function render()
    {
        return view('livewire.driver-app.dashboard')
            ->layout('driver-app.layouts.app', [
                'title' => 'Dashboard',
            ]);
    }
}
