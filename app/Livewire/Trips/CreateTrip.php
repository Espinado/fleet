<?php

namespace App\Livewire\Trips;

use Livewire\Component;
use App\Models\{Driver, Truck, Trailer, Client, Trip};

class CreateTrip extends Component
{
    public $expeditor_id, $expeditorData = [];

    // Transport
    public $driver_id, $truck_id, $trailer_id;
    public $drivers = [], $trucks = [], $trailers = [];

    // Clients
    public $clients = [], $shipperId, $consigneeId;
    public $shipperData = [], $consigneeData = [];

    // Route
    public $origin_country_id, $origin_city_id, $origin_address, $originCities = [];
    public $destination_country_id, $destination_city_id, $destination_address, $destinationCities = [];

    // Cargo
    public $cargo_description, $cargo_packages, $cargo_weight, $cargo_volume, $cargo_marks, $cargo_instructions, $cargo_remarks;

    // Payment
    public $price, $currency = 'EUR', $payment_terms, $payer_type_id;

    // Trip
    public $start_date, $end_date, $status = 'planned', $successMessage;

    protected $rules = [
        'expeditor_id' => 'required',
        'driver_id' => 'required',
        'truck_id' => 'required',
        'shipperId' => 'required',
        'consigneeId' => 'required',
        'origin_country_id' => 'required',
        'origin_city_id' => 'required',
        'origin_address' => 'required|string|max:255',
        'destination_country_id' => 'required',
        'destination_city_id' => 'required',
        'destination_address' => 'required|string|max:255',
        'start_date' => 'required|date',
        'payment_terms' => 'nullable|date',
    ];

    /** === При выборе Expeditor === */
    public function updatedExpeditorId($id)
    {
        $this->expeditorData = config("companies.$id") ?? [];

        // фильтруем транспорт по компании
        $this->drivers = Driver::where('company', $id)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name'])
            ->mapWithKeys(fn($d) => [$d->id => "{$d->first_name} {$d->last_name}"])
            ->toArray();

        $this->trucks = Truck::where('company', $id)
            ->orderBy('brand')
            ->get(['id', 'brand', 'model', 'plate'])
            ->mapWithKeys(fn($t) => [$t->id => "{$t->brand} {$t->model} ({$t->plate})"])
            ->toArray();

        $this->trailers = Trailer::where('company', $id)
            ->orderBy('plate')
            ->get(['id', 'brand', 'plate'])
            ->mapWithKeys(fn($t) => [$t->id => "{$t->brand} ({$t->plate})"])
            ->toArray();

        // клиенты не фильтруем
        $this->clients = Client::orderBy('company_name')
            ->get(['id', 'company_name'])
            ->mapWithKeys(fn($c) => [$c->id => $c->company_name])
            ->toArray();
    }

    /** === При выборе Shipper === */
    public function updatedShipperId($id)
    {
        $client = Client::find($id);
        $this->shipperData = $client?->only(['company_name', 'email', 'phone', 'fiz_address', 'fiz_city', 'fiz_country']) ?? [];
    }

    /** === При выборе Consignee === */
    public function updatedConsigneeId($id)
    {
        $client = Client::find($id);
        $this->consigneeData = $client?->only(['company_name', 'email', 'phone', 'fiz_address', 'fiz_city', 'fiz_country']) ?? [];
    }

    /** === Города === */
    public function updatedOriginCountryId($countryId)
    {
        $this->originCities = $this->formatCities(getCitiesByCountryId((int) $countryId));
        $this->origin_city_id = null;
    }

    public function updatedDestinationCountryId($countryId)
    {
        $this->destinationCities = $this->formatCities(getCitiesByCountryId((int) $countryId));
        $this->destination_city_id = null;
    }

    private function formatCities(array $cities): array
    {
        return collect($cities)->mapWithKeys(fn($c, $id) => [$id => $c['name']])->toArray();
    }

    /** === Сохранение === */
    public function save()
    {
        $this->validate();

        $exp = config("companies.{$this->expeditor_id}");

       Trip::create([
    // === Expeditor Snapshot ===
    'expeditor_id'        => $this->expeditor_id,
    'expeditor_name'      => $exp['name'] ?? '',
    'expeditor_reg_nr'    => $exp['reg_nr'] ?? '',
    'expeditor_country'   => $exp['country'] ?? '',
    'expeditor_city'      => $exp['city'] ?? '',
    'expeditor_address'   => $exp['address'] ?? '',
    'expeditor_post_code' => $exp['post_code'] ?? '',
    'expeditor_email'     => $exp['email'] ?? '',
    'expeditor_phone'     => $exp['phone'] ?? '',

    // === Relations ===
    'driver_id'      => $this->driver_id,
    'truck_id'       => $this->truck_id,
    'trailer_id'     => $this->trailer_id,
    'shipper_id'     => $this->shipperId,
    'consignee_id'   => $this->consigneeId,

    // === Route ===
    'origin_country_id'      => $this->origin_country_id,
    'origin_city_id'         => $this->origin_city_id,
    'origin_address'         => $this->origin_address,
    'destination_country_id' => $this->destination_country_id,
    'destination_city_id'    => $this->destination_city_id,
    'destination_address'    => $this->destination_address,

    // === Cargo ===
    'cargo_description' => $this->cargo_description,
    'cargo_packages'    => $this->cargo_packages,
    'cargo_weight'      => $this->cargo_weight,
    'cargo_volume'      => $this->cargo_volume,
    'cargo_marks'       => $this->cargo_marks,
    'cargo_instructions'=> $this->cargo_instructions,
    'cargo_remarks'     => $this->cargo_remarks,

    // === Payment ===
    'price'          => $this->price,
    'currency'       => $this->currency,
    'payment_terms'  => $this->payment_terms,
    'payer_type_id'  => $this->payer_type_id,

    // === Other ===
    'start_date' => $this->start_date,
    'end_date'   => $this->end_date,
    'status'     => $this->status,
]);
        $this->resetExcept('successMessage');
        $this->successMessage = '✅ CMR Trip successfully created!';
        return redirect()->route('trips.index');
    }

    /** === Рендер === */
    public function render()
    {
        return view('livewire.trips.create-trip', [
            // ✅ исправлено — теперь обе компании отобразятся
            'companies' => collect(config('companies'))
                ->mapWithKeys(fn($c, $id) => [$id => $c['name']])
                ->toArray(),

            // страны тоже исправлены
            'countries' => collect(config('countries'))
                ->mapWithKeys(fn($c, $id) => [$id => $c['name']])
                ->toArray(),

            // типы оплат
            'payerTypes' => collect(config('payers'))
                ->mapWithKeys(fn($p, $id) => [$id => $p['label']])
                ->toArray(),

            // клиенты
            'clients' => $this->clients ?: Client::orderBy('company_name')
                ->get(['id', 'company_name'])
                ->mapWithKeys(fn($c) => [$c->id => $c->company_name])
                ->toArray(),

            'drivers' => $this->drivers,
            'trucks' => $this->trucks,
            'trailers' => $this->trailers,
            'originCities' => $this->originCities,
            'destinationCities' => $this->destinationCities,
        ])->layout('layouts.app');
    }
}
