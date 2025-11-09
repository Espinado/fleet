<?php

namespace App\Livewire\Trips;

use Livewire\Component;
use App\Models\Trip;
use App\Http\Controllers\CmrController;
use App\Models\TripCargo;

class ViewTrip extends Component
{
    public Trip $trip;

    public function mount($trip)
    {
        $this->trip = $trip instanceof Trip
            ? $trip->load([
                'driver', 'truck', 'trailer',
                'cargos.shipper', 'cargos.consignee', 'cargos.customer',
                'cargos.items', // ✅ добавлено
            ])
            : Trip::with([
                'driver', 'truck', 'trailer',
                'cargos.shipper', 'cargos.consignee', 'cargos.customer',
                'cargos.items', // ✅ добавлено
            ])->findOrFail($trip);
    }

    public function generateCmr(int $cargoId): void
    {
        $cargo = TripCargo::findOrFail($cargoId);
        $url = app(CmrController::class)->generateAndSave($cargo);

        $this->trip->load([
            'driver', 'truck', 'trailer',
            'cargos.shipper', 'cargos.consignee', 'cargos.customer',
            'cargos.items', // ✅ добавлено
        ]);

        $this->dispatch('cmrGenerated', url: $url);
    }

    public function generateOrder(int $cargoId)
    {
        $cargo = TripCargo::findOrFail($cargoId);
        $url = app(CmrController::class)->generateTransportOrder($cargo);

        $this->trip->load([
            'driver', 'truck', 'trailer',
            'cargos.shipper', 'cargos.consignee', 'cargos.customer',
            'cargos.items', // ✅ добавлено
        ]);

        $this->dispatch('orderGenerated', ['url' => $url]);
    }

    public function generateInvoice(int $cargoId)
    {
        $cargo = TripCargo::findOrFail($cargoId);
        $url = app(CmrController::class)->generateInvoice($cargo);

        $this->trip->load([
            'driver', 'truck', 'trailer',
            'cargos.shipper', 'cargos.consignee', 'cargos.customer',
            'cargos.items', // ✅ добавлено
        ]);

        $this->dispatch('invoiceGenerated', ['url' => $url]);
    }

    public function render()
    {
        return view('livewire.trips.view-trip', [
            'trip' => $this->trip,
        ])->layout('layouts.app')->title('View CMR Trip');
    }
}
