<?php

namespace App\Livewire\DriverApp;

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

        if (!$user || !$user->driver) {
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
       return view('driver-app.pages.trip-details', [
            'history' => $this->history,
        ])->layout('driver-app.layouts.app', [
            'title' => 'Детали рейса'
        ]);
    }
}
