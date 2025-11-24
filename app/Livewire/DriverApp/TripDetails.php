<?php

namespace App\Livewire\DriverApp;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Trip;
use App\Models\TripStep;
use App\Models\TripStatusHistory;
use App\Helpers\TripStepSorter;

class TripDetails extends Component
{
    public Trip $trip;
    public $steps;   // Collection
    public $history; // Collection

public function mount(Trip $trip)
{
    $user = Auth::user();

    if (!$user || !$user->driver) {
        return redirect()->route('driver.login');
    }

    $this->trip = $trip;

    // ↓ грузим шаги из базы строго по order
    $steps = TripStep::where('trip_id', $trip->id)
        ->orderBy('order')
        ->orderBy('id')
        ->get();

    // ↓ применяем ТОЧНО такую же сортировку, как у админа
    $this->steps = TripStepSorter::sort($steps);

    // история
    $this->history = TripStatusHistory::where('trip_id', $trip->id)
        ->orderBy('time')
        ->get();
}
    public function render()
    {
        return view('driver-app.pages.trip-details', [
            'trip'    => $this->trip,
            'steps'   => $this->steps,
            'history' => $this->history,
        ])->layout('driver-app.layouts.app', [
            'title' => 'Детали рейса',
        ]);
    }
}
