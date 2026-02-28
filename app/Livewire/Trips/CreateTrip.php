<?php

namespace App\Livewire\Trips;

use Livewire\Component;
use App\Helpers\CalculateTax;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use App\Models\Company;
use App\Models\TripExpense;
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
     *  EXPEDITOR + CARRIER LOGIC
     * ============================================================ */
    public ?int $expeditor_id = null;
    public array $expeditorData = [];

    /** Company.type of expeditor (forwarder / expeditor / carrier / mixed / etc.) */
    public ?string $expeditor_type = null;

    /**
     * trips.carrier_company_id (кто выполняет рейс)
     * - если expeditor не посредник => carrier = expeditor
     * - если expeditor посредник => выбираем carrier (внутренний или third party)
     */
    public ?int $company_id = null;

    public bool $needsCarrierSelect = false;

    /** internal carriers list */
    public $carrierCompanies = [];

    /** banks decoded from companies.banks_json */
    public array $banks = [];
    public ?string $bank_index = null;

    public array $payers = [];
    public array $taxRates = [0, 5, 10, 21];

    /** ============================================================
     *  TRANSPORT
     * ============================================================ */
    public ?int $driver_id = null;
    public ?int $truck_id = null;

    public ?int $trailer_id = null;
    public ?int $selected_trailer_type_id = null;

    public ?string $cont_nr = null;
    public ?string $seal_nr = null;

    public $drivers = [];
    public $trucks = [];
    public $trailers = [];

    /**
     * Carrier UI select:
     * '' | numeric string company id | '__third_party__'
     */
    public string $carrier_company_select = '';

    /** ============================================================
     *  THIRD PARTY INPUTS
     * ============================================================ */
    public ?string $third_party_name = null;
    public ?string $third_party_country = null;
    public ?string $third_party_reg_nr = null;

    // third party truck (минимум plate)
    public ?string $third_party_truck_plate = null;
    public ?string $third_party_truck_brand = null;
    public ?string $third_party_truck_model = null;
    public ?int    $third_party_truck_year = null;

    // third party trailer (опционально, но лучше plate)
    public ?string $third_party_trailer_plate = null;
    public ?string $third_party_trailer_brand = null;
    public ?int    $third_party_trailer_type_id = null;
    public ?int    $third_party_trailer_year = null;
    public ?string $third_party_trailer_vin = null;

    // фиксированная сумма, которую Рона платит third party
    public ?string $third_party_price = null; // строка (нормализуем в float)

    /** ============================================================
     *  TRIP (currency можно не светить, но пусть будет всегда EUR)
     * ============================================================ */
    public string $currency = 'EUR';
    public $start_date;
    public $end_date;
    public string $status = 'planned';

    public bool $customs = false;
    public ?string $customs_address = null;

    /** ============================================================
     *  STEPS
     * ============================================================ */
    public array $steps = [];
    public array $stepCities = [];

    /** ============================================================
     *  CARGOS
     * ============================================================ */
    public array $cargos = [];

    /** ============================================================
     *  MOUNT
     * ============================================================ */
    public function mount(): void
    {
        $this->drivers  = Driver::where('is_active', 1)->get();

        // показываем весь транспорт (как у тебя было).
        // позже можно фильтровать по carrier_company_id.
        $this->trucks   = Truck::where('is_active', 1)->get();
        $this->trailers = Trailer::where('is_active', 1)->get();

        $this->payers = config('payers', []);

        // внутренние компании-перевозчики (Lakna/Padex)
        $this->carrierCompanies = Company::query()
            ->where('is_active', 1)
            ->whereIn('type', ['carrier', 'mixed', 'forwarder']) // безопасно (у тебя типы гуляли)
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        $this->updatedTrailerId($this->trailer_id);

        $this->addStep();
        $this->addCargo();
    }

    /** ============================================================
     *  EXPEDITOR (DB + banks_json)
     * ============================================================ */
    protected function decodeBanksJson(mixed $banksJson): array
    {
        if (empty($banksJson)) return [];
        if (is_array($banksJson)) return $banksJson;

        $str = (string) $banksJson;

        $decoded = json_decode($str, true);
        if (is_array($decoded)) return $decoded;

        $decoded2 = json_decode(trim($str, "\""), true);

        return is_array($decoded2) ? $decoded2 : [];
    }

    protected function resetExpeditorState(): void
    {
        $this->expeditorData = [];
        $this->banks = [];
        $this->bank_index = null;

        $this->expeditor_type = null;
        $this->needsCarrierSelect = false;
    }

    protected function resetThirdPartyState(): void
    {
        $this->third_party_name = null;
        $this->third_party_country = null;
        $this->third_party_reg_nr = null;

        $this->third_party_truck_plate = null;
        $this->third_party_truck_brand = null;
        $this->third_party_truck_model = null;
        $this->third_party_truck_year = null;

        $this->third_party_trailer_plate = null;
        $this->third_party_trailer_brand = null;
        $this->third_party_trailer_type_id = null;
        $this->third_party_trailer_year = null;
        $this->third_party_trailer_vin = null;

        $this->third_party_price = null;
    }

    protected function hydrateExpeditor(): void
    {
        $id = (int) ($this->expeditor_id ?? 0);

        $this->resetExpeditorState();

        if (!$id) {
            $this->company_id = null;
            $this->carrier_company_select = '';
            $this->resetThirdPartyState();
            return;
        }

        $exp = Company::query()->find($id);
        if (!$exp) return;

        $this->expeditor_type = $exp->type ?? null;

        $this->banks = $this->decodeBanksJson($exp->banks_json);
        $this->bank_index = null;

        $this->expeditorData = [
            'name'      => $exp->name ?? null,
            'reg_nr'    => $exp->reg_nr ?? null,
            'country'   => $exp->country ?? null,
            'city'      => $exp->city ?? null,
            'address'   => $exp->address ?? null,
            'post_code' => $exp->post_code ?? null,
            'email'     => $exp->email ?? null,
            'phone'     => $exp->phone ?? null,
            'bank'      => null,
            'iban'      => null,
            'bic'       => null,
        ];

        // посредник: company.type == 'expeditor'
        $this->needsCarrierSelect = ($this->expeditor_type === 'expeditor');

        if (!$this->needsCarrierSelect) {
            // обычный кейс: expeditor = carrier
            $this->company_id = (int) $exp->id;
            $this->carrier_company_select = (string) $this->company_id;
            $this->resetThirdPartyState();
        } else {
            // посредник: нужно выбрать перевозчика
            $this->company_id = null;
            $this->carrier_company_select = '';
        }
    }

    protected function hydrateBank(): void
    {
        if ($this->bank_index === null || $this->bank_index === '') {
            $this->expeditorData['bank'] = null;
            $this->expeditorData['iban'] = null;
            $this->expeditorData['bic']  = null;
            return;
        }

        $key = (string) $this->bank_index;

        if (!array_key_exists($key, $this->banks)) {
            $this->expeditorData['bank'] = null;
            $this->expeditorData['iban'] = null;
            $this->expeditorData['bic']  = null;
            return;
        }

        $bank = $this->banks[$key];

        $this->expeditorData['bank'] = $bank['name'] ?? null;
        $this->expeditorData['iban'] = $bank['iban'] ?? null;
        $this->expeditorData['bic']  = $bank['bic']  ?? null;
    }

    public function updatedExpeditorId($value): void
    {
        $this->expeditor_id = $value ? (int) $value : null;
        $this->hydrateExpeditor();
    }

    public function updatedBankIndex($value = null): void
    {
        $this->bank_index = ($value === '' || $value === null) ? null : (string) $value;

        if (empty($this->expeditorData)) {
            $this->expeditorData = [
                'name' => null, 'reg_nr' => null, 'country' => null, 'city' => null,
                'address' => null, 'post_code' => null, 'email' => null, 'phone' => null,
                'bank' => null, 'iban' => null, 'bic' => null,
            ];
        }

        $this->hydrateBank();
    }

    /**
     * Carrier select changed:
     * - '' => company_id = null
     * - '__third_party__' => company_id = null (создадим на save)
     * - '123' => company_id = 123
     */
    public function updatedCarrierCompanySelect($value): void
    {
        $this->carrier_company_select = (string) ($value ?? '');

        if (!$this->needsCarrierSelect) {
            if ($this->expeditor_id) {
                $this->company_id = (int) $this->expeditor_id;
                $this->carrier_company_select = (string) $this->company_id;
            }
            return;
        }

        if ($this->carrier_company_select === '__third_party__') {
            $this->company_id = null;
            return;
        }

        if ($this->carrier_company_select === '') {
            $this->company_id = null;
            $this->resetThirdPartyState();
            return;
        }

        if (ctype_digit($this->carrier_company_select)) {
            $this->company_id = (int) $this->carrier_company_select;
            $this->resetThirdPartyState();
            return;
        }

        $this->company_id = null;
        $this->carrier_company_select = '';
    }

    /** ============================================================
     *  HELPERS: NUM NORMALIZATION
     * ============================================================ */
    private function normNumString($v): ?string
    {
        if ($v === null) return null;
        if ($v === '') return null;

        $v = (string) $v;
        $v = str_replace(["\xc2\xa0", ' '], '', $v);
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

            $this->cargos[$ci]['tax_percent'] =
                $this->normNumString($cargo['tax_percent'] ?? null) ?? ($cargo['tax_percent'] ?? null);

            $this->cargos[$ci]['commercial_invoice_amount'] =
                $this->normNumString($cargo['commercial_invoice_amount'] ?? null);

            foreach (($cargo['items'] ?? []) as $ii => $item) {
                foreach (['packages','pallets','units','gross_weight','net_weight','tonnes','volume','loading_meters'] as $f) {
                    $this->cargos[$ci]['items'][$ii][$f] = $this->normNumString($item[$f] ?? null);
                }
            }
        }

        $this->third_party_price = $this->normNumString($this->third_party_price);
    }

    /** ============================================================
     *  STEPS
     * ============================================================ */
    public function addStep(): void
    {
        $this->steps[] = [
            'uid'        => (string) Str::uuid(),
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
        $parts = explode('.', $key);
        $stepIndex = (int)($parts[0] ?? 0);
        $field = $parts[1] ?? null;

        if ($field === 'country_id') {
            $this->stepCities[$stepIndex]['cities'] = getCitiesByCountryId((int)$value) ?? [];
            $this->steps[$stepIndex]['city_id'] = null;
        }
    }

    private function normalizeStepSelectionsToUids(): void
    {
        foreach ($this->cargos as $ci => $cargo) {
            foreach (['loading_step_ids', 'unloading_step_ids'] as $field) {
                $vals = $cargo[$field] ?? [];
                $out = [];

                foreach ($vals as $v) {
                    if (is_numeric($v)) {
                        $idx = (int)$v;
                        $uid = $this->steps[$idx]['uid'] ?? null;
                        if ($uid) $out[] = (string)$uid;
                        continue;
                    }
                    if ($v !== null && $v !== '') {
                        $out[] = (string)$v;
                    }
                }

                $out = array_values(array_unique($out));
                $this->cargos[$ci][$field] = $out;
            }
        }
    }

    private function stepPositionByToken($token): ?int
    {
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

            'loading_step_ids'   => [],
            'unloading_step_ids' => [],

            'price'            => '',
            'tax_percent'      => 21,
            'total_tax_amount' => 0,
            'price_with_tax'   => 0,
            'currency'         => 'EUR',
            'payment_terms'    => null,
            'payer_type_id'    => null,

            'commercial_invoice_nr'     => null,
            'commercial_invoice_amount' => null,

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

    public function updatedCustoms($value): void
    {
        $this->customs = (bool) $value;
        if (!$this->customs) {
            $this->customs_address = null;
        }
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
     *  THIRD PARTY CREATE (Company + Truck + Trailer + Expense)
     * ============================================================ */
    private function ensureThirdPartyCarrierCompany(): Company
    {
        $name = trim((string)($this->third_party_name ?? ''));

        $existing = Company::query()
            ->where('is_active', 1)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($existing) {
            return $existing;
        }

        $slug = Str::slug($name) ?: 'third-party';
        $base = $slug;
        $i = 2;

        while (Company::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return Company::create([
            'slug'       => $slug,
            'name'       => $name,
            'type'       => 'carrier', // ✅ third party это перевозчик
            'reg_nr'     => $this->third_party_reg_nr,
            'country'    => $this->third_party_country,

            // если добавил флаг is_third_party миграцией — отлично:
            'is_third_party' => true,

            'is_system'  => 0,
            'is_active'  => 1,
        ]);
    }

    private function ensureThirdPartyTruck(int $companyId): Truck
    {
        $plate = trim((string)($this->third_party_truck_plate ?? ''));

        $existing = Truck::query()
            ->where('company_id', $companyId)
            ->whereRaw('LOWER(plate) = ?', [mb_strtolower($plate)])
            ->first();

        if ($existing) return $existing;

        return Truck::create([
            'company_id'    => $companyId,
            'plate'         => $plate,
            'brand'         => $this->third_party_truck_brand,
            'model'         => $this->third_party_truck_model,
            'year'          => $this->third_party_truck_year ?? date('Y'),
            'can_available' => 0,
            'status'        => 1,
            'is_active'     => 1,
        ]);
    }

    private function ensureThirdPartyTrailer(int $companyId): ?Trailer
    {
        $plate = trim((string)($this->third_party_trailer_plate ?? ''));

        if ($plate === '') {
            return null; // прицеп можно не задавать
        }

        $existing = Trailer::query()
            ->where('company_id', $companyId)
            ->whereRaw('LOWER(plate) = ?', [mb_strtolower($plate)])
            ->first();

        if ($existing) return $existing;

        return Trailer::create([
            'company_id' => $companyId,
            'plate'      => $plate,
            'brand'      => $this->third_party_trailer_brand,
            'type_id'    => $this->third_party_trailer_type_id ?? 1,
            'year'       => $this->third_party_trailer_year ?? date('Y'),
            'vin'        => $this->third_party_trailer_vin,
            'status'     => 1,
            'is_active'  => 1,
        ]);
    }

    /** ============================================================
     *  SAVE
     * ============================================================ */
    public function save(): void
    {
        $this->normalizeInputsForValidation();
        $this->normalizeStepSelectionsToUids();

        // currency всегда EUR
        $this->currency = 'EUR';

        // expeditor != посредник => carrier = expeditor
        if (!$this->needsCarrierSelect && $this->expeditor_id) {
            $this->company_id = (int) $this->expeditor_id;
            $this->carrier_company_select = (string) $this->company_id;
        }

        // посредник: синхронизируем company_id если выбран внутренний перевозчик
        if ($this->needsCarrierSelect) {
            if ($this->carrier_company_select === '__third_party__') {
                $this->company_id = null; // создадим после валидации
            } elseif (ctype_digit((string)$this->carrier_company_select)) {
                $this->company_id = (int) $this->carrier_company_select;
            } else {
                $this->company_id = null;
            }
        }

        $isThirdPartyFlow = $this->needsCarrierSelect && $this->carrier_company_select === '__third_party__';

        $rules = [
            'expeditor_id' => 'required|integer|exists:companies,id',
            'bank_index'   => empty($this->banks) ? 'nullable' : 'required',

            'carrier_company_select' => $this->needsCarrierSelect ? 'required' : 'nullable',

            'company_id' => ($this->needsCarrierSelect && !$isThirdPartyFlow)
                ? 'required|integer|exists:companies,id'
                : 'nullable|integer|exists:companies,id',

            // third party company
            'third_party_name' => $isThirdPartyFlow ? 'required|string|max:255' : 'nullable|string|max:255',
            'third_party_country' => 'nullable|string|max:191',
            'third_party_reg_nr' => 'nullable|string|max:191',

            // third party truck/trailer
            'third_party_truck_plate' => $isThirdPartyFlow ? 'required|string|max:191' : 'nullable|string|max:191',
            'third_party_trailer_plate' => 'nullable|string|max:191',

            // third party price
            'third_party_price' => $isThirdPartyFlow ? 'required|numeric|min:0' : 'nullable|numeric|min:0',

            // transport (обычный)
            'driver_id'  => 'required|integer|exists:drivers,id',
            'truck_id'   => $isThirdPartyFlow ? 'nullable' : 'required|integer|exists:trucks,id',
            'trailer_id' => 'nullable|integer|exists:trailers,id',

            'start_date' => 'required|date',
            'end_date'   => 'required|date',

            'cont_nr' => 'nullable|string|max:50',
            'seal_nr' => 'nullable|string|max:50',

            'customs'         => 'nullable|boolean',
            'customs_address' => $this->customs ? 'required|string|max:255' : 'nullable|string|max:255',

            // steps
            'steps'              => 'required|array|min:1',
            'steps.*.type'       => 'required|string|in:loading,unloading',
            'steps.*.country_id' => 'required|integer',
            'steps.*.city_id'    => 'required|integer',
            'steps.*.address'    => 'required|string',
            'steps.*.date'       => 'required|date',
            'steps.*.time'       => 'nullable',
            'steps.*.order'      => 'required|integer',

            // cargos
            'cargos'                      => 'required|array|min:1',
            'cargos.*.customer_id'        => 'required|integer',
            'cargos.*.shipper_id'         => 'required|integer',
            'cargos.*.consignee_id'       => 'required|integer',
            'cargos.*.loading_step_ids'   => 'required|array|min:1',
            'cargos.*.unloading_step_ids' => 'required|array|min:1',
            'cargos.*.price'              => 'required|numeric',
            'cargos.*.tax_percent'        => 'required|numeric',

            'cargos.*.commercial_invoice_nr'     => 'nullable|string|max:64',
            'cargos.*.commercial_invoice_amount' => 'nullable|numeric|min:0',

            'cargos.*.items'               => 'required|array|min:1',
            'cargos.*.items.*.customs_code'=> 'nullable|string|max:32',
            'cargos.*.items.*.description' => 'nullable|string|max:255',
        ];

        $messages = [
            'bank_index.required'             => 'Выберите банковский счёт экспедитора.',
            'carrier_company_select.required' => 'Выберите перевозчика (или “Третья сторона”).',
            'company_id.required'             => 'Выберите перевозчика (внутреннюю компанию).',
            'third_party_name.required'       => 'Введите название третьей стороны.',
            'third_party_truck_plate.required'=> 'Введите номер тягача третьей стороны.',
            'third_party_price.required'      => 'Введите сумму, которую платим третьей стороне.',
            'cargos.*.loading_step_ids.required'   => 'Выберите хотя бы один шаг погрузки.',
            'cargos.*.unloading_step_ids.required' => 'Выберите хотя бы один шаг разгрузки.',
        ];

        $data = [
            'expeditor_id' => $this->expeditor_id,
            'bank_index'   => $this->bank_index,

            'carrier_company_select' => $this->carrier_company_select,
            'company_id'             => $this->company_id,

            'third_party_name'    => $this->third_party_name,
            'third_party_country' => $this->third_party_country,
            'third_party_reg_nr'  => $this->third_party_reg_nr,

            'third_party_truck_plate'  => $this->third_party_truck_plate,
            'third_party_trailer_plate'=> $this->third_party_trailer_plate,
            'third_party_price'        => $this->third_party_price,

            'driver_id'  => $this->driver_id,
            'truck_id'   => $this->truck_id,
            'trailer_id' => $this->trailer_id,

            'start_date' => $this->start_date,
            'end_date'   => $this->end_date,

            'customs'         => $this->customs,
            'customs_address' => $this->customs_address,

            'steps'  => $this->steps,
            'cargos' => $this->cargos,

            'cont_nr' => $this->cont_nr,
            'seal_nr' => $this->seal_nr,
        ];

        $validator = Validator::make($data, $rules, $messages);

        $validator->after(function ($validator) use ($isThirdPartyFlow) {
            // если выбрали внутреннего перевозчика — можно ограничить типы
            if ($this->needsCarrierSelect && !$isThirdPartyFlow && $this->company_id) {
                $type = Company::whereKey($this->company_id)->value('type');
                if (!in_array($type, ['carrier', 'mixed', 'forwarder'], true)) {
                    $validator->errors()->add('company_id', 'Некорректный тип компании перевозчика.');
                }
            }

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

        // unloading after loading
        foreach ($this->cargos as $ci => $c) {
            foreach (($c['loading_step_ids'] ?? []) as $lToken) {
                foreach (($c['unloading_step_ids'] ?? []) as $uToken) {
                    $lPos = $this->stepPositionByToken($lToken);
                    $uPos = $this->stepPositionByToken($uToken);

                    if ($lPos === null || $uPos === null) {
                        $this->addError("cargos.$ci.unloading_step_ids", 'Выбраны некорректные шаги (обновите страницу и попробуйте снова).');
                        return;
                    }

                    if ($uPos <= $lPos) {
                        $this->addError("cargos.$ci.unloading_step_ids", 'Разгрузки должны быть ПОСЛЕ всех погрузок.');
                        return;
                    }
                }
            }
        }

        DB::beginTransaction();

        try {
            $thirdPartyCompany = null;
            $thirdPartyTruck = null;
            $thirdPartyTrailer = null;

            // third party flow: create company + truck + trailer + set ids
            if ($isThirdPartyFlow) {
                $thirdPartyCompany = $this->ensureThirdPartyCarrierCompany();
                $this->company_id = (int) $thirdPartyCompany->id;

                $thirdPartyTruck = $this->ensureThirdPartyTruck($this->company_id);
                $this->truck_id = (int) $thirdPartyTruck->id;

                $thirdPartyTrailer = $this->ensureThirdPartyTrailer($this->company_id);
                $this->trailer_id = $thirdPartyTrailer ? (int) $thirdPartyTrailer->id : null;

                // чтобы контейнерные поля корректно повели себя
                $this->updatedTrailerId($this->trailer_id);
            }

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

                'customs'         => (bool) $this->customs,
                'customs_address' => $this->customs ? $this->customs_address : null,

                'carrier_company_id' => $this->company_id,

                'expeditor_bank_id' => $this->bank_index !== null ? (int) $this->bank_index : null,
                'expeditor_bank'    => $this->expeditorData['bank'] ?? null,
                'expeditor_iban'    => $this->expeditorData['iban'] ?? null,
                'expeditor_bic'     => $this->expeditorData['bic']  ?? null,

                'driver_id'  => $this->driver_id,
                'truck_id'   => $this->truck_id,
                'trailer_id' => $this->trailer_id,

                'start_date' => $this->start_date,
                'end_date'   => $this->end_date,

                'currency'   => 'EUR',
                'status'     => $this->status,

                'cont_nr'    => $this->cont_nr,
                'seal_nr'    => $this->seal_nr,
            ]);

            // Если third party — сохраняем фиксированную оплату как TripExpense
            if ($isThirdPartyFlow) {
                $amount = $this->toFloat($this->third_party_price, 0.0);

                TripExpense::create([
                    'trip_id'   => $trip->id,
                    'category'  => 'subcontractor', // добавим в enum позже
                    'description' => 'Оплата третьей стороне: ' . ($thirdPartyCompany->name ?? ''),
                    'amount'    => $amount,
                    'currency'  => 'EUR',
                    'expense_date' => $this->start_date,
                    // supplier_company_id => $thirdPartyCompany->id (если добавишь колонку)
                ]);
            }

            // uid -> db id
            $stepUidToId = [];

            foreach ($this->steps as $i => $s) {
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

            foreach ($this->cargos as $cargoData) {
                $price = $this->toFloat($cargoData['price'] ?? null, 0.0);
                $taxPercent = $this->toFloat($cargoData['tax_percent'] ?? null, 0.0);

                $tax = CalculateTax::calculate($price, $taxPercent);

                $commercialInvoiceAmountRaw = $cargoData['commercial_invoice_amount'] ?? null;
                $commercialInvoiceAmount = ($commercialInvoiceAmountRaw !== null && $commercialInvoiceAmountRaw !== '')
                    ? $this->toFloat($commercialInvoiceAmountRaw, 0.0)
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

                    'currency'      => 'EUR',
                    'payment_terms' => $cargoData['payment_terms'] ?? null,
                    'payer_type_id' => $cargoData['payer_type_id'] ?? null,

                    'commercial_invoice_nr'      => $cargoData['commercial_invoice_nr'] ?? null,
                    'commercial_invoice_amount'  => $commercialInvoiceAmount,
                ]);

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

                // pivot steps
                $pivot = [];

                foreach (($cargoData['loading_step_ids'] ?? []) as $token) {
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

            $this->redirectRoute('trips.show', $trip->id);
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
        $expeditors = Company::query()
            ->where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        return view('livewire.trips.create-trip', [
            'clients'            => Client::orderBy('company_name')->get(),
            'countries'          => config('countries', []),
            'expeditors'         => $expeditors,
            'carrierCompanies'   => $this->carrierCompanies,
            'needsCarrierSelect' => $this->needsCarrierSelect,
            'payers'             => $this->payers,
            'taxRates'           => $this->taxRates,
        ])->layout('layouts.app', [
            'title' => 'Create trip',
        ]);
    }

    /** ============================================================
     *  TRAILER TYPE HELPERS
     * ============================================================ */
    private function containerTypeId(): int
    {
        $types = config('trailer-types.types', []);
        $id = array_search('container', $types, true);
        return $id ?: 2;
    }

    public function getIsContainerTrailerProperty(): bool
    {
        return (int)($this->selected_trailer_type_id ?? 0) === (int)$this->containerTypeId();
    }

    public function updatedTrailerId($value): void
    {
        $this->trailer_id = $value ? (int)$value : null;

        $this->selected_trailer_type_id = $this->trailer_id
            ? (int) Trailer::whereKey($this->trailer_id)->value('type_id')
            : null;

        if (!$this->isContainerTrailer) {
            $this->cont_nr = null;
            $this->seal_nr = null;
        }
    }

    public function getTrailerTypeMetaProperty(): ?array
    {
        if (!$this->selected_trailer_type_id) return null;

        $types  = config('trailer-types.types', []);
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
