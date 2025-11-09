<?php

namespace App\Livewire\Trips;

use Livewire\Component;
use App\Models\{Driver, Truck, Trailer, Client, Trip};
use App\Helpers\CalculateTax;

class CreateTrip extends Component
{
    public $expeditor_id, $expeditorData = [];

    // Транспорт
    public $driver_id, $truck_id, $trailer_id;
    public $drivers = [], $trucks = [], $trailers = [];

    // Клиенты
    public $clients = [];
    public $customers = [];

    // Рейс
    public $status = 'planned', $successMessage;

    // Грузы
    public $cargos = [];

    protected $rules = [
       'expeditor_id' => 'required|integer',
        'driver_id'    => 'required|integer',
        'truck_id'     => 'required|integer',
        'trailer_id'   => 'nullable|integer',
        'status'       => 'required|string',

        'cargos.*.customer_id'          => 'required|integer',
        'cargos.*.shipper_id'           => 'required|integer',
        'cargos.*.consignee_id'         => 'required|integer',
        'cargos.*.loading_country_id'   => 'required|integer',
        'cargos.*.loading_city_id'      => 'required|integer',
        'cargos.*.loading_address'      => 'required|string|min:3',
        'cargos.*.loading_date'         => 'required|date',
        'cargos.*.unloading_country_id' => 'required|integer',
        'cargos.*.unloading_city_id'    => 'required|integer',
        'cargos.*.unloading_address'    => 'required|string|min:3',
        'cargos.*.unloading_date'       => 'required|date',
        'cargos.*.payment_terms'        => 'required|date',
        'cargos.*.payer_type_id'        => 'required|integer',
        'cargos.*.tax_percent'          => 'required|numeric|min:0',

        'cargos.*.items.*.description'    => 'required|string|min:2',
        'cargos.*.items.*.packages'       => 'required|numeric|min:1',
        'cargos.*.items.*.weight'         => 'required|numeric|min:0',
        'cargos.*.items.*.price_with_tax' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        $this->addCargo();
    }

    /** === Добавить новый груз === */
    public function addCargo()
    {
        $this->cargos[] = [
            'shipper_id'           => null,
            'consignee_id'         => null,
            'customer_id'          => null,
            'shipperData'          => [],
            'consigneeData'        => [],
            'customerData'         => [],
            'cargo_description'    => '',
            'cargo_packages'       => 1,
            'cargo_weight'         => 0,
            'cargo_volume'         => 0,
            'cargo_marks'          => '',
            'cargo_instructions'   => '',
            'cargo_remarks'        => '',
            'loading_country_id'   => null,
            'loading_city_id'      => null,
            'loadingCities'        => [],
            'loading_address'      => '',
            'loading_date'         => '',
            'unloading_country_id' => null,
            'unloading_city_id'    => null,
            'unloadingCities'      => [],
            'unloading_address'    => '',
            'unloading_date'       => '',
            'price'                => 0,
            'total_tax_amount'     => 0,
            'price_with_tax'       => 0,
            'price_with_tax'       => 0,
           'tax_percent'          => null, // 👈 добавили
            'currency'             => 'EUR',
            'payment_terms'        => '',
            'payer_type_id'        => '',
            'items' => [
                [
                    'description'        => '',
                    'packages'           => 1,
                    'cargo_paletes'      => 0,
                    'cargo_tonnes'       => 0,
                    'weight'             => 0,
                    'cargo_netto_weight' => 0,
                    'volume'             => 0,
                    'price'              => 0,
                    'tax_percent'        => 0,
                    'tax_amount'         => 0,
                    'price_with_tax'     => 0,
                    'instructions'       => '',
                    'remarks'            => '',
                ],
            ],
        ];
    }

    /** === Удалить груз === */
    public function removeCargo($index)
    {
        unset($this->cargos[$index]);
        $this->cargos = array_values($this->cargos);
        $this->recalculateTotals();
    }

    /** === Добавить товар (позицию) в груз === */
    public function addCargoItem($cargoIndex)
    {
        $this->cargos[$cargoIndex]['items'][] = [
            'description'        => '',
            'packages'           => 1,
            'cargo_paletes'      => 0,
            'cargo_tonnes'       => 0,
            'weight'             => 0,
            'cargo_netto_weight' => 0,
            'volume'             => 0,
            'price'              => 0,
            'tax_percent'        => 0,
            'tax_amount'         => 0,
            'price_with_tax'     => 0,
            'instructions'       => '',
            'remarks'            => '',
        ];

        $this->recalculateTotals();
    }

    /** === Удалить товар === */
    public function removeCargoItem($cargoIndex, $itemIndex)
    {
        unset($this->cargos[$cargoIndex]['items'][$itemIndex]);
        $this->cargos[$cargoIndex]['items'] = array_values($this->cargos[$cargoIndex]['items']);
        $this->recalculateTotals();
    }

    /** === Пересчёт итогов (через CalculateTax) === */
    private function recalculateTotals()
    {
      foreach ($this->cargos as $i => &$cargo) {

        $taxPercent = (float)($cargo['tax_percent'] ?? 0);
        $priceWithTax = (float)($cargo['price_with_tax'] ?? 0);

        if ($taxPercent > 0) {
            // Цена без налога
            $price = $priceWithTax / (1 + $taxPercent / 100);
            // Сумма налога
            $taxAmount = $priceWithTax - $price;
        } else {
            $price = $priceWithTax;
            $taxAmount = 0;
        }

        $cargo['price'] = round($price, 2);
        $cargo['total_tax_amount'] = round($taxAmount, 2);
        $cargo['price_with_tax'] = round($priceWithTax, 2);
    }
    }

    /** === Реакция на выбор страны === */
    public function updated($property, $value)
    {
        // === Страны ===
        if (preg_match('/^cargos\.(\d+)\.(loading|unloading)_country_id$/', $property, $m)) {
            $i = (int)$m[1];
            $type = $m[2];
            $this->cargos[$i]["{$type}_city_id"] = null;
            $cities = getCitiesByCountryId((int)$value);
            $this->cargos[$i]["{$type}Cities"] = $this->formatCities($cities);
        }

        // === Клиенты (Customer / Shipper / Consignee) ===
        if (preg_match('/^cargos\.(\d+)\.(customer_id|shipper_id|consignee_id)$/', $property, $m)) {
            $i = (int)$m[1];
            $field = str_replace('_id', '', $m[2]);
            $client = Client::find($value);
            $this->cargos[$i]["{$field}Data"] = $client ? $client->toArray() : [];
        }

        // === Пересчёт при изменении позиций ===
        if (str_contains($property, 'items')) {
            $this->recalculateTotals();
        }
        if (preg_match('/^cargos\.(\d+)\.(price_with_tax|tax_percent)$/', $property)) {
    $this->recalculateTotals();
}
    }

    private function formatCities(array $cities): array
    {
        return collect($cities)->mapWithKeys(fn($c, $id) => [$id => $c['name']])->toArray();
    }


    /** === Обновление списка по экспедитору === */
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
            $this->customers = Client::orderBy('company_name')
            ->pluck('company_name', 'id')
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
            'driver_id'           => $this->driver_id,
            'truck_id'            => $this->truck_id,
            'trailer_id'          => $this->trailer_id,
            'status'              => $this->status,
        ]);

       foreach ($this->cargos as $cargo) {

    $taxPercent = floatval($cargo['tax_percent'] ?? 0);

    // 🟢 Сумма всех позиций с налогом
    $totalPriceWithTax = collect($cargo['items'])->sum('price_with_tax');

    // 🟢 Вычисляем сумму без налога и сам налог
    $priceWithoutTax = $taxPercent > 0
        ? round($totalPriceWithTax / (1 + $taxPercent / 100), 2)
        : $totalPriceWithTax;

    $totalTaxAmount = round($totalPriceWithTax - $priceWithoutTax, 2);

    // 🟢 Создаём сам груз (TripCargo)
    $cargoModel = $trip->cargos()->create([
        'shipper_id'           => $cargo['shipper_id'] ?? null,
        'consignee_id'         => $cargo['consignee_id'] ?? null,
        'customer_id'          => $cargo['customer_id'] ?? null,
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
        'cargo_paletes'        => collect($cargo['items'])->sum('cargo_paletes'),
        'cargo_tonnes'         => collect($cargo['items'])->sum('cargo_tonnes'),
        'cargo_weight'         => collect($cargo['items'])->sum('weight'),
        'cargo_netto_weight'   => collect($cargo['items'])->sum('cargo_netto_weight'),
        'cargo_volume'         => collect($cargo['items'])->sum('volume'),

        // 💰 Финансы на уровне клиента
        'tax_percent'          => $taxPercent,
        'price'                => $priceWithoutTax,
        'total_tax_amount'     => $totalTaxAmount,
        'price_with_tax'       => $totalPriceWithTax,
        'currency'             => $cargo['currency'] ?? 'EUR',

        'cargo_instructions'   => $cargo['cargo_instructions'] ?? '',
        'cargo_remarks'        => $cargo['cargo_remarks'] ?? '',
        'payment_terms'        => $cargo['payment_terms'] ?? null,
        'payer_type_id'        => $cargo['payer_type_id'] ?? null,
    ]);

    // 🧾 Сохраняем товары (без цен)
  foreach ($cargo['items'] as $item) {
    $priceWithTax = (float)($item['price_with_tax'] ?? 0);

    if ($taxPercent > 0) {
        $priceWithoutTax = round($priceWithTax / (1 + $taxPercent / 100), 2);
        $taxAmount = round($priceWithTax - $priceWithoutTax, 2);
    } else {
        $priceWithoutTax = $priceWithTax;
        $taxAmount = 0;
    }

    $cargoModel->items()->create([
        'description'        => $item['description'] ?? '',
        'packages'           => $item['packages'] ?? 0,
        'cargo_paletes'      => $item['cargo_paletes'] ?? 0,
        'cargo_tonnes'       => $item['cargo_tonnes'] ?? 0,
        'weight'             => $item['weight'] ?? 0,
        'cargo_netto_weight' => $item['cargo_netto_weight'] ?? 0,
        'volume'             => $item['volume'] ?? 0,
        'instructions'       => $item['instructions'] ?? '',
        'remarks'            => $item['remarks'] ?? '',

        // 💶 Добавим цену по позиции
        'price'  => $priceWithoutTax,
        'tax_amount'         => $taxAmount,
        'price_with_tax'     => $priceWithTax,
         'tax_percent'          => $taxPercent,
    ]);
}
}
        $this->resetExcept('successMessage');
        $this->successMessage = '✅ Trip successfully created with per-item storage (no JSON)!';

        return redirect()->route('trips.index');
    }

    private function buildCargoDescription(array $items): string
    {
        return collect($items)
            ->map(fn($it) => trim(($it['packages'] ?? 0) . ' × ' . ($it['description'] ?? '')))
            ->implode(', ');
    }

    public function render()
    {
        return view('livewire.trips.create-trip', [
            'companies'  => collect(config('companies'))->mapWithKeys(fn($c, $id) => [$id => $c['name']])->toArray(),
            'countries'  => collect(config('countries'))->mapWithKeys(fn($c, $id) => [$id => $c['name']])->toArray(),
            'payerTypes' => collect(config('payers'))->mapWithKeys(fn($p, $id) => [$id => $p['label']])->toArray(),
            'clients'    => $this->clients ?: Client::orderBy('company_name')->pluck('company_name', 'id')->toArray(),
            'customers'    => $this->customers ?: Client::orderBy('company_name')->pluck('company_name', 'id')->toArray(),
            'drivers'    => $this->drivers,
            'trucks'     => $this->trucks,
            'trailers'   => $this->trailers,
        ])->layout('layouts.app');
    }

    protected $messages = [
    // === Общие ===
    'expeditor_id.required' => 'Please select an expeditor company.',
    'driver_id.required'    => 'Driver is required.',
    'truck_id.required'     => 'Truck is required.',

    // === Грузы ===
    'cargos.*.customer_id.required'          => 'Customer must be selected.',
    'cargos.*.shipper_id.required'           => 'Shipper is required.',
    'cargos.*.consignee_id.required'         => 'Consignee is required.',
    'cargos.*.loading_country_id.required'   => 'Loading country is required.',
    'cargos.*.loading_city_id.required'      => 'Loading city is required.',
    'cargos.*.loading_address.required'      => 'Enter loading address.',
    'cargos.*.loading_date.required'         => 'Specify loading date.',
    'cargos.*.unloading_country_id.required' => 'Unloading country is required.',
    'cargos.*.unloading_city_id.required'    => 'Unloading city is required.',
    'cargos.*.unloading_address.required'    => 'Enter unloading address.',
    'cargos.*.unloading_date.required'       => 'Specify unloading date.',
    'cargos.*.tax_percent.required'          => 'Tax percentage must be selected.',

    // === Items ===
    'cargos.*.items.*.description.required'    => 'Please enter item description.',
    'cargos.*.items.*.packages.required'       => 'Enter number of packages.',
    'cargos.*.items.*.weight.required'         => 'Enter weight in kg.',
    'cargos.*.items.*.price_with_tax.required' => 'Enter item price (with tax).',
      'cargos.*.tax_percent.required' => 'Please select tax percentage.',
    'cargos.*.tax_percent.numeric'  => 'Tax must be a number.',
];

}
