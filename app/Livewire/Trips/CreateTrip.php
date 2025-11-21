<?php

namespace App\Livewire\Trips;

use Livewire\Component;
use App\Models\TripStep;
use App\Models\{Driver, Truck, Trailer, Client, Trip};

class CreateTrip extends Component
{
    public $expeditor_id, $expeditorData = [];
    public $bank_index = 1;
    public $banks = [];

    public $driver_id, $truck_id, $trailer_id;
    public $drivers = [], $trucks = [], $trailers = [];

    public $clients = [], $customers = [];

    public $status = 'planned', $successMessage;

    public $cargos = [];

    protected $rules = [
        'expeditor_id' => 'required|integer',
        'bank_index'   => 'required|integer',
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

    public function addCargo()
    {
        $this->cargos[] = [
            'shipper_id'           => null,
            'consignee_id'         => null,
            'customer_id'          => null,
            'shipperData'          => [],
            'consigneeData'        => [],
            'customerData'         => [],

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
            'tax_percent'          => null,
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
                    'price_with_tax'     => 0,
                    'instructions'       => '',
                    'remarks'            => '',
                ],
            ],
        ];
    }

    public function removeCargo($index)
    {
        unset($this->cargos[$index]);
        $this->cargos = array_values($this->cargos);
    }

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
            'price_with_tax'     => 0,
            'instructions'       => '',
            'remarks'            => '',
        ];
    }

    public function removeCargoItem($cargoIndex, $itemIndex)
    {
        unset($this->cargos[$cargoIndex]['items'][$itemIndex]);
        $this->cargos[$cargoIndex]['items'] = array_values($this->cargos[$cargoIndex]['items']);
    }

    /** =======================================================
     *  SAVE TRIP + CREATE STEPS WITH AUTO SORT
     * =======================================================*/

    public function save()
    {
        $this->validate();

        $exp = config("companies.{$this->expeditor_id}");
        $bank = $exp['bank'][$this->bank_index] ?? [];

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
            'expeditor_bank_id'   => $this->bank_index,
            'expeditor_bank'      => $bank['name'] ?? '',
            'expeditor_iban'      => $bank['iban'] ?? '',
            'expeditor_bic'       => $bank['bic'] ?? '',
            'driver_id'           => $this->driver_id,
            'truck_id'            => $this->truck_id,
            'trailer_id'          => $this->trailer_id,
            'status'              => $this->status,
        ]);

        /** === SAVE CARGOS === */
        $steps = [];

        foreach ($this->cargos as $cargo) {

            $cargoModel = $trip->cargos()->create([
                'shipper_id'           => $cargo['shipper_id'],
                'consignee_id'         => $cargo['consignee_id'],
                'customer_id'          => $cargo['customer_id'],
                'loading_country_id'   => $cargo['loading_country_id'],
                'loading_city_id'      => $cargo['loading_city_id'],
                'loading_address'      => $cargo['loading_address'],
                'loading_date'         => $cargo['loading_date'],
                'unloading_country_id' => $cargo['unloading_country_id'],
                'unloading_city_id'    => $cargo['unloading_city_id'],
                'unloading_address'    => $cargo['unloading_address'],
                'unloading_date'       => $cargo['unloading_date'],
                'cargo_description'    => $this->buildCargoDescription($cargo['items']),
                'tax_percent'          => $cargo['tax_percent'],
                'payment_terms'        => $cargo['payment_terms'],
                'payer_type_id'        => $cargo['payer_type_id'],
            ]);

            // Items
            foreach ($cargo['items'] as $item) {
                $cargoModel->items()->create([
                    'description'        => $item['description'],
                    'packages'           => $item['packages'],
                    'cargo_paletes'      => $item['cargo_paletes'] ?? 0,
                    'cargo_tonnes'       => $item['cargo_tonnes'] ?? 0,
                    'weight'             => $item['weight'] ?? 0,
                    'cargo_netto_weight' => $item['cargo_netto_weight'] ?? 0,
                    'volume'             => $item['volume'] ?? 0,
                    'price_with_tax'     => $item['price_with_tax'],
                ]);
            }

            // Steps to sort later
            $steps[] = [
                'cargo'      => $cargoModel->id,
                'type'       => 'loading',
                'date'       => $cargo['loading_date'],
                'country_id' => $cargo['loading_country_id'],
                'city_id'    => $cargo['loading_city_id'],
                'address'    => $cargo['loading_address'],
            ];

            $steps[] = [
                'cargo'      => $cargoModel->id,
                'type'       => 'unloading',
                'date'       => $cargo['unloading_date'],
                'country_id' => $cargo['unloading_country_id'],
                'city_id'    => $cargo['unloading_city_id'],
                'address'    => $cargo['unloading_address'],
            ];
        }

        /** === AUTO SORT STEPS === */
        usort($steps, function ($a, $b) {
            $cmp = strtotime($a['date']) <=> strtotime($b['date']);
            if ($cmp !== 0) return $cmp;

            return $a['type'] === 'loading' ? -1 : 1;
        });

        /** === CREATE STEPS === */
        foreach ($steps as $i => $s) {
            TripStep::create([
                'trip_id'       => $trip->id,
                'trip_cargo_id' => $s['cargo'],
                'type'          => $s['type'],
                'country_id'    => $s['country_id'],
                'city_id'       => $s['city_id'],
                'address'       => $s['address'],
                'date'          => $s['date'],
                'order'         => $i + 1,
            ]);
        }

        $this->successMessage = 'Trip created successfully!';
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
            'companies' => collect(config('companies'))
                ->filter(fn($c) => is_array($c) && isset($c['name']))
                ->mapWithKeys(fn($c, $id) => [$id => $c['name']])
                ->toArray(),
            'countries'  => collect(config('countries'))->mapWithKeys(fn($c, $id) => [$id => $c['name']])->toArray(),
            'payerTypes' => collect(config('payers'))->mapWithKeys(fn($p, $id) => [$id => $p['label']])->toArray(),
            'clients'    => $this->clients ?: Client::orderBy('company_name')->pluck('company_name', 'id')->toArray(),
            'customers'  => $this->customers ?: Client::orderBy('company_name')->pluck('company_name', 'id')->toArray(),
            'drivers'    => $this->drivers,
            'trucks'     => $this->trucks,
            'trailers'   => $this->trailers,
            'banks'      => $this->banks,
        ])->layout('layouts.app');
    }
}
