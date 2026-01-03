<?php

namespace App\Livewire\DriverApp;

use Livewire\Component;
use Illuminate\Support\Facades\Log;
use App\Models\Trip;

class Dashboard extends Component
{
    public $driver;
    public $trip;

    public function mount()
    {
        $user = auth('driver')->user();

        Log::info('Dashboard mount()', [
            'driver_guard' => auth('driver')->check(),
            'driver_id'    => auth('driver')->id(),
            'user_id'      => $user?->id,
            'has_driver'   => (bool) $user?->driver,
            'session_id'   => session()->getId(),
        ]);

        if (!$user || !$user->driver) {
            Log::warning('Dashboard mount redirect to login', [
                'reason' => 'no user or no driver relation',
            ]);

            return redirect()->route('driver.login');
        }

        $this->driver = $user->driver;

        $this->trip = Trip::where('driver_id', $this->driver->id)
            ->where('status', '!=', 'completed')
            ->first();
    }

    public function render()
    {
        Log::info('Dashboard render()', [
            'driver_guard' => auth('driver')->check(),
            'driver_id'    => auth('driver')->id(),
            'session_id'   => session()->getId(),
        ]);

        return view('livewire.driver-app.dashboard')
            ->layout('driver-app.layouts.app', [
                'title' => 'Dashboard'
            ]);
    }
}
