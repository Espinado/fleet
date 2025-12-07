<?php

namespace App\Livewire\DriverApp;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Trip;
use App\Models\TripStatusHistory;
use App\Models\TripStep;

class TripDetails extends Component
{
    public Trip $trip;
    public $steps;      // отсортированные шаги
    public $history;    // история статусов

    public function mount(Trip $trip)
    {
        $user = Auth::user();

        // защита
        if (!$user || !$user->driver) {
            return redirect()->route('driver.login');
        }

        $this->trip = $trip;

        // грузим шаги
        $this->steps = TripStep::where('trip_id', $trip->id)
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        // история
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
}
