{{-- resources/views/livewire/trips/create-trip.blade.php --}}

@php
    /**
     * ============================================================
     *  SAFE UI HELPERS / DEFAULTS
     * ============================================================
     */

    // safe blank checker
    $isBlank = $isBlank ?? function ($v) {
        if ($v === null) return true;
        if (is_string($v) && trim($v) === '') return true;
        if (is_array($v) && count($v) === 0) return true;
        return false;
    };

    // required badge
    $reqBadge = $reqBadge ?? function () {
        return '<span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-semibold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-200">required</span>';
    };

    // inputs
    $baseInput = $baseInput ?? 'w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400';
    $warnInput = $warnInput ?? 'border-amber-400 focus:ring-amber-300';
    $errInput  = $errInput  ?? 'border-red-500 focus:ring-red-300';

    // badges
    $badgeError = $badgeError ?? 'inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-200';
    $badgeWarn  = $badgeWarn  ?? 'inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-200';

    // data safety
    $expeditors        = $expeditors        ?? collect();
    $carrierCompanies  = $carrierCompanies  ?? collect();
    $banks             = $banks             ?? [];
    $expeditorData     = $expeditorData     ?? [];
    $taxRates          = $taxRates          ?? [0, 5, 10, 21];
    $payers            = $payers            ?? [];

    $drivers           = $drivers           ?? [];
    $trucks            = $trucks            ?? [];
    $trailers          = $trailers          ?? [];
    $clients           = $clients           ?? collect();
    $countries         = $countries         ?? [];

    $steps             = $steps             ?? [];
    $stepCities        = $stepCities        ?? [];
    $cargos            = $cargos            ?? [];

    // keys
    $kExp  = 'expeditor_id';
    $kBank = 'bank_index';

    // carrier logic flags (from component)
    $needsCarrier = (bool)($needsCarrierSelect ?? false);

    // values (from component)
    $carrier_company_select = $carrier_company_select ?? '';
    $thirdPartySelected     = ($carrier_company_select === '__third_party__');

    // bank required: if expeditor has banks -> required, else optional
    $bankRequired = $bankRequired ?? (!empty($banks));

    // warnings for exp/bank/carrier
    $expWarn  = $expWarn  ?? ($isBlank($expeditor_id ?? null) && !$errors->has($kExp));
    $bankWarn = $bankWarn ?? (($bankRequired && $isBlank($bank_index ?? null)) && !$errors->has($kBank));

    $kCarrier = 'company_id';
    $carrierWarn = ($needsCarrier && $isBlank($carrier_company_select) && !$errors->has($kCarrier));

    // third party keys + values
$kThirdName    = 'third_party_name';
$kThirdTruck   = 'third_party_truck_plate';
$kThirdTrailer = 'third_party_trailer_plate';
$kThirdFreight = 'third_party_freight_price';

$third_party_name          = $third_party_name ?? null;
$third_party_truck_plate   = $third_party_truck_plate ?? null;
$third_party_trailer_plate = $third_party_trailer_plate ?? null;
$third_party_freight_price = $third_party_freight_price ?? null;

$thirdPartyNameWarn    = ($thirdPartySelected && $isBlank($third_party_name) && !$errors->has($kThirdName));
$thirdPartyTruckWarn   = ($thirdPartySelected && $isBlank($third_party_truck_plate) && !$errors->has($kThirdTruck));
$thirdPartyFreightWarn = ($thirdPartySelected && $isBlank($third_party_freight_price) && !$errors->has($kThirdFreight));

    $hasErrors =
        $errors->has($kExp) || $errors->has($kBank) || $errors->has($kCarrier) ||
        $errors->has($kThirdName) || $errors->has($kThirdTruck) || $errors->has($kThirdTrailer) ||
        $errors->has($kThirdFreight);

    $hasWarns =
        $expWarn || $bankWarn || ($needsCarrier && $carrierWarn) ||
        $thirdPartyNameWarn || $thirdPartyTruckWarn || $thirdPartyFreightWarn;
@endphp

<div class="min-h-screen pb-24 bg-gradient-to-b from-gray-50 via-gray-100 to-gray-100 dark:from-gray-950 dark:via-gray-900 dark:to-gray-900">

    {{-- =========================
         HEADER
    ========================== --}}
    <div class="sticky top-0 z-20 bg-white/85 dark:bg-gray-900/80 border-b border-blue-100 dark:border-blue-900/40 backdrop-blur">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
            <h1 class="text-lg sm:text-2xl font-semibold text-gray-800 dark:text-gray-100 truncate">
                ➕ Создание рейса (multi-cargo)
            </h1>

            <button
                wire:click="save"
                wire:loading.attr="disabled"
                class="hidden sm:inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold
                       bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white shadow">
                <span wire:loading.remove>💾 Создать рейс</span>
                <span wire:loading>⏳ Сохранение...</span>
            </button>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-3 sm:px-4 pt-4 space-y-6">

        {{-- =========================
             GLOBAL MESSAGES / ERRORS
        ========================== --}}
        @if (session('success'))
            <div class="bg-green-50 border border-green-300 text-green-800 rounded-xl px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border border-red-300 text-red-800 rounded-xl px-4 py-3 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @if (!empty($successMessage))
            <div class="bg-green-50 border border-green-300 text-green-800 rounded-xl px-4 py-3 text-sm">
                {{ $successMessage }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-300 text-red-800 rounded-xl px-4 py-3 text-sm">
                <div class="font-semibold mb-1">Ошибки:</div>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @error('error')
            <div class="bg-red-50 border border-red-300 text-red-800 rounded-xl px-4 py-3 text-sm">
                {{ $message }}
            </div>
        @enderror

        {{-- =========================
             EXPEDITOR + CARRIER (+ THIRD PARTY)
        ========================== --}}
       <section class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm px-4 py-4 sm:px-6 sm:py-5 space-y-4 border border-gray-100 dark:border-gray-800">

    <div class="flex items-center justify-between gap-2">
        <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100">
            🧾 Экспедитор
        </h2>

        @if($hasErrors)
            <span class="{{ $badgeError }}">Ошибки</span>
        @elseif($hasWarns)
            <span class="{{ $badgeWarn }}">Не заполнено</span>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-start">

        {{-- LEFT COLUMN --}}
        <div class="space-y-3">

            {{-- EXPEDITOR --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                    Выберите экспедитора {!! $reqBadge() !!}
                </label>

                <select
                    wire:model.live="expeditor_id"
                    @class([$baseInput, $warnInput => $expWarn, $errInput => $errors->has($kExp), 'input-error' => $errors->has($kExp)])
                >
                    <option value="">— выберите экспедитора —</option>
                    @foreach($expeditors as $exp)
                        <option value="{{ $exp->id }}">
                            {{ $exp->name }}@if(!empty($exp->type)) — {{ $exp->type }}@endif
                        </option>
                    @endforeach
                </select>

                @error('expeditor_id')
                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- BANK --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                    Банковский счёт {!! $bankRequired ? $reqBadge() : '' !!}
                </label>

                <select
                    wire:model.live="bank_index"
                    @class([$baseInput, $warnInput => $bankWarn, $errInput => $errors->has($kBank), 'input-error' => $errors->has($kBank)])
                >
                    <option value="">— выберите банк —</option>
                    @foreach(($banks ?? []) as $idx => $bank)
                        <option value="{{ (string)$idx }}">
                            {{ $bank['name'] ?? ('Bank #'.$idx) }}
                        </option>
                    @endforeach
                </select>

                @error('bank_index')
                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                @enderror

                @if(!$bankRequired)
                    <div class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                        Если у экспедитора нет банковских реквизитов — можно оставить пустым.
                    </div>
                @endif
            </div>

            {{-- CARRIER SELECT --}}
            @if($needsCarrier)
                <div class="pt-2 border-t border-gray-200/70 dark:border-gray-700/70 space-y-2">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Перевозчик (carrier) {!! $reqBadge() !!}
                    </label>

                    <select
                        wire:model.live="carrier_company_select"
                        @class([$baseInput, $warnInput => $carrierWarn, $errInput => $errors->has($kCarrier), 'input-error' => $errors->has($kCarrier)])
                    >
                        <option value="">— выберите перевозчика —</option>
                        <option value="__third_party__">➕ Третья сторона</option>

                        @foreach(($carrierCompanies ?? []) as $c)
                            <option value="{{ $c->id }}">
                                {{ $c->name }}@if(!empty($c->type)) — {{ $c->type }}@endif
                            </option>
                        @endforeach
                    </select>

                    @error('company_id')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror

                    @if(!$thirdPartySelected)
                        <div class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                            Экспедитор-посредник не является перевозчиком. Выберите компанию, которая выполняет рейс.
                        </div>
                    @endif
                </div>
            @else
                {{-- when forwarder => carrier auto --}}
                @if(!empty($expeditor_id))
                    <div class="pt-2 border-t border-gray-200/70 dark:border-gray-700/70">
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">
                            Перевозчик:
                            <span class="font-semibold">{{ $expeditorData['name'] ?? '—' }}</span>
                            (auto, т.к. выбран forwarder)
                        </div>
                    </div>
                @endif
            @endif

        </div>

        {{-- RIGHT INFO CARD --}}
        <div class="sm:col-span-2">
            <div class="rounded-2xl px-4 py-3 text-xs sm:text-sm space-y-1.5 border
                        bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700">

                <div class="font-semibold text-gray-900 dark:text-gray-100 flex items-center justify-between gap-2">
                    <span class="truncate">{{ $expeditorData['name'] ?? '—' }}</span>
                    <span class="text-[10px] text-gray-500">
                        ID: {{ $expeditor_id ?? '—' }}
                    </span>
                </div>

                <div class="text-gray-700 dark:text-gray-200">
                    <div>Reg. Nr / VAT:
                        <span class="font-medium">{{ $expeditorData['reg_nr'] ?? '—' }}</span>
                    </div>
                    <div>Country / City:
                        <span class="font-medium">
                            {{ $expeditorData['country'] ?? '—' }}{{ !empty($expeditorData['city']) ? ', '.$expeditorData['city'] : '' }}
                        </span>
                    </div>
                    <div>Address:
                        <span class="font-medium">
                            {{ $expeditorData['address'] ?? '—' }}
                            @if(!empty($expeditorData['post_code']))
                                , {{ $expeditorData['post_code'] }}
                            @endif
                        </span>
                    </div>
                </div>

                <div class="text-gray-700 dark:text-gray-200 pt-1 border-t border-gray-200/70 dark:border-gray-700/70 mt-1">
                    <div>Phone: <span class="font-medium">{{ $expeditorData['phone'] ?? '—' }}</span></div>
                    <div>Email: <span class="font-medium">{{ $expeditorData['email'] ?? '—' }}</span></div>
                </div>

                <div class="text-gray-700 dark:text-gray-200 pt-1 border-t border-gray-200/70 dark:border-gray-700/70 mt-1">
                    <div>Bank: <span class="font-medium">{{ $expeditorData['bank'] ?? '—' }}</span></div>
                    <div>IBAN: <span class="font-medium">{{ $expeditorData['iban'] ?? '—' }}</span></div>
                    <div>BIC: <span class="font-medium">{{ $expeditorData['bic'] ?? '—' }}</span></div>
                </div>
            </div>
        </div>

        {{-- ✅ THIRD PARTY FULL WIDTH (UNDER COLUMNS) --}}
        @if($needsCarrier && $thirdPartySelected)
            <div class="sm:col-span-3">
                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4 space-y-3">

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">

                        {{-- Name --}}
                        <div class="sm:col-span-5 min-w-0">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                Название третьей стороны {!! $reqBadge() !!}
                            </label>

                            <input type="text"
                                   wire:model.defer="third_party_name"
                                   placeholder="Напр. SIA New Carrier"
                                   @class([$baseInput, $warnInput => $thirdPartyNameWarn, $errInput => $errors->has($kThirdName), 'input-error' => $errors->has($kThirdName)])>

                            @error('third_party_name')
                                <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Truck --}}
                        <div class="sm:col-span-3 min-w-0">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                Номер тягача {!! $reqBadge() !!}
                            </label>

                            <input type="text"
                                   wire:model.defer="third_party_truck_plate"
                                   placeholder="Напр. AB-1234"
                                   @class([$baseInput, $warnInput => $thirdPartyTruckWarn, $errInput => $errors->has($kThirdTruck), 'input-error' => $errors->has($kThirdTruck)])>

                            @error('third_party_truck_plate')
                                <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Trailer --}}
                        <div class="sm:col-span-2 min-w-0">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                Прицеп  {!! $reqBadge() !!}<span class="ml-2 text-[11px] text-gray-400">(опц.)</span>
                            </label>

                            <input type="text"
                                   wire:model.defer="third_party_trailer_plate"
                                   placeholder="Напр. XY-9876"
                                   @class([$baseInput, $errInput => $errors->has($kThirdTrailer), 'input-error' => $errors->has($kThirdTrailer)])>

                            @error('third_party_trailer_plate')
                                <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Freight --}}
                        <div class="sm:col-span-2 min-w-0">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                Фрахт (EUR) {!! $reqBadge() !!}
                            </label>

                            <div class="relative">
                                <input type="number"
                                       step="0.01"
                                       wire:model.defer="third_party_freight_price"
                                       placeholder="0.00"
                                       @class([$baseInput.' pr-10', $warnInput => $thirdPartyFreightWarn, $errInput => $errors->has($kThirdFreight), 'input-error' => $errors->has($kThirdFreight)])>

                                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs text-gray-400">
                                    EUR
                                </div>
                            </div>

                            @error('third_party_freight_price')
                                <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="text-[11px] text-gray-500 dark:text-gray-400">
                        Внешний перевозчик: укажите название, номера тягача/прицепа и фрахт (EUR), который экспедитор оплатит третьей стороне.
                    </div>
                </div>
            </div>
        @endif

    </div>
</section>

        {{-- =========================
             TRANSPORT
        ========================== --}}
        @if(!$thirdPartySelected)
        <section class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm px-4 py-4 sm:px-6 sm:py-5 space-y-4 border border-gray-100 dark:border-gray-800">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100">
                    🚚 Транспорт
                </h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Водитель {!! $reqBadge() !!}
                    </label>
                    <select wire:model.live="driver_id" class="{{ $baseInput }}">
                        <option value="">— выбрать —</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->first_name }} {{ $driver->last_name }}</option>
                        @endforeach
                    </select>
                    @error('driver_id') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Тягач {!! $reqBadge() !!}
                    </label>
                    <select wire:model.live="truck_id" class="{{ $baseInput }}">
                        <option value="">— выбрать —</option>
                        @foreach($trucks as $truck)
                            <option value="{{ $truck->id }}">{{ $truck->plate }} ({{ $truck->brand }} {{ $truck->model }})</option>
                        @endforeach
                    </select>
                    @error('truck_id') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Прицеп <span class="ml-2 text-[11px] text-gray-400">(опц.)</span>
                    </label>
                    <select wire:model.live="trailer_id" class="{{ $baseInput }}">
                        <option value="">— без прицепа —</option>
                        @foreach($trailers as $trailer)
                            <option value="{{ $trailer->id }}">{{ $trailer->plate }} ({{ $trailer->brand }})</option>
                        @endforeach
                    </select>
                    @error('trailer_id') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Дата начала {!! $reqBadge() !!}
                    </label>
                    <input type="date" wire:model.defer="start_date" class="{{ $baseInput }}">
                    @error('start_date') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Дата окончания {!! $reqBadge() !!}
                    </label>
                    <input type="date" wire:model.defer="end_date" class="{{ $baseInput }}">
                    @error('end_date') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Валюта {!! $reqBadge() !!}
                    </label>
                    <input type="text" wire:model.defer="currency" class="{{ $baseInput }}">
                    @error('currency') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>
            </div>
        </section>
        @endif

        {{-- =========================
             STEPS
        ========================== --}}
        <section class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm px-4 py-4 sm:px-6 sm:py-5 space-y-4 border border-gray-100 dark:border-gray-800">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100">
                    🧭 Маршрут (steps)
                </h2>

                <button type="button"
                        wire:click="addStep"
                        class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-sm">
                    ➕ Добавить шаг
                </button>
            </div>

            @forelse($steps as $index => $step)
                @php $stepKey = $step['uid'] ?? ($step['id'] ?? "step-$index"); @endphp

                <div class="border rounded-2xl overflow-hidden bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700"
                     wire:key="step-{{ $stepKey }}">
                    <div class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-gray-100 bg-white/40 dark:bg-gray-900/20">
                        Шаг #{{ $index + 1 }}
                    </div>

                    <div class="px-4 py-4 space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Тип {!! $reqBadge() !!}
                                </label>
                                <select wire:model.defer="steps.{{ $index }}.type" class="{{ $baseInput }}">
                                    <option value="loading">Погрузка</option>
                                    <option value="unloading">Разгрузка</option>
                                </select>
                                @error("steps.$index.type") <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Дата / время {!! $reqBadge() !!}
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="date" wire:model.defer="steps.{{ $index }}.date" class="{{ $baseInput }}">
                                    <input type="time" wire:model.defer="steps.{{ $index }}.time" class="{{ $baseInput }}">
                                </div>
                                @error("steps.$index.date") <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Order {!! $reqBadge() !!}
                                </label>
                                <input type="number" wire:model.defer="steps.{{ $index }}.order" class="{{ $baseInput }}">
                                @error("steps.$index.order") <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Страна {!! $reqBadge() !!}
                                </label>
                                <select wire:model.live="steps.{{ $index }}.country_id" class="{{ $baseInput }}">
                                    <option value="">— выбрать —</option>
                                    @foreach($countries as $countryId => $country)
                                        <option value="{{ $countryId }}">{{ $country['name'] }}</option>
                                    @endforeach
                                </select>
                                @error("steps.$index.country_id") <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Город {!! $reqBadge() !!}
                                </label>
                                <select wire:model.live="steps.{{ $index }}.city_id" class="{{ $baseInput }}">
                                    <option value="">— выбрать —</option>
                                    @foreach(($stepCities[$index]['cities'] ?? []) as $cityId => $city)
                                        <option value="{{ $cityId }}">{{ $city['name'] ?? ('#'.$cityId) }}</option>
                                    @endforeach
                                </select>
                                @error("steps.$index.city_id") <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Адрес {!! $reqBadge() !!}
                                </label>
                                <input type="text" wire:model.defer="steps.{{ $index }}.address" class="{{ $baseInput }}">
                                @error("steps.$index.address") <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            @if(count($steps) > 1)
                                <button type="button"
                                        wire:click="removeStep({{ $index }})"
                                        class="text-xs text-red-600 hover:text-red-700 px-3 py-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20">
                                    ✕ Удалить шаг
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-xs text-gray-500">
                    Пока нет ни одного шага. Нажмите «Добавить шаг».
                </div>
            @endforelse
        </section>

        {{-- CARGOS (оставлено как было, чтобы файл был полный) --}}
        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base sm:text-lg font-semibold text-gray-800 dark:text-gray-100">
                    📦 Грузы (multi-cargo)
                </h2>

                <button type="button"
                        wire:click="addCargo"
                        class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold
                               bg-green-600 hover:bg-green-700 text-white shadow-sm">
                    ➕ Добавить груз
                </button>
            </div>

            @forelse($cargos as $index => $cargo)
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800"
                     wire:key="cargo-{{ $cargo['uid'] ?? $index }}">

                    <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100 dark:border-gray-800">
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                            Груз #{{ $index + 1 }}
                        </div>

                        @if(count($cargos) > 1)
                            <button type="button"
                                    wire:click="removeCargo({{ $index }})"
                                    class="text-xs text-red-500 hover:text-red-600 px-2 py-1 rounded-lg hover:bg-red-50">
                                ✕ Удалить
                            </button>
                        @endif
                    </div>

                    <div class="px-4 py-4 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Заказчик {!! $reqBadge() !!}</label>
                                <select wire:model.live="cargos.{{ $index }}.customer_id" class="{{ $baseInput }}">
                                    <option value="">— выбрать —</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                    @endforeach
                                </select>
                                @error("cargos.$index.customer_id") <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Shipper {!! $reqBadge() !!}</label>
                                <select wire:model.live="cargos.{{ $index }}.shipper_id" class="{{ $baseInput }}">
                                    <option value="">— выбрать —</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                    @endforeach
                                </select>
                                @error("cargos.$index.shipper_id") <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Consignee {!! $reqBadge() !!}</label>
                                <select wire:model.live="cargos.{{ $index }}.consignee_id" class="{{ $baseInput }}">
                                    <option value="">— выбрать —</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                    @endforeach
                                </select>
                                @error("cargos.$index.consignee_id") <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-6 gap-3 border-t border-gray-100 dark:border-gray-800 pt-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Цена</label>
                                <input type="text" wire:model.live="cargos.{{ $index }}.price" class="{{ $baseInput }} text-xs">
                                @error("cargos.$index.price") <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">НДС %</label>
                                <select wire:model.live="cargos.{{ $index }}.tax_percent" class="{{ $baseInput }} text-xs">
                                    @foreach($taxRates as $rate)
                                        <option value="{{ $rate }}">{{ $rate }}</option>
                                    @endforeach
                                </select>
                                @error("cargos.$index.tax_percent") <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Сумма НДС</label>
                                <input type="number" wire:model.defer="cargos.{{ $index }}.total_tax_amount" class="{{ $baseInput }} text-xs" readonly>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Итого с НДС</label>
                                <input type="number" wire:model.defer="cargos.{{ $index }}.price_with_tax" class="{{ $baseInput }} text-xs" readonly>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Оплата до</label>
                                <input type="date" wire:model.defer="cargos.{{ $index }}.payment_terms" class="{{ $baseInput }} text-xs">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Плательщик</label>
                                <select wire:model.live="cargos.{{ $index }}.payer_type_id" class="{{ $baseInput }} text-xs">
                                    <option value="">— не выбрано —</option>
                                    @foreach($payers as $payerId => $payer)
                                        <option value="{{ $payerId }}">{{ $payer['label'] ?? $payerId }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            <button type="button"
                                    wire:click="addItem({{ $index }})"
                                    class="text-xs px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100">
                                ➕ Добавить позицию
                            </button>

                        </div>
                        {{-- =========================
     CARGO ITEMS (positions)
========================== --}}
<div class="space-y-3">
    @foreach(($cargo['items'] ?? []) as $itemIndex => $item)
        <div
            class="border border-gray-200 dark:border-gray-700 rounded-2xl p-3 bg-gray-50 dark:bg-gray-800"
            wire:key="cargo-{{ $cargo['uid'] ?? $index }}-item-{{ $item['uid'] ?? $itemIndex }}"
        >
            <div class="flex items-center justify-between mb-2">
                <div class="text-xs font-semibold text-gray-700 dark:text-gray-200">
                    Позиция #{{ $itemIndex + 1 }}
                </div>

                @if(count($cargo['items'] ?? []) > 1)
                    <button type="button"
                            wire:click="removeItem({{ $index }}, {{ $itemIndex }})"
                            class="text-xs text-red-600 hover:text-red-700 px-2 py-1 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20">
                        ✕ Удалить позицию
                    </button>
                @endif
            </div>

            {{-- Row 1 --}}
            <div class="grid grid-cols-1 sm:grid-cols-6 gap-3">
                <div class="sm:col-span-3">
                    <label class="block text-[11px] text-gray-500 mb-1">Описание</label>
                    <input type="text"
                           wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.description"
                           class="{{ $baseInput }} text-xs">
                    @error("cargos.$index.items.$itemIndex.description")
                        <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="sm:col-span-1">
                    <label class="block text-[11px] text-gray-500 mb-1">HS / Customs</label>
                    <input type="text"
                           wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.customs_code"
                           class="{{ $baseInput }} text-xs">
                    @error("cargos.$index.items.$itemIndex.customs_code")
                        <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-[11px] text-gray-500 mb-1">Packages</label>
                    <input type="number" step="1"
                           wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.packages"
                           class="{{ $baseInput }} text-xs">
                </div>

                <div>
                    <label class="block text-[11px] text-gray-500 mb-1">Pallets</label>
                    <input type="number" step="1"
                           wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.pallets"
                           class="{{ $baseInput }} text-xs">
                </div>
            </div>

            {{-- Row 2 --}}
            <div class="grid grid-cols-2 sm:grid-cols-6 gap-3 mt-3">
                <div>
                    <label class="block text-[11px] text-gray-500 mb-1">Units</label>
                    <input type="number" step="1"
                           wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.units"
                           class="{{ $baseInput }} text-xs">
                </div>

                <div>
                    <label class="block text-[11px] text-gray-500 mb-1">Net kg</label>
                    <input type="number" step="0.001"
                           wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.net_weight"
                           class="{{ $baseInput }} text-xs">
                </div>

                <div>
                    <label class="block text-[11px] text-gray-500 mb-1">Gross kg</label>
                    <input type="number" step="0.001"
                           wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.gross_weight"
                           class="{{ $baseInput }} text-xs">
                </div>

                <div>
                    <label class="block text-[11px] text-gray-500 mb-1">Tonnes</label>
                    <input type="number" step="0.001"
                           wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.tonnes"
                           class="{{ $baseInput }} text-xs">
                </div>

                <div>
                    <label class="block text-[11px] text-gray-500 mb-1">m³</label>
                    <input type="number" step="0.001"
                           wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.volume"
                           class="{{ $baseInput }} text-xs">
                </div>

                <div>
                    <label class="block text-[11px] text-gray-500 mb-1">LM</label>
                    <input type="number" step="0.001"
                           wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.loading_meters"
                           class="{{ $baseInput }} text-xs">
                </div>
            </div>

            {{-- Row 3 --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3">
                <div>
                    <label class="block text-[11px] text-gray-500 mb-1">ADR / Hazmat</label>
                    <input type="text"
                           wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.hazmat"
                           class="{{ $baseInput }} text-xs">
                </div>

                <div>
                    <label class="block text-[11px] text-gray-500 mb-1">Temperature</label>
                    <input type="text"
                           wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.temperature"
                           class="{{ $baseInput }} text-xs">
                </div>

                <div class="flex items-end">
                    <label class="inline-flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                        <input type="checkbox"
                               wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.stackable"
                               class="rounded border-gray-300">
                        Stackable
                    </label>
                </div>
            </div>

            {{-- Notes --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                <div>
                    <label class="block text-[11px] text-gray-500 mb-1">Instructions</label>
                    <input type="text"
                           wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.instructions"
                           class="{{ $baseInput }} text-xs">
                </div>

                <div>
                    <label class="block text-[11px] text-gray-500 mb-1">Remarks</label>
                    <input type="text"
                           wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.remarks"
                           class="{{ $baseInput }} text-xs">
                </div>
            </div>

            {{-- ✅ твоя кастомная ошибка measurements --}}
            @error("cargos.$index.items.$itemIndex.measurements")
                <div class="text-[11px] text-red-600 mt-2">{{ $message }}</div>
            @enderror
        </div>
    @endforeach
</div>

                    </div>
                </div>
            @empty
                <div class="text-sm text-gray-500">
                    Пока нет ни одного груза. Нажмите «Добавить груз».
                </div>
            @endforelse
        </section>

    </div>

    {{-- =========================
         BOTTOM FIXED ACTION BAR
    ========================== --}}
    <div class="fixed bottom-0 inset-x-0 z-30 bg-white/95 dark:bg-gray-900/95 border-t border-gray-200 dark:border-gray-700 backdrop-blur">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
            <div class="text-xs text-gray-500 truncate">
                После сохранения рейс, маршрут и грузы будут записаны в систему.
            </div>
            <button
                wire:click="save"
                wire:loading.attr="disabled"
                class="inline-flex items-center px-4 py-2 rounded-2xl text-sm font-semibold
                       bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white shadow">
                <span wire:loading.remove>💾 Создать рейс</span>
                <span wire:loading>⏳ Сохранение...</span>
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener("livewire:initialized", () => {
    Livewire.hook('message.processed', () => {
        const firstError = document.querySelector('.input-error');
        if (firstError) {
            firstError.focus({ preventScroll: true });
            const yOffset = -140;
            const y = firstError.getBoundingClientRect().top + window.scrollY + yOffset;
            window.scrollTo({ top: y, behavior: 'smooth' });
        }
    });
});
</script>
