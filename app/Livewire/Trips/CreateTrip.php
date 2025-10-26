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
    public $clients = [];

    // Trip
    public $status = 'planned', $successMessage;

    // Multiple cargos
    public $cargos = [];

    protected $rules = [
        'expeditor_id' => 'required',
        'driver_id'    => 'required',
        'truck_id'     => 'required',
    ];

    public function mount()
    {
        $this->addCargo();
    }

    /** === Выбор экспедитора === */
    public function updatedExpeditorId($id)
    {
        $this->expeditorData = config("companies.$id") ?? [];

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

        $this->clients = Client::orderBy('company_name')
            ->pluck('company_name', 'id')
            ->toArray();
    }

    /** === Добавление и удаление грузов === */
   public function addCargo()
{
    $this->cargos[] = [
        'shipper_id' => null,
        'consignee_id' => null,
        'shipperData' => [],
        'consigneeData' => [],
        'cargo_description' => '',
        'cargo_packages' => 1,
        'cargo_weight' => 0,
        'cargo_volume' => 0,
        'cargo_marks' => '',
        'cargo_instructions' => '',
        'cargo_remarks' => '',
        // маршрут
        'loading_country_id' => null,
        'loading_city_id' => null,
        'loadingCities' => [],
        'loading_address' => '',
        'loading_date' => '',
        'unloading_country_id' => null,
        'unloading_city_id' => null,
        'unloadingCities' => [],
        'unloading_address' => '',
        'unloading_date' => '',
        // оплата
        'price' => '',
        'currency' => 'EUR',
        'payment_terms' => '',
        'payer_type_id' => '',
    ];
}


    public function removeCargo($index)
    {
        unset($this->cargos[$index]);
        $this->cargos = array_values($this->cargos);
    }

    /** === Обновления внутри вложенного массива cargos === */
   public function updated($property, $value)
{
    // === Shipper
    if (preg_match('/^cargos\.(\d+)\.shipper_id$/', $property, $m)) {
        $i = (int)$m[1];
        $client = Client::find($value);
        $this->cargos[$i]['shipperData'] = $client ? [
            'company_name' => $client->company_name,
            'email' => $client->email,
            'phone' => $client->phone,
            'fiz_address' => $client->fiz_address,
            'fiz_city' => $client->fiz_city,
            'fiz_country' => $client->fiz_country,
        ] : [];
    }

    // === Consignee
    if (preg_match('/^cargos\.(\d+)\.consignee_id$/', $property, $m)) {
        $i = (int)$m[1];
        $client = Client::find($value);
        $this->cargos[$i]['consigneeData'] = $client ? [
            'company_name' => $client->company_name,
            'email' => $client->email,
            'phone' => $client->phone,
            'fiz_address' => $client->fiz_address,
            'fiz_city' => $client->fiz_city,
            'fiz_country' => $client->fiz_country,
        ] : [];
    }

    // === Города загрузки ===
    if (preg_match('/^cargos\.(\d+)\.loading_country_id$/', $property, $m)) {
        $i = (int)$m[1];
        $countryId = (int)$value;

        $this->cargos[$i]['loading_city_id'] = null;
        $cities = getCitiesByCountryId($countryId);

        $this->cargos[$i]['loadingCities'] = $this->formatCities($cities);

        // 🟢 Принудительно обновляем массив, чтобы Livewire отрисовал
        $this->cargos = array_values($this->cargos);
    }

    // === Города разгрузки ===
    if (preg_match('/^cargos\.(\d+)\.unloading_country_id$/', $property, $m)) {
        $i = (int)$m[1];
        $countryId = (int)$value;

        $this->cargos[$i]['unloading_city_id'] = null;
        $cities = getCitiesByCountryId($countryId);

        $this->cargos[$i]['unloadingCities'] = $this->formatCities($cities);

        // 🟢 Принудительно обновляем массив, чтобы Livewire отрисовал
        $this->cargos = array_values($this->cargos);
    }
}


    /** === Формат городов === */
    private function formatCities(array $cities): array
    {
        return collect($cities)
            ->mapWithKeys(fn($c, $id) => [$id => $c['name']])
            ->toArray();
    }

    /** === Сохранение === */
    public function save()
    {
       
        $this->validate();

        $exp = config("companies.{$this->expeditor_id}");

        $trip = Trip::create([
             'expeditor_id'        => $this->expeditor_id,
        'expeditor_name'      => $exp['name'] ?? '',
        'expeditor_reg_nr'    => $exp['reg_nr'] ?? '',
        'expeditor_country'   => $exp['country'] ?? '',
        'expeditor_city'      => $exp['city'] ?? '',
        'expeditor_address'   => $exp['address'] ?? '',
        'expeditor_post_code' => $exp['post_code'] ?? '',
        'expeditor_email'     => $exp['email'] ?? '',
        'expeditor_phone'     => $exp['phone'] ?? '',
            'driver_id'         => $this->driver_id,
            'truck_id'          => $this->truck_id,
            'trailer_id'        => $this->trailer_id,
            'status'            => $this->status,
        ]);

        // создаём связанные грузы
        foreach ($this->cargos as $cargo) {
            $trip->cargos()->create($cargo);
        }

        $this->resetExcept('successMessage');
        $this->successMessage = '✅ Trip successfully created with multiple clients!';
        return redirect()->route('trips.index');
    }

    /** === Рендер === */
    public function render()
    {
        return view('livewire.trips.create-trip', [
            'companies' => collect(config('companies'))
                ->mapWithKeys(fn($c, $id) => [$id => $c['name']])
                ->toArray(),

            'countries' => collect(config('countries'))
                ->mapWithKeys(fn($c, $id) => [$id => $c['name']])
                ->toArray(),

            'payerTypes' => collect(config('payers'))
                ->mapWithKeys(fn($p, $id) => [$id => $p['label']])
                ->toArray(),

            'clients' => $this->clients ?: Client::orderBy('company_name')
                ->pluck('company_name', 'id')
                ->toArray(),

            'drivers' => $this->drivers,
            'trucks' => $this->trucks,
            'trailers' => $this->trailers,
        ])->layout('layouts.app');
    }
}
