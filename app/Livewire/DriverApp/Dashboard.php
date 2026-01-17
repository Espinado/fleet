<?php

namespace App\Livewire\DriverApp;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Trip;
use App\Services\Services\Odometer\GarageDepartureService;
use App\Models\TruckOdometerEvent;

class Dashboard extends Component
{
    public $driver;
    public $trip;
    public ?string $garageSuccess = null;
public ?string $garageError = null;
public bool $canDepart = true;
public bool $canReturn = false;


public function departFromGarage(): void
{
    $this->garageSuccess = null;
    $this->garageError = null;

    if (!$this->trip) {
        $this->garageError = 'Нет активного рейса (end_date пустой).';
        return;
    }

    $truck = $this->trip->truck;
    if (!$truck) {
        $this->garageError = 'В активном рейсе не найден truck.';
        return;
    }

    try {
        $svc = app(GarageDepartureService::class);
        $event = $svc->recordDeparture($truck, $this->driver->id);

        $msg = "✅ Выезд: {$event->odometer_km} км";
        if ($event->is_stale && $event->stale_minutes) {
            $msg .= " ⚠️ (данные {$event->stale_minutes} мин назад)";
        }

        $this->garageSuccess = $msg;
    } catch (\Throwable $e) {
        $this->garageError = $e->getMessage();
    }
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
        $svc = app(\App\Services\Services\Odometer\GarageDepartureService::class);
        $event = $svc->recordReturn($truck, $this->driver->id);

        $msg = "✅ Возврат: {$event->odometer_km} км";
        if ($event->is_stale && $event->stale_minutes) {
            $msg .= " ⚠️ (данные {$event->stale_minutes} мин назад)";
        }

        $this->garageSuccess = $msg;

        // обновляем флаги UI
        $this->canDepart = true;
        $this->canReturn = false;

    } catch (\Throwable $e) {
        $this->garageError = $e->getMessage();
    }
}
    public function mount()
    {

        $user = Auth::user();

        if (!$user || $user->role !== 'driver' || !$user->driver) {
            return redirect()->route('driver.login');
        }

        $this->driver = $user->driver;

        $this->trip = Trip::where('driver_id', $this->driver->id)
    ->whereNull('end_date')
    ->latest('id')
    ->first();

    if ($this->trip && $this->trip->truck_id) {
    $last = TruckOdometerEvent::where('truck_id', $this->trip->truck_id)
        ->latest('occurred_at')
        ->first();

    $open = $last && (int) $last->type === TruckOdometerEvent::TYPE_DEPARTURE;

    $this->canDepart = !$open;
    $this->canReturn = $open;
}

    }

    public function render()
    {
       
     
   return view('livewire.driver-app.dashboard')
        ->layout('driver-app.layouts.app', [
            'title' => 'Dashboard'
        ]);
    }
}
