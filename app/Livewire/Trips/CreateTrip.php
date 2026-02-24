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

    public ?int $trailer_id = null;                 // выбранный trailer.id (FK в trips)
    public ?int $selected_trailer_type_id = null;   // выбранный тип прицепа (trailers.type_id)
    public ?string $cont_nr = null;
    public ?string $seal_nr = null;

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

        // если trailer_id уже стоит (редко, но ок)
        $this->updatedTrailerId($this->trailer_id);

        $this->addStep();
        $this->addCargo();
    }

    /** ============================================================
     *  EXPEDITOR
     * ============================================================ */
    protected function hydrateExpeditor(): void
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

    protected function hydrateBank(): void
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

    public function updatedExpeditorId(): void
    {
        $this->hydrateExpeditor();
    }

    public function updatedBankIndex(): void
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
        foreach ($this->cargos as $ci => $cargo) {
            $this->cargos[$ci]['price'] = $this->normNumString($cargo['price'] ?? null);

            // tax_percent is select; but if you ever allow typing, keep safe:
            $this->cargos[$ci]['tax_percent'] =
                $this->normNumString($cargo['tax_percent'] ?? null) ?? ($cargo['tax_percent'] ?? null);

            // supplier invoice amount
            $this->cargos[$ci]['supplier_invoice_amount'] =
                $this->normNumString($cargo['supplier_invoice_amount'] ?? null);

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
    public function addStep(): void
    {
        $this->steps[] = [
            'uid'        => (string) Str::uuid(),   // ✅ стабильный ключ (и будем использовать его в привязках грузов)
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

    public function removeStep($index): void
    {
        $removedUid = $this->steps[$index]['uid'] ?? null;

        unset($this->steps[$index], $this->stepCities[$index]);
        $this->steps = array_values($this->steps);
        $this->stepCities = array_values($this->stepCities);

        if ($removedUid) {
            // ✅ Ключевая часть: мы храним привязки шагов как UID (а не индексы),
            // поэтому удаление шага безопасно — просто выкидываем этот uid из выбранных.
            foreach ($this->cargos as $ci => $cargo) {
                $this->cargos[$ci]['loading_step_ids'] = array_values(array_filter(
                    $cargo['loading_step_ids'] ?? [],
                    fn ($v) => (string)$v !== (string)$removedUid
                ));

                $this->cargos[$ci]['unloading_step_ids'] = array_values(array_filter(
                    $cargo['unloading_step_ids'] ?? [],
                    fn ($v) => (string)$v !== (string)$removedUid
                ));
            }
        }
    }

    public function updatedSteps($value, $key): void
    {
        // ожидаем key в формате: "{index}.field"
        $parts = explode('.', $key);
        $stepIndex = (int)($parts[0] ?? 0);
        $field = $parts[1] ?? null;

        if ($field === 'country_id') {
            $this->stepCities[$stepIndex]['cities'] =
                getCitiesByCountryId((int)$value) ?? [];

            $this->steps[$stepIndex]['city_id'] = null;
        }
    }

    /**
     * Для совместимости:
     * - если фронт ещё шлёт индексы шагов (0,1,2...), мы можем их конвертировать в uid
     * - если фронт уже шлёт uid — оставляем как есть
     */
    private function normalizeStepSelectionsToUids(): void
    {
        foreach ($this->cargos as $ci => $cargo) {
            foreach (['loading_step_ids', 'unloading_step_ids'] as $field) {
                $vals = $cargo[$field] ?? [];
                $out = [];

                foreach ($vals as $v) {
                    // numeric index -> uid
                    if (is_numeric($v)) {
                        $idx = (int)$v;
                        $uid = $this->steps[$idx]['uid'] ?? null;
                        if ($uid) $out[] = (string)$uid;
                        continue;
                    }

                    // already uid-like string
                    if ($v !== null && $v !== '') {
                        $out[] = (string)$v;
                    }
                }

                // unique + preserve order
                $out = array_values(array_unique($out));

                $this->cargos[$ci][$field] = $out;
            }
        }
    }

    private function stepPositionByToken($token): ?int
    {
        // token может быть uid или индексом
        if (is_numeric($token)) {
            return isset($this->steps[(int)$token]) ? (int)$token : null;
        }

        $token = (string)$token;
        foreach ($this->steps as $i => $s) {
            if (($s['uid'] ?? null) === $token) return $i;
        }
        return null;
    }

    /** ============================================================
     *  CARGOS
     * ============================================================ */
    public function addCargo(): void
    {
        $this->cargos[] = [
            'uid'                => (string) Str::uuid(),

            'customer_id'        => null,
            'shipper_id'         => null,
            'consignee_id'       => null,

            // ✅ ВАЖНО: теперь это UID шагов, а не индексы. (Но компонент умеет принять и индексы.)
            'loading_step_ids'   => [],
            'unloading_step_ids' => [],

            // Оплата (наш фрахт)
            'price'            => '',
            'tax_percent'      => 21,
            'total_tax_amount' => 0,
            'price_with_tax'   => 0,
            'currency'         => $this->currency,
            'payment_terms'    => null,
            'payer_type_id'    => null,

            // Supplier invoice (для CMR/доков)
            'supplier_invoice_nr'     => null,
            'supplier_invoice_amount' => null,

            'items' => [
                [
                    'uid'             => (string) Str::uuid(),
                    'description'     => '',

                    'customs_code'    => null,

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

    public function removeCargo($index): void
    {
        unset($this->cargos[$index]);
        $this->cargos = array_values($this->cargos);
    }

    public function addItem($cargoIndex): void
    {
        $this->cargos[$cargoIndex]['items'][] = [
            'uid'             => (string) Str::uuid(),
            'description'     => '',
            'customs_code'    => null,

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

    public function removeItem($cargoIndex, $itemIndex): void
    {
        unset($this->cargos[$cargoIndex]['items'][$itemIndex]);
        $this->cargos[$cargoIndex]['items'] = array_values($this->cargos[$cargoIndex]['items']);
    }

    /** ============================================================
     *  TAX
     * ============================================================ */
    public function updated($name): void
    {
        if (preg_match('/^cargos\.(\d+)\.(price|tax_percent)$/', $name, $m)) {
            $idx = (int) $m[1];
            $this->recalcCargoTotals($idx);
        }
    }

    public function recalcCargoTotals($idx): void
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
        // ✅ 1) нормализуем числа для numeric validation
        $this->normalizeInputsForValidation();

        // ✅ 2) приводим выбранные шаги к UID (и поддерживаем старый фронт, где были индексы)
        $this->normalizeStepSelectionsToUids();

        $rules = [
            'expeditor_id' => 'required|integer',
            'bank_index'   => 'required',
            'driver_id'    => 'required|integer',
            'truck_id'     => 'required|integer',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date',
            'currency'     => 'required|string|max:3',

            'cont_nr'      => 'nullable|string|max:50',
            'seal_nr'      => 'nullable|string|max:50',

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

            // ✅ теперь здесь uid-ы, но валидация всё равно по массиву
            'cargos.*.loading_step_ids'   => 'required|array|min:1',
            'cargos.*.unloading_step_ids' => 'required|array|min:1',

            'cargos.*.price'              => 'required|numeric',
            'cargos.*.tax_percent'        => 'required|numeric',
            'cargos.*.currency'           => 'required|string|max:3',

            // supplier invoice
            'cargos.*.supplier_invoice_nr'     => 'nullable|string|max:64',
            'cargos.*.supplier_invoice_amount' => 'nullable|numeric|min:0',

            // customs code per item
            'cargos.*.items.*.customs_code'    => 'nullable|string|max:32',
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
            'cont_nr'      => $this->cont_nr,
            'seal_nr'      => $this->seal_nr,
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

        // ✅ unloading после loading (по позиции в массиве steps; работает и для uid, и для индексов)
        foreach ($this->cargos as $ci => $c) {
            foreach (($c['loading_step_ids'] ?? []) as $lToken) {
                foreach (($c['unloading_step_ids'] ?? []) as $uToken) {
                    $lPos = $this->stepPositionByToken($lToken);
                    $uPos = $this->stepPositionByToken($uToken);

                    // если не нашли — отловим как ошибку данных
                    if ($lPos === null || $uPos === null) {
                        $this->addError("cargos.$ci.unloading_step_ids", 'Выбраны некорректные шаги (обновите страницу и попробуйте снова).');
                        return;
                    }

                    if ($uPos <= $lPos) {
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

                'driver_id'  => $this->driver_id,
                'truck_id'   => $this->truck_id,
                'trailer_id' => $this->trailer_id,

                'start_date' => $this->start_date,
                'end_date'   => $this->end_date,

                'currency'   => $this->currency,
                'status'     => $this->status,

                'cont_nr'    => $this->cont_nr,
                'seal_nr'    => $this->seal_nr,
            ]);

            // ------ STEPS ------
            // uid -> db id
            $stepUidToId = [];

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

                if (!empty($s['uid'])) {
                    $stepUidToId[(string)$s['uid']] = $dbStep->id;
                }
            }

            // ------ CARGOS ------
            foreach ($this->cargos as $cargoData) {

                $price = $this->toFloat($cargoData['price'] ?? null, 0.0);
                $taxPercent = $this->toFloat($cargoData['tax_percent'] ?? null, 0.0);

                // пересчёт перед записью (чтобы не зависеть от фронта)
                $tax = CalculateTax::calculate($price, $taxPercent);

                $supplierInvoiceAmountRaw = $cargoData['supplier_invoice_amount'] ?? null;
                $supplierInvoiceAmount = ($supplierInvoiceAmountRaw !== null && $supplierInvoiceAmountRaw !== '')
                    ? $this->toFloat($supplierInvoiceAmountRaw, 0.0)
                    : null;

                $cargo = TripCargo::create([
                    'trip_id'      => $trip->id,
                    'customer_id'  => $cargoData['customer_id'],
                    'shipper_id'   => $cargoData['shipper_id'],
                    'consignee_id' => $cargoData['consignee_id'],

                    'price'            => $price,
                    'tax_percent'      => $taxPercent,
                    'total_tax_amount' => $tax['tax_amount'],
                    'price_with_tax'   => $tax['price_with_tax'],

                    'currency'      => $cargoData['currency'] ?? $this->currency,
                    'payment_terms' => $cargoData['payment_terms'] ?? null,
                    'payer_type_id' => $cargoData['payer_type_id'] ?? null,

                    'supplier_invoice_nr'     => $cargoData['supplier_invoice_nr'] ?? null,
                    'supplier_invoice_amount' => $supplierInvoiceAmount,
                ]);

                // cargo items
                foreach (($cargoData['items'] ?? []) as $item) {
                    $cargo->items()->create([
                        'description'  => $item['description'] ?? '',
                        'customs_code' => $item['customs_code'] ?? null,

                        'packages'       => $this->toInt($item['packages'] ?? null, 0),
                        'pallets'        => $this->toInt($item['pallets'] ?? null, 0),
                        'units'          => $this->toInt($item['units'] ?? null, 0),

                        'net_weight'     => $this->toFloat($item['net_weight'] ?? null, 0.0),
                        'gross_weight'   => $this->toFloat($item['gross_weight'] ?? null, 0.0),
                        'tonnes'         => $this->toFloat($item['tonnes'] ?? null, 0.0),
                        'volume'         => $this->toFloat($item['volume'] ?? null, 0.0),
                        'loading_meters' => $this->toFloat($item['loading_meters'] ?? null, 0.0),

                        'hazmat'       => $item['hazmat'] ?? '',
                        'temperature'  => $item['temperature'] ?? '',
                        'stackable'    => (bool)($item['stackable'] ?? false),
                        'instructions' => $item['instructions'] ?? '',
                        'remarks'      => $item['remarks'] ?? '',
                    ]);
                }

                // pivot steps (uid -> db id)
                $pivot = [];

                foreach (($cargoData['loading_step_ids'] ?? []) as $token) {
                    // token должен быть uid (после normalizeStepSelectionsToUids), но оставим fallback
                    $uid = is_numeric($token) ? ($this->steps[(int)$token]['uid'] ?? null) : (string)$token;
                    if ($uid && isset($stepUidToId[$uid])) {
                        $pivot[$stepUidToId[$uid]] = ['role' => 'loading'];
                    }
                }

                foreach (($cargoData['unloading_step_ids'] ?? []) as $token) {
                    $uid = is_numeric($token) ? ($this->steps[(int)$token]['uid'] ?? null) : (string)$token;
                    if ($uid && isset($stepUidToId[$uid])) {
                        $pivot[$stepUidToId[$uid]] = ['role' => 'unloading'];
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
                'msg'  => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
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

    /** ============================================================
     *  TRAILER TYPE HELPERS
     * ============================================================ */
    private function containerTypeId(): int
    {
        $types = config('trailer-types.types', []);
        $id = array_search('container', $types, true);

        return $id ?: 2; // fallback
    }

    public function getIsContainerTrailerProperty(): bool
    {
        return (int)($this->selected_trailer_type_id ?? 0) === (int)$this->containerTypeId();
    }

    public function updatedTrailerId($value): void
    {
        // trailer_id = trailers.id (FK)
        $this->trailer_id = $value ? (int)$value : null;

        // selected_trailer_type_id = trailers.type_id
        $this->selected_trailer_type_id = $this->trailer_id
            ? (int) Trailer::whereKey($this->trailer_id)->value('type_id')
            : null;

        // если НЕ контейнер — чистим поля
        if (!$this->isContainerTrailer) {
            $this->cont_nr = null;
            $this->seal_nr = null;
        }
    }

    public function getTrailerTypeMetaProperty(): ?array
    {
        if (!$this->selected_trailer_type_id) return null;

        $types  = config('trailer-types.types', []);   // [1=>'cargo',2=>'container',3=>'ref']
        $labels = config('trailer-types.labels', []);
        $icons  = config('trailer-types.icons', []);

        $key = $types[$this->selected_trailer_type_id] ?? null;
        if (!$key) return null;

        return [
            'id'    => $this->selected_trailer_type_id,
            'key'   => $key,
            'label' => $labels[$key] ?? $key,
            'icon'  => $icons[$key] ?? '🚚',
        ];
    }
}
