<?php

namespace App\Livewire\Trips;

use Livewire\Component;
use App\Helpers\CalculateTax;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use App\Models\{
    Trip,
    TripCargo,
    TripStep,
    Driver,
    Truck,
    Trailer,
    Client
};

class EditTrip extends Component
{
    public Trip $trip;

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

    /** ============================================================
     *  MOUNT
     * ============================================================ */
    public function mount(Trip $trip)
    {
        $this->trip = $trip->load([
            'steps',
            'cargos.steps',
            'cargos.items',
        ]);

        $this->drivers  = Driver::where('is_active', 1)->get();
        $this->trucks   = Truck::where('is_active', 1)->get();
        $this->trailers = Trailer::where('is_active', 1)->get();

        $this->payers = config('payers', []);

        // Trip fields
        $this->expeditor_id = $this->trip->expeditor_id;
        $this->bank_index   = $this->trip->expeditor_bank_id;

        $this->driver_id  = $this->trip->driver_id;
        $this->truck_id   = $this->trip->truck_id;
        $this->trailer_id = $this->trip->trailer_id;

        $this->start_date = optional($this->trip->start_date)->format('Y-m-d');
        $this->end_date   = optional($this->trip->end_date)->format('Y-m-d');
        $this->currency   = $this->trip->currency ?? 'EUR';
        $this->status     = $this->trip->status?->value ?? (string)$this->trip->status ?? 'planned';

        // Expeditor snapshot -> data for UI
        $this->hydrateExpeditor();

        // но банк берём из trip snapshot, если есть (приоритетнее)
        $this->expeditorData['bank'] = $this->trip->expeditor_bank;
        $this->expeditorData['iban'] = $this->trip->expeditor_iban;
        $this->expeditorData['bic']  = $this->trip->expeditor_bic;

        // STEPS -> form arrays
        $this->steps = [];
        $this->stepCities = [];

        foreach ($this->trip->steps as $s) {
            $this->steps[] = [
                'type'       => $s->type,
                'country_id' => $s->country_id,
                'city_id'    => $s->city_id,
                'address'    => $s->address,
                'date'       => optional($s->date)->format('Y-m-d'),
                'time'       => $s->time,
                'order'      => $s->order,
                'notes'      => $s->notes,
            ];

            $this->stepCities[] = [
                'cities' => $s->country_id ? (getCitiesByCountryId((int)$s->country_id) ?? []) : []
            ];
        }

        if (count($this->steps) === 0) {
            $this->addStep();
        }

        // CARGOS -> form arrays (в UI шаги — это ИНДЕКСЫ массива $steps)
        $this->cargos = [];

        foreach ($this->trip->cargos as $cargo) {
            $loadingIndexes = [];
            $unloadingIndexes = [];

            foreach ($cargo->steps as $st) {
                $idx = $this->trip->steps->search(fn ($x) => $x->id === $st->id);
                if ($idx === false) continue;

                if ($st->pivot?->role === 'loading') $loadingIndexes[] = $idx;
                if ($st->pivot?->role === 'unloading') $unloadingIndexes[] = $idx;
            }

            $items = [];
            foreach ($cargo->items as $it) {
                $items[] = [
                    'description'     => $it->description ?? '',
                    'packages'        => $it->packages,
                    'pallets'         => $it->pallets,
                    'units'           => $it->units,
                    'net_weight'      => $it->net_weight,
                    'gross_weight'    => $it->gross_weight,
                    'tonnes'          => $it->tonnes,
                    'volume'          => $it->volume,
                    'loading_meters'  => $it->loading_meters,
                    'hazmat'          => $it->hazmat ?? '',
                    'temperature'     => $it->temperature ?? '',
                    'stackable'       => (bool)$it->stackable,
                    'instructions'    => $it->instructions ?? '',
                    'remarks'         => $it->remarks ?? '',
                ];
            }

            $this->cargos[] = [
                'customer_id'        => $cargo->customer_id,
                'shipper_id'         => $cargo->shipper_id,
                'consignee_id'       => $cargo->consignee_id,

                'loading_step_ids'   => array_values(array_unique($loadingIndexes)),
                'unloading_step_ids' => array_values(array_unique($unloadingIndexes)),

                'price'            => (string)($cargo->price ?? ''),
                'tax_percent'      => (float)($cargo->tax_percent ?? 21),
                'total_tax_amount' => (float)($cargo->total_tax_amount ?? 0),
                'price_with_tax'   => (float)($cargo->price_with_tax ?? 0),
                'currency'         => $cargo->currency ?? $this->currency,
                'payment_terms'    => optional($cargo->payment_terms)->format('Y-m-d'),
                'payer_type_id'    => $cargo->payer_type_id,

                'items' => $items ?: [[
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
                ]],
            ];
        }

        if (count($this->cargos) === 0) {
            $this->addCargo();
        }

        foreach (array_keys($this->cargos) as $i) {
            $this->recalcCargoTotals($i);
        }
    }

    /** ============================================================
     *  STEP LABEL HELPERS (UI)
     * ============================================================ */
    public function stepMetaByIndex(int $idx): array
    {
        $s = $this->steps[$idx] ?? null;
        if (!$s) {
            return [
                'type' => 'loading',
                'icon' => '📦',
                'typeLabel' => 'Iekraušana',
                'location' => '—',
                'address' => null,
                'dateTime' => '—',
                'label' => '—',
            ];
        }

        $type = ($s['type'] ?? 'loading') === 'unloading' ? 'unloading' : 'loading';

        $icon = $type === 'loading' ? '📦' : '📤';
        $typeLabel = $type === 'loading' ? 'Iekraušana' : 'Izkraušana';

        // под твои helpers (они уже используются в blade)
        $country = !empty($s['country_id']) ? (getCountryById((int)$s['country_id']) ?? null) : null;

        $city = (!empty($s['country_id']) && !empty($s['city_id']))
            ? (getCityNameByCountryId((int)$s['country_id'], (int)$s['city_id']) ?? null)
            : null;

        $location = $city ?: $country ?: '—';

        $address = !empty($s['address']) ? trim((string)$s['address']) : null;

        $date = !empty($s['date']) ? \Carbon\Carbon::parse($s['date'])->format('d.m.Y') : '—';
        $time = !empty($s['time']) ? trim((string)$s['time']) : null;
        $dateTime = ($date !== '—')
            ? ($time ? "{$date} {$time}" : $date)
            : '—';

        $parts = array_values(array_filter([$location, $address]));
        $line = $parts ? implode(', ', $parts) : '—';

        $label = "{$icon} {$typeLabel} • {$line} • {$dateTime}";

        return compact('type', 'icon', 'typeLabel', 'location', 'address', 'dateTime', 'label');
    }

    public function stepLabelByIndex(int $idx): string
    {
        return $this->stepMetaByIndex($idx)['label'] ?? '—';
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
        $this->bank_index = $this->bank_index ?? ($this->banks ? array_key_first($this->banks) : null);

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

    public function updatedExpeditorId() { $this->hydrateExpeditor(); }
    public function updatedBankIndex() { $this->hydrateBank(); }

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
        unset($cargo);
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
            'customer_id'        => null,
            'shipper_id'         => null,
            'consignee_id'       => null,

            'loading_step_ids'   => [],
            'unloading_step_ids' => [],

            'price'            => '',
            'tax_percent'      => 21,
            'total_tax_amount' => 0,
            'price_with_tax'   => 0,
            'currency'         => $this->currency,
            'payment_terms'    => null,
            'payer_type_id'    => null,

            'items' => [[
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
            ]],
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
        $this->cargos[$cargoIndex]['items'] = array_values($this->cargos[$cargoIndex]['items']);
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
        $rules = [
            'expeditor_id' => 'required|integer',
            'bank_index'   => 'required',
            'driver_id'    => 'required|integer',
            'truck_id'     => 'required|integer',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date',
            'currency'     => 'required|string',

            'steps.*.type'       => 'required',
            'steps.*.country_id' => 'required|integer',
            'steps.*.city_id'    => 'required|integer',
            'steps.*.address'    => 'required|string',
            'steps.*.date'       => 'required|date',
            'steps.*.time'       => 'nullable',
            'steps.*.order'      => 'required|integer',

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

        $validator->after(function ($validator) {
            foreach ($this->cargos as $cargoIndex => $cargo) {
                foreach (($cargo['items'] ?? []) as $itemIndex => $item) {

                    $hasAny =
                        (!empty($item['packages'])       && $item['packages'] > 0) ||
                        (!empty($item['pallets'])        && $item['pallets'] > 0) ||
                        (!empty($item['units'])          && $item['units'] > 0) ||
                        (!empty($item['net_weight'])     && $item['net_weight'] > 0) ||
                        (!empty($item['gross_weight'])   && $item['gross_weight'] > 0) ||
                        (!empty($item['tonnes'])         && $item['tonnes'] > 0) ||
                        (!empty($item['volume'])         && $item['volume'] > 0) ||
                        (!empty($item['loading_meters']) && $item['loading_meters'] > 0);

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

        // unloading после loading (по индексам steps массива) + запрет пересечения
        foreach ($this->cargos as $ci => $c) {
            $loading = array_unique($c['loading_step_ids'] ?? []);
            $unload  = array_unique($c['unloading_step_ids'] ?? []);

            $intersect = array_intersect($loading, $unload);
            if (!empty($intersect)) {
                $this->addError("cargos.$ci.unloading_step_ids", 'Один и тот же шаг не может быть и погрузкой, и разгрузкой.');
                return;
            }

            foreach ($loading as $lIndex) {
                foreach ($unload as $uIndex) {
                    if ($uIndex <= $lIndex) {
                        $this->addError("cargos.$ci.unloading_step_ids", 'Разгрузки должны быть ПОСЛЕ всех погрузок.');
                        return;
                    }
                }
            }
        }

        DB::beginTransaction();

        try {
            $this->trip->update([
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

                'driver_id'  => $this->driver_id,
                'truck_id'   => $this->truck_id,
                'trailer_id' => $this->trailer_id,

                'start_date' => $this->start_date,
                'end_date'   => $this->end_date,

                'currency' => $this->currency,
                'status'   => $this->status,
            ]);

            $this->trip->load('cargos');

            // steps
            $this->trip->steps()->delete();

            // cargos + pivot + items
            foreach ($this->trip->cargos as $c) {
                $c->steps()->detach();
                $c->items()->delete();
            }
            $this->trip->cargos()->delete();

            // create steps
            $stepIdMap = [];

            foreach ($this->steps as $i => $s) {
                $dbStep = TripStep::create([
                    'trip_id'    => $this->trip->id,
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

            // create cargos + items + pivot
            foreach ($this->cargos as $cargoData) {

                $cargo = TripCargo::create([
                    'trip_id'          => $this->trip->id,
                    'customer_id'      => $cargoData['customer_id'],
                    'shipper_id'       => $cargoData['shipper_id'],
                    'consignee_id'     => $cargoData['consignee_id'],

                    'price'            => $cargoData['price'] ?: 0,
                    'tax_percent'      => $cargoData['tax_percent'],
                    'total_tax_amount' => $cargoData['total_tax_amount'],
                    'price_with_tax'   => $cargoData['price_with_tax'],

                    'currency'         => $cargoData['currency'],
                    'payment_terms'    => $cargoData['payment_terms'],
                    'payer_type_id'    => $cargoData['payer_type_id'],
                ]);

                foreach (($cargoData['items'] ?? []) as $item) {
                    $cargo->items()->create([
                        'description'    => $item['description'] ?? '',
                        'packages'       => $item['packages'] ?? 0,
                        'pallets'        => $item['pallets'] ?? 0,
                        'units'          => $item['units'] ?? 0,
                        'net_weight'     => $item['net_weight'] ?? 0,
                        'gross_weight'   => $item['gross_weight'] ?? 0,
                        'tonnes'         => $item['tonnes'] ?? 0,
                        'volume'         => $item['volume'] ?? 0,
                        'loading_meters' => $item['loading_meters'] ?? 0,
                        'hazmat'         => $item['hazmat'] ?? '',
                        'temperature'    => $item['temperature'] ?? '',
                        'stackable'      => (bool)($item['stackable'] ?? false),
                        'instructions'   => $item['instructions'] ?? '',
                        'remarks'        => $item['remarks'] ?? '',
                    ]);
                }

                $pivot = [];

                foreach (array_unique($cargoData['loading_step_ids'] ?? []) as $idx) {
                    if (isset($stepIdMap[$idx])) $pivot[$stepIdMap[$idx]] = ['role' => 'loading'];
                }
                foreach (array_unique($cargoData['unloading_step_ids'] ?? []) as $idx) {
                    if (isset($stepIdMap[$idx])) $pivot[$stepIdMap[$idx]] = ['role' => 'unloading'];
                }

                if ($pivot) $cargo->steps()->attach($pivot);
            }

            DB::commit();

            return redirect()->route('trips.show', $this->trip->id);

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('EditTrip ERROR', [
                'msg'  => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            $this->addError('error', 'Ошибка при сохранении рейса.');
        }
    }

    /** ============================================================
     *  RENDER
     * ============================================================ */
    public function render()
    {
        return view('livewire.trips.edit-trip', [
            'clients'    => Client::orderBy('company_name')->get(),
            'countries'  => config('countries', []),
            'expeditors' => config('companies', []),
            'payers'     => $this->payers,
            'taxRates'   => $this->taxRates,
        ])->layout('layouts.app');
    }
}
