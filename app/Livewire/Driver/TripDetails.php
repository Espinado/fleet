<?php

namespace App\Livewire\Driver;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Trip;
use App\Models\TripStatusHistory;

class TripDetails extends Component
{
    public $trip;
    public $history;

    public function mount($trip)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'driver') {
            return redirect()->route('driver.login');
        }

        $this->trip = Trip::with([
            'truck',
            'cargos.items',
            'cargos.shipper',
            'cargos.consignee',
            'cargos.customer',
        ])->findOrFail($trip);

        $this->history = TripStatusHistory::where('trip_id', $this->trip->id)
            ->orderBy('time')
            ->get();
    }

    public function render()
    {
        return view('livewire.driver.trip-details', [
            'history' => $this->history,
        ])->layout('components.layouts.driver');
    }
}
