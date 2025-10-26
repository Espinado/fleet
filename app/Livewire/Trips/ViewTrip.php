<?php

namespace App\Livewire\Trips;

use Livewire\Component;
use App\Models\Trip;

class ViewTrip extends Component
{
    public Trip $trip;

    public function mount($trip)
    {
        // Поддержка передачи ID или модели
        $this->trip = $trip instanceof Trip
            ? $trip->load([
                'driver',
                'truck',
                'trailer',
                'cargos.shipper',
                'cargos.consignee',
            ])
            : Trip::with([
                'driver',
                'truck',
                'trailer',
                'cargos.shipper',
                'cargos.consignee',
            ])->findOrFail($trip);
    }

    public function render()
{
    // Если у рейса есть хотя бы один груз — берём первый
    if ($this->trip->cargos->isNotEmpty()) {
        $cargo = $this->trip->cargos->first();

        // dd([
        //     'loading_country_id' => $cargo->loading_country_id,
        //     'country_return' => getCountryById((int) $cargo->loading_country_id),
        //     'unloading_country_id' => $cargo->unloading_country_id,
        //     'unloading_return' => getCountryById((int) $cargo->unloading_country_id),
        //     'loading_city_id' => $cargo->loading_city_id,
        //     'city_return' => getCityById((int) $cargo->loading_city_id),
        // ]);
    }

    return view('livewire.trips.view-trip', [
        'trip' => $this->trip,
    ])->layout('layouts.app')->title('View CMR Trip');
}
}
