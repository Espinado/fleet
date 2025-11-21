<?php

namespace App\Livewire\Driver;

use Livewire\Component;
use App\Models\Trip;

class TripHistory extends Component
{
    public Trip $trip;

    public function mount(Trip $trip)
    {
        $this->trip = $trip;
    }

    public function render()
    {
        return view('livewire.driver.trip-history', [
            'history' => $this->trip->history()->orderBy('time', 'asc')->get(),
        ]);
    }
}
