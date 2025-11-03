<?php

namespace App\Livewire\Trips;

use Livewire\Component;
use App\Models\Trip;
use App\Http\Controllers\CmrController;
use App\Models\TripCargo;

class ViewTrip extends Component
{
    public Trip $trip;

   public function generateCmr(int $cargoId): void
{
    $cargo = \App\Models\TripCargo::findOrFail($cargoId);

    // Генерация и получение публичного URL
    $url = app(\App\Http\Controllers\CmrController::class)->generateAndSave($cargo);

    // Обновляем данные у рейса, чтобы сразу увидеть "View CMR"
    $this->trip->refresh();

    // 🟢 Отправляем JS-событие с URL PDF
    $this->dispatch('cmrGenerated', url: $url);
}

public function generateOrder($cargoId)
{
    $cargo = TripCargo::findOrFail($cargoId);
    $controller = app(\App\Http\Controllers\CmrController::class);
    $url = $controller->generateTransportOrder($cargo);

    $this->dispatch('orderGenerated', ['url' => $url]);
    $this->dispatch('$refresh');
}
    public function mount($trip)
    {
        $this->trip = $trip instanceof Trip
            ? $trip->load([
                'driver', 'truck', 'trailer',
                'cargos.shipper', 'cargos.consignee',
            ])
            : Trip::with([
                'driver', 'truck', 'trailer',
                'cargos.shipper', 'cargos.consignee',
            ])->findOrFail($trip);
    }

    public function render()
    {
        return view('livewire.trips.view-trip', [
            'trip' => $this->trip,
        ])->layout('layouts.app')->title('View CMR Trip');
    }
}
