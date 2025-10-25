<?php

namespace App\Livewire\Trips;

use Livewire\Component;
use App\Models\Trip;

class ViewTrip extends Component
{
    public Trip $trip;

    public function mount($trip)
    {
        // Маршрут может передать или id, или уже модель — поддержим оба случая
        if ($trip instanceof Trip) {
            $this->trip = $trip->load(['driver','truck','trailer','shipper','consignee']);
        } else {
            $this->trip = Trip::with(['driver','truck','trailer','shipper','consignee'])->findOrFail($trip);
        }
    }

    public function render()
    {
        return view('livewire.trips.view-trip', [
            'trip' => $this->trip,   // 👈 передаём явно
        ])->layout('layouts.app')->title('View CMR Trip');
    }
}
