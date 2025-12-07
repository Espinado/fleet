<?php

namespace App\Livewire\DriverApp;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Trip;

class Dashboard extends Component
{
    public $driver;
    public $trip;

    public function mount()
    {

        $user = Auth::user();

        if (!$user || $user->role !== 'driver' || !$user->driver) {
            return redirect()->route('driver.login');
        }

        $this->driver = $user->driver;

        $this->trip = Trip::where('driver_id', $this->driver->id)
            ->where('status', '!=', 'completed')
            ->first();
    }

    public function render()
    {
       
     
   return view('livewire.driver-app.dashboard')
        ->layout('driver-app.layouts.app', [
            'title' => 'Dashboard'
        ]);
    }
}
