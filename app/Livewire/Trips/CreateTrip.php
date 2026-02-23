<?php

namespace App\Livewire\Trips;

use Livewire\Component;
use App\Helpers\CalculateTax;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use App\Models\{
    Trip,
    TripCargo,
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
        if ($this->bank_index === null || !isset($this->banks[$this->bank_index])) {
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
     *  HELPERS: NUM NORMALIZATION
     * ============================================================ */
    private function normNumString($v): ?string
    {
        if ($v === null) return null;
        if ($v === '') return null;

        $v = (string) $v;

        // remove spaces + nbsp
        $v = str_replace(["\xc2\xa0", ' '], '', $v);

        // comma -> dot
        $v = str_replace(',', '.', $v);

        return $v === '' ? null : $v;
    }

    private function toFloat($v, float $default = 0.0): float
    {
        $v = $this->normNumString($v);
        if ($v === null) return $default;
        if (!is_numeric($v)) return $default;
        return (float) $v;
    }

    private function toInt($v, int $default = 0): int
    {
        if ($v === null || $v === '') return $default;
        $v = (string) $v;
        $v = str_replace(["\xc2\xa0", ' '], '', $v);
        if (!is_numeric($v)) return $default;
        return (int) $v;
    }

    private function normalizeInputsForValidation(): void
    {
        // normalize cargo price + item numeric fields so Laravel numeric validation works with commas
        foreach ($this->cargos as $ci => $cargo) {
            $this->cargos[$ci]['price'] = $this->normNumString($cargo['price'] ?? null);

            // tax_percent is select; but if you ever allow typing, keep safe:
            $this->cargos[$ci]['tax_percent'] = $this->normNumString($cargo['tax_percent'] ?? null) ?? ($cargo['tax_percent'] ?? null);

            foreach (($cargo['items'] ?? []) as $ii => $item) {
                foreach (['packages','pallets','units','net_weight','gross_weight','tonnes','volume','loading_meters'] as $f) {
                    $this->cargos[$ci]['items'][$ii][$f] = $this->normNumString($item[$f] ?? null);
                }
            }
        }
    }

    /** ============================================================
     *  STEPS
     * ============================================================ */
    public function addStep()
    {
        $this->steps[] = [
            'uid'        => (string) Str::uuid(),   // ✅ стабильный ключ для wire:key
            'type'       => 'loading',
            'country_id' => null,
            'city_id'    => null,
            'address'    => null,
            'date'       => null,
            'time'       => null,
            'order'      => count($this->steps) + 1,
            'notes'      => null,
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
            $cargo['loading_step_ids'] = array_values(array_filter(
                $cargo['loading_step_ids'] ?? [],
                fn ($i) => isset($this->steps[$i])
            ));
            $cargo['unloading_step_ids'] = array_values(array_filter(
                $cargo['unloading_step_ids'] ?? [],
                fn ($i) => isset($this->steps[$i])
            ));
        }
    }

    public function updatedSteps($value, $key)
    {
        $parts = explode('.', $key);
        $stepIndex = (int)($parts[0] ?? 0);
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
            'uid'                => (string) Str::uuid(), // ✅ стабильный ключ для wire:key

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
                    'uid'             => (string) Str::uuid(), // ✅
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
            'uid'             => (string) Str::uuid(), // ✅
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
        $this->cargos[$cargoIndex]['items'] = array_values($this->cargos[$cargoIndex]['items']);
    }

    /** ============================================================
     *  TAX
     * ============================================================ */
    public function updated($name)
    {
        if (preg_match('/^cargos\.(\d+)\.(price|tax_percent)$/', $name, $m)) {
            $idx = (int) $m[1];
            $this->recalcCargoTotals($idx);
        }
    }

    public function recalcCargoTotals($idx)
    {
        $p = $this->toFloat($this->cargos[$idx]['price'] ?? null, 0.0);
        $t = $this->toFloat($this->cargos[$idx]['tax_percent'] ?? null, 0.0);

        $tax = CalculateTax::calculate($p, $t);

        $this->cargos[$idx]['total_tax_amount'] = $tax['tax_amount'];
        $this->cargos[$idx]['price_with_tax']   = $tax['price_with_tax'];
    }

    /** ============================================================
     *  SAVE
     * ============================================================ */
    public function save()
    {
        // ✅ важно: чтобы numeric валидация не падала на "1000,50" и т.п.
        $this->normalizeInputsForValidation();

        $rules = [
            'expeditor_id' => 'required|integer',
            'bank_index'   => 'required',
            'driver_id'    => 'required|integer',
            'truck_id'     => 'required|integer',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date',
            'currency'     => 'required|string',

            // шаги маршрута
            'steps.*.type'       => 'required',
            'steps.*.country_id' => 'required|integer',
            'steps.*.city_id'    => 'required|integer',
            'steps.*.address'    => 'required|string',
            'steps.*.date'       => 'required|date',
            'steps.*.time'       => 'nullable',
            'steps.*.order'      => 'required|integer',

            // грузы
            'cargos.*.customer_id'        => 'required|integer',
            'cargos.*.shipper_id'         => 'required|integer',
            'cargos.*.consignee_id'       => 'required|integer',
            'cargos.*.loading_step_ids'   => 'required|array|min:1',
            'cargos.*.unloading_step_ids' => 'required|array|min:1',
            'cargos.*.price'              => 'required|numeric',
            'cargos.*.tax_percent'        => 'required|numeric',
        ];

        $messages = [
            'cargos.*.loading_step_ids.required'   => 'Выберите хотя бы один шаг погрузки.',
            'cargos.*.unloading_step_ids.required' => 'Выберите хотя бы один шаг разгрузки.',
        ];

        $data = [
            'expeditor_id' => $this->expeditor_id,
            'bank_index'   => $this->bank_index,
            'driver_id'    => $this->driver_id,
            'truck_id'     => $this->truck_id,
            'start_date'   => $this->start_date,
            'end_date'     => $this->end_date,
            'currency'     => $this->currency,
            'steps'        => $this->steps,
            'cargos'       => $this->cargos,
        ];

        $validator = Validator::make($data, $rules, $messages);

        // Кастомная проверка: в каждой позиции должна быть хотя бы 1 "единица"
        $validator->after(function ($validator) {
            foreach ($this->cargos as $cargoIndex => $cargo) {
                foreach (($cargo['items'] ?? []) as $itemIndex => $item) {

                    $hasAny =
                        ($this->toFloat($item['packages'] ?? null, 0) > 0) ||
                        ($this->toFloat($item['pallets'] ?? null, 0) > 0) ||
                        ($this->toFloat($item['units'] ?? null, 0) > 0) ||
                        ($this->toFloat($item['net_weight'] ?? null, 0) > 0) ||
                        ($this->toFloat($item['gross_weight'] ?? null, 0) > 0) ||
                        ($this->toFloat($item['tonnes'] ?? null, 0) > 0) ||
                        ($this->toFloat($item['volume'] ?? null, 0) > 0) ||
                        ($this->toFloat($item['loading_meters'] ?? null, 0) > 0);

                    if (!$hasAny) {
                        $validator->errors()->add(
                            "cargos.$cargoIndex.items.$itemIndex.measurements",
                            'В позиции #' . ($itemIndex + 1) . ' необходимо указать хотя бы одну единицу измерения.'
                        );
                    }
                }
            }
        });

        if ($validator->fails()) {
            $this->setErrorBag($validator->errors());
            return;
        }

        // unloading после loading (по индексам steps массива)
        foreach ($this->cargos as $ci => $c) {
            foreach (($c['loading_step_ids'] ?? []) as $lIndex) {
                foreach (($c['unloading_step_ids'] ?? []) as $uIndex) {
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
            // ------ TRIP ------
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

            // ------ STEPS ------
            $stepIdMap = [];

            foreach ($this->steps as $i => $s) {
                if (empty($s['type']) || empty($s['country_id'])) {
                    continue;
                }

                $dbStep = TripStep::create([
                    'trip_id'    => $trip->id,
                    'order'      => (int)($s['order'] ?? ($i + 1)),
                    'type'       => $s['type'],
                    'country_id' => $s['country_id'],
                    'city_id'    => $s['city_id'],
                    'address'    => $s['address'],
                    'date'       => $s['date'],
                    'time'       => $s['time'] ?? null,
                    'notes'      => $s['notes'] ?? null,
                ]);

                $stepIdMap[$i] = $dbStep->id;
            }

            // ------ CARGOS ------
            foreach ($this->cargos as $cargoData) {

                $price = $this->toFloat($cargoData['price'] ?? null, 0.0);
                $taxPercent = $this->toFloat($cargoData['tax_percent'] ?? null, 0.0);

                // пересчёт перед записью (чтобы не зависеть от фронта)
                $tax = CalculateTax::calculate($price, $taxPercent);

                $cargo = TripCargo::create([
                    'trip_id'          => $trip->id,
                    'customer_id'      => $cargoData['customer_id'],
                    'shipper_id'       => $cargoData['shipper_id'],
                    'consignee_id'     => $cargoData['consignee_id'],

                    'price'            => $price,
                    'tax_percent'      => $taxPercent,
                    'total_tax_amount' => $tax['tax_amount'],
                    'price_with_tax'   => $tax['price_with_tax'],

                    'currency'         => $cargoData['currency'],
                    'payment_terms'    => $cargoData['payment_terms'],
                    'payer_type_id'    => $cargoData['payer_type_id'],
                ]);

                // cargo items
                foreach (($cargoData['items'] ?? []) as $item) {
                    $cargo->items()->create([
                        'description'    => $item['description'] ?? '',

                        'packages'       => $this->toInt($item['packages'] ?? null, 0),
                        'pallets'        => $this->toInt($item['pallets'] ?? null, 0),
                        'units'          => $this->toInt($item['units'] ?? null, 0),

                        'net_weight'     => $this->toFloat($item['net_weight'] ?? null, 0.0),
                        'gross_weight'   => $this->toFloat($item['gross_weight'] ?? null, 0.0),
                        'tonnes'         => $this->toFloat($item['tonnes'] ?? null, 0.0),
                        'volume'         => $this->toFloat($item['volume'] ?? null, 0.0),
                        'loading_meters' => $this->toFloat($item['loading_meters'] ?? null, 0.0),

                        'hazmat'         => $item['hazmat'] ?? '',
                        'temperature'    => $item['temperature'] ?? '',
                        'stackable'      => (bool)($item['stackable'] ?? false),
                        'instructions'   => $item['instructions'] ?? '',
                        'remarks'        => $item['remarks'] ?? '',
                    ]);
                }

                // pivot steps
                $pivot = [];
                foreach (($cargoData['loading_step_ids'] ?? []) as $idx) {
                    if (isset($stepIdMap[$idx])) {
                        $pivot[$stepIdMap[$idx]] = ['role' => 'loading'];
                    }
                }
                foreach (($cargoData['unloading_step_ids'] ?? []) as $idx) {
                    if (isset($stepIdMap[$idx])) {
                        $pivot[$stepIdMap[$idx]] = ['role' => 'unloading'];
                    }
                }

                if ($pivot) {
                    $cargo->steps()->attach($pivot);
                }
            }

            DB::commit();
            return redirect()->route('trips.show', $trip->id);

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('CreateTrip ERROR', [
                'msg'   => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
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
