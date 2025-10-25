<?php

namespace App\View\Components;

use Illuminate\View\Component;

class TripLocation extends Component
{
    public string $countryId;
    public string $cityId;
    public string $address;

    public function __construct(string $countryId, string $cityId, string $address)
    {
        $this->countryId = $countryId;
        $this->cityId = $cityId;
        $this->address = $address;
    }

    public function render()
    {
        return view('components.trip-location');
    }
}
