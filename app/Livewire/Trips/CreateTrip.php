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

    /** === Добавить товар клиенту === */
    public function addCargoItem($cargoIndex)
    {
        $this->cargos[$cargoIndex]['items'][] = [
            'description' => '',
            'packages' => 1,
            'weight' => 0,
            'volume' => null,
            'price' => 0,
            'instructions' => '',
            'remarks' => '',
        ];

        $this->recalculateTotals(); // 🔄 сразу обновляем сумму
    }

    /** === Удалить товар === */
    public function removeCargoItem($cargoIndex, $itemIndex)
    {
        unset($this->cargos[$cargoIndex]['items'][$itemIndex]);
        $this->cargos[$cargoIndex]['items'] = array_values($this->cargos[$cargoIndex]['items']);
        $this->recalculateTotals(); // 🔄 обновляем суммы
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
            'price' => '',
            'currency' => 'EUR',
            'payment_terms' => '',
            'payer_type_id' => '',
            // 🟢 товары этого клиента
            'items' => [
                [
                    'description' => '',
                    'packages' => 1,
                    'weight' => 0,
                    'volume' => 0,
                    'price' => 0,
                    'instructions' => '',
                    'remarks' => '',
                ]
            ],
        ];
    }

    public function removeCargo($index)
    {
        unset($this->cargos[$index]);
        $this->cargos = array_values($this->cargos);
        $this->recalculateTotals();
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
            $this->cargos = array_values($this->cargos);
        }

        // === Города разгрузки ===
        if (preg_match('/^cargos\.(\d+)\.unloading_country_id$/', $property, $m)) {
            $i = (int)$m[1];
            $countryId = (int)$value;

            $this->cargos[$i]['unloading_city_id'] = null;
            $cities = getCitiesByCountryId($countryId);
            $this->cargos[$i]['unloadingCities'] = $this->formatCities($cities);
            $this->cargos = array_values($this->cargos);
        }

        // === Если изменяются товары — пересчитать суммы
        if (str_contains($property, 'items')) {
            $this->recalculateTotals();
        }
    }

    /** === Пересчет суммарной цены и веса === */
    private function recalculateTotals()
    {
        foreach ($this->cargos as $i => $cargo) {
            $items = $cargo['items'] ?? [];

            // 🧮 Общий вес, объем и стоимость по товарам
            $totalWeight = collect($items)->sum(fn($it) => (float) ($it['weight'] ?? 0));
            $totalVolume = collect($items)->sum(fn($it) => (float) ($it['volume'] ?? 0));
            $totalPrice  = collect($items)->sum(fn($it) => (float) ($it['price'] ?? 0));

            // 🟢 Обновляем поля в массиве
            $this->cargos[$i]['cargo_weight'] = round($totalWeight, 2);
            $this->cargos[$i]['cargo_volume'] = round($totalVolume, 2);
            $this->cargos[$i]['price'] = round($totalPrice, 2);
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

    // 🟢 создаём сам рейс
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
        'driver_id'           => $this->driver_id,
        'truck_id'            => $this->truck_id,
        'trailer_id'          => $this->trailer_id,
        'status'              => $this->status,
    ]);

    // 🟢 создаём связанные грузы клиентов
    foreach ($this->cargos as $cargo) {
        $trip->cargos()->create([
            'shipper_id'           => $cargo['shipper_id'] ?? null,
            'consignee_id'         => $cargo['consignee_id'] ?? null,
            'loading_country_id'   => $cargo['loading_country_id'] ?? null,
            'loading_city_id'      => $cargo['loading_city_id'] ?? null,
            'loading_address'      => $cargo['loading_address'] ?? '',
            'loading_date'         => $cargo['loading_date'] ?? null,
            'unloading_country_id' => $cargo['unloading_country_id'] ?? null,
            'unloading_city_id'    => $cargo['unloading_city_id'] ?? null,
            'unloading_address'    => $cargo['unloading_address'] ?? '',
            'unloading_date'       => $cargo['unloading_date'] ?? null,
            'cargo_description'    => $this->buildCargoDescription($cargo['items']),
            'cargo_packages'       => collect($cargo['items'])->sum('packages'),
            'cargo_weight'         => $cargo['cargo_weight'] ?? 0,
            'cargo_volume'         => $cargo['cargo_volume'] ?? 0,
            'price'                => $cargo['price'] ?? 0,
            'currency'             => $cargo['currency'] ?? 'EUR',
            'cargo_instructions'   => $cargo['cargo_instructions'] ?? '',
            'cargo_remarks'        => $cargo['cargo_remarks'] ?? '',
            'payment_terms'        => $cargo['payment_terms'] ?? null,
            'payer_type_id'        => $cargo['payer_type_id'] ?? null,
            // 🟢 сохраняем все товары клиента в JSON
            'items_json'           => json_encode($cargo['items'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ]);
    }

    $this->resetExcept('successMessage');
    $this->successMessage = '✅ Trip successfully created with detailed cargo items!';
    return redirect()->route('trips.index');
}

/** helper */
private function buildCargoDescription(array $items): string
{
    return collect($items)
        ->map(fn($it) => trim(($it['packages'] ?? 0) . ' × ' . ($it['description'] ?? '')))
        ->implode(', ');
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
            'trucks'  => $this->trucks,
            'trailers'=> $this->trailers,
        ])->layout('layouts.app');
    }
}
