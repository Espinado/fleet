<?php

namespace App\Livewire\Trips;

use Livewire\Component;
use App\Helpers\CalculateTax;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\{
    Trip,
    TripCargo,
    TripCargoItem,
    TripStep,
    Driver,
    Truck,
    Trailer,
    Client
};

class CreateTrip extends Component
{
    /** ============================================================
     *  EXPEDITOR
     * ============================================================ */
    public $expeditor_id = null;
    public $expeditorData = [];
    public $banks = [];
    public $bank_index = null;

    public array $payers = [];
    public array $taxRates = [0, 5, 10, 21];

    /** ============================================================
     *  TRANSPORT
     * ============================================================ */
    public $driver_id;
    public $truck_id;
    public $trailer_id;

    public $drivers = [];
    public $trucks = [];
    public $trailers = [];

    /** ============================================================
     *  STEPS
     * ============================================================ */
    public $steps = [];
    public $stepCities = [];

    /** ============================================================
     *  CARGOS (multi)
     * ============================================================ */
    public $cargos = [];

    /** ============================================================
     *  TRIP
     * ============================================================ */
    public $currency = 'EUR';
    public $start_date;
    public $end_date;
    public $status = 'planned';
    public $successMessage = null;


    /** ============================================================
     *  MOUNT
     * ============================================================ */
    public function mount()
    {
        $this->drivers  = Driver::where('is_active', 1)->get();
        $this->trucks   = Truck::where('is_active', 1)->get();
        $this->trailers = Trailer::where('is_active', 1)->get();

        $this->payers = config('payers', []);

        $this->addStep();
        $this->addCargo();
    }

    /** ============================================================
     *  EXPEDITOR
     * ============================================================ */

    protected function hydrateExpeditor()
    {
        $expeditors = config('companies', []);
        $id = $this->expeditor_id;

        if (!$id || !isset($expeditors[$id])) {
            $this->expeditorData = [];
            $this->banks = [];
            $this->bank_index = null;
            return;
        }

        $exp = $expeditors[$id];

        $this->expeditorData = [
            'name'      => $exp['name']      ?? null,
            'reg_nr'    => $exp['reg_nr']    ?? null,
            'country'   => $exp['country']   ?? null,
            'city'      => $exp['city']      ?? null,
            'address'   => $exp['address']   ?? null,
            'post_code' => $exp['post_code'] ?? null,
            'email'     => $exp['email']     ?? null,
            'phone'     => $exp['phone']     ?? null,
            'bank'      => null,
            'iban'      => null,
            'bic'       => null,
        ];

        $this->banks = $exp['bank'] ?? [];
        $this->bank_index = $this->banks ? array_key_first($this->banks) : null;

        $this->hydrateBank();
    }

    protected function hydrateBank()
    {
        if (!$this->bank_index || !isset($this->banks[$this->bank_index])) {
            $this->expeditorData['bank'] = null;
            $this->expeditorData['iban'] = null;
            $this->expeditorData['bic']  = null;
            return;
        }

        $bank = $this->banks[$this->bank_index];

        $this->expeditorData['bank'] = $bank['name'] ?? null;
        $this->expeditorData['iban'] = $bank['iban'] ?? null;
        $this->expeditorData['bic']  = $bank['bic']  ?? null;
    }

    public function updatedExpeditorId()
    {
        $this->hydrateExpeditor();
    }

    public function updatedBankIndex()
    {
        $this->hydrateBank();
    }


    /** ============================================================
     *  STEPS
     * ============================================================ */

    public function addStep()
    {
        $this->steps[] = [
            'type'       => 'loading',
            'country_id' => null,
            'city_id'    => null,
            'address'    => null,
            'date'       => null,
            'time'       => null,
            'comment'    => null,
        ];

        $this->stepCities[] = ['cities' => []];
    }

    public function removeStep($index)
    {
        unset($this->steps[$index], $this->stepCities[$index]);
        $this->steps = array_values($this->steps);
        $this->stepCities = array_values($this->stepCities);

        // после удаления индексы сдвигаются → чистим связки
        foreach ($this->cargos as &$cargo) {
            $cargo['loading_step_ids'] = array_filter(
                $cargo['loading_step_ids'] ?? [],
                fn($i) => isset($this->steps[$i])
            );
            $cargo['unloading_step_ids'] = array_filter(
                $cargo['unloading_step_ids'] ?? [],
                fn($i) => isset($this->steps[$i])
            );
        }
    }

    public function updatedSteps($value, $key)
    {
        $parts = explode('.', $key);
        $stepIndex = (int)$parts[0];
        $field = $parts[1] ?? null;

        if ($field === 'country_id') {
            $this->stepCities[$stepIndex]['cities'] =
                getCitiesByCountryId((int)$value) ?? [];

            $this->steps[$stepIndex]['city_id'] = null;
        }
    }


    /** ============================================================
     *  CARGOS
     * ============================================================ */

    public function addCargo()
    {
        $this->cargos[] = [
            'customer_id'        => null,
            'shipper_id'         => null,
            'consignee_id'       => null,

            // МУЛЬТИВЫБОР
            'loading_step_ids'   => [],
            'unloading_step_ids' => [],

            // Оплата
            'price'            => '',
            'tax_percent'      => 21,
            'total_tax_amount' => 0,
            'price_with_tax'   => 0,
            'currency'         => $this->currency,
            'payment_terms'    => null,
            'payer_type_id'    => null,

            'items' => [
                [
                    'description'     => '',
                    'packages'        => null,
                    'pallets'         => null,
                    'units'           => null,
                    'net_weight'      => null,
                    'gross_weight'    => null,
                    'tonnes'          => null,
                    'volume'          => null,
                    'loading_meters'  => null,
                    'hazmat'          => '',
                    'temperature'     => '',
                    'stackable'       => false,
                    'instructions'    => '',
                    'remarks'         => '',
                ],
            ],
        ];
    }

    public function removeCargo($index)
    {
        unset($this->cargos[$index]);
        $this->cargos = array_values($this->cargos);
    }

    public function addItem($cargoIndex)
    {
        $this->cargos[$cargoIndex]['items'][] = [
            'description'     => '',
            'packages'        => null,
            'pallets'         => null,
            'units'           => null,
            'net_weight'      => null,
            'gross_weight'    => null,
            'tonnes'          => null,
            'volume'          => null,
            'loading_meters'  => null,
            'hazmat'          => '',
            'temperature'     => '',
            'stackable'       => false,
            'instructions'    => '',
            'remarks'         => '',
        ];
    }

    public function removeItem($cargoIndex, $itemIndex)
    {
        unset($this->cargos[$cargoIndex]['items'][$itemIndex]);
        $this->cargos[$cargoIndex]['items'] =
            array_values($this->cargos[$cargoIndex]['items']);
    }


    /** ============================================================
     *  TAX
     * ============================================================ */
    public function updated($name)
    {
        if (preg_match('/^cargos\.(\d+)\.(price|tax_percent)$/', $name, $m)) {
            $idx = (int)$m[1];
            $this->recalcCargoTotals($idx);
        }
    }

    public function recalcCargoTotals($idx)
    {
        $p  = (float)($this->cargos[$idx]['price'] ?? 0);
        $t  = (float)($this->cargos[$idx]['tax_percent'] ?? 0);

        $tax = CalculateTax::calculate($p, $t);

        $this->cargos[$idx]['total_tax_amount'] = $tax['tax_amount'];
        $this->cargos[$idx]['price_with_tax']   = $tax['price_with_tax'];
    }


    /** ============================================================
     *  SAVE
     * ============================================================ */
    public function save()
    {
        $this->validate([
            'expeditor_id' => 'required|integer',
            'driver_id'    => 'required|integer',
            'truck_id'     => 'required|integer',
            'currency'     => 'required|string',
        ]);

        // Проверка порядка шагов в мультиселекте
        foreach ($this->cargos as $ci => $c) {
            foreach ($c['loading_step_ids'] as $lIndex) {
                foreach ($c['unloading_step_ids'] as $uIndex) {
                    if ($uIndex <= $lIndex) {
                        $this->addError(
                            "cargos.$ci.unloading_step_ids",
                            'Разгрузки должны быть ПОСЛЕ всех погрузок.'
                        );
                        return;
                    }
                }
            }
        }

        DB::beginTransaction();

        try {
            /** --- Trip --- */
            $trip = Trip::create([
                'expeditor_id'        => $this->expeditor_id,
                'expeditor_name'      => $this->expeditorData['name']      ?? null,
                'expeditor_reg_nr'    => $this->expeditorData['reg_nr']    ?? null,
                'expeditor_country'   => $this->expeditorData['country']   ?? null,
                'expeditor_city'      => $this->expeditorData['city']      ?? null,
                'expeditor_address'   => $this->expeditorData['address']   ?? null,
                'expeditor_post_code' => $this->expeditorData['post_code'] ?? null,
                'expeditor_email'     => $this->expeditorData['email']     ?? null,
                'expeditor_phone'     => $this->expeditorData['phone']     ?? null,

                'expeditor_bank_id' => $this->bank_index,
                'expeditor_bank'    => $this->expeditorData['bank'] ?? null,
                'expeditor_iban'    => $this->expeditorData['iban'] ?? null,
                'expeditor_bic'     => $this->expeditorData['bic']  ?? null,

                'driver_id' => $this->driver_id,
                'truck_id'  => $this->truck_id,
                'trailer_id'=> $this->trailer_id,

                'start_date'=> $this->start_date,
                'end_date'  => $this->end_date,

                'currency'  => $this->currency,
                'status'    => $this->status,
            ]);

            /** --- Steps --- */
            $stepIdMap = [];

            foreach ($this->steps as $i => $s) {
                if (!$s['type'] || !$s['country_id']) continue;

                $dbStep = TripStep::create([
                    'trip_id'    => $trip->id,
                    'order'      => $i + 1,
                    'type'       => $s['type'],
                    'country_id' => $s['country_id'],
                    'city_id'    => $s['city_id'],
                    'address'    => $s['address'],
                    'date'       => $s['date'],
                    'time'       => $s['time'],
                    'notes'      => $s['comment'],
                ]);

                $stepIdMap[$i] = $dbStep->id;
            }

            /** --- Cargos --- */
            foreach ($this->cargos as $cargoData) {

                $cargo = TripCargo::create([
                    'trip_id'          => $trip->id,
                    'customer_id'      => $cargoData['customer_id'],
                    'shipper_id'       => $cargoData['shipper_id'],
                    'consignee_id'     => $cargoData['consignee_id'],

                    'price'            => $cargoData['price']            ?: 0,
                    'tax_percent'      => $cargoData['tax_percent'],
                    'total_tax_amount' => $cargoData['total_tax_amount'],
                    'price_with_tax'   => $cargoData['price_with_tax'],

                    'currency'         => $cargoData['currency'],
                    'payment_terms'    => $cargoData['payment_terms'],
                    'payer_type_id'    => $cargoData['payer_type_id'],
                ]);

                /** Cargo items */
                foreach ($cargoData['items'] as $item) {
                    $cargo->items()->create($item);
                }

                /** --- PIVOT: steps --- */
                $pivot = [];

                foreach ($cargoData['loading_step_ids'] as $idx) {
                    if (isset($stepIdMap[$idx])) {
                        $pivot[$stepIdMap[$idx]] = ['role' => 'loading'];
                    }
                }

                foreach ($cargoData['unloading_step_ids'] as $idx) {
                    if (isset($stepIdMap[$idx])) {
                        $pivot[$stepIdMap[$idx]] = ['role' => 'unloading'];
                    }
                }

                if ($pivot) {
                    $cargo->steps()->attach($pivot);
                }
            }

            DB::commit();
            return redirect()->route('trips.view', $trip->id);

        } catch (\Throwable $e) {

            DB::rollBack();
            Log::error('CreateTrip ERROR', [
                'msg'    => $e->getMessage(),
                'line'   => $e->getLine(),
                'file'   => $e->getFile(),
                'trace'  => $e->getTraceAsString(),
            ]);

            $this->addError('error', 'Ошибка при создании рейса.');
        }
    }


    /** ============================================================
     *  RENDER
     * ============================================================ */
    public function render()
    {
        return view('livewire.trips.create-trip', [
            'clients'    => Client::orderBy('company_name')->get(),
            'countries'  => config('countries', []),
            'expeditors' => config('companies', []),
            'payers'     => $this->payers,
            'taxRates'   => $this->taxRates,
        ])->layout('layouts.app');
    }
}
