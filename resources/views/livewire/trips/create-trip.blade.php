{{-- resources/views/livewire/trips/create-trip.blade.php --}}

@php
    /**
     * ============================================================
     *  SAFE UI HELPERS / DEFAULTS
     * ============================================================
     */
    

    $isBlank = $isBlank ?? function ($v) {
        if ($v === null) return true;
        if (is_string($v) && trim($v) === '') return true;
        if (is_array($v) && count($v) === 0) return true;
        return false;
    };

    $reqBadge = $reqBadge ?? function () {
        return '<span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-semibold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-200">required</span>';
    };

    // ⬇️ changed focus ring to amber (instead of blue)
    $baseInput = $baseInput ?? 'w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-300';
    $warnInput = $warnInput ?? 'border-amber-400 focus:ring-amber-300';
    $errInput  = $errInput  ?? 'border-red-500 focus:ring-red-300';

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

    $needsCarrier = (bool)($needsCarrierSelect ?? false);

    $carrier_company_select = $carrier_company_select ?? '';
    $thirdPartySelected     = ($carrier_company_select === '__third_party__');

    // keys
    $kExp  = 'expeditor_id';
    $kBank = 'bank_index';
    $kCarrierSelect = 'carrier_company_select';
    $kCarrierId     = 'carrier_company_id';

    $bankRequired = $bankRequired ?? (!empty($banks));

    $expWarn  = ($isBlank($expeditor_id ?? null) && !$errors->has($kExp));
    $bankWarn = (($bankRequired && $isBlank($bank_index ?? null)) && !$errors->has($kBank));

    // third party keys
    $kThirdName    = 'third_party_name';
    $kThirdTruck   = 'third_party_truck_plate';
    $kThirdTrailer = 'third_party_trailer_plate';
    $kThirdPrice   = 'third_party_price';

    $thirdPartyNameWarn  = ($thirdPartySelected && $isBlank($third_party_name ?? null) && !$errors->has($kThirdName));
    $thirdPartyTruckWarn = ($thirdPartySelected && $isBlank($third_party_truck_plate ?? null) && !$errors->has($kThirdTruck));
    $thirdPartyPriceWarn = ($thirdPartySelected && $isBlank($third_party_price ?? null) && !$errors->has($kThirdPrice));

    $isContainerTrailer = (bool)($isContainerTrailer ?? false);
    $trailerTypeMeta    = $trailerTypeMeta ?? null;

    // global step selection
    $kTripLoad = 'trip_loading_step_ids';
    $kTripUnld = 'trip_unloading_step_ids';

    $hasErrors =
        $errors->has($kExp) || $errors->has($kBank) ||
        $errors->has($kCarrierSelect) || $errors->has($kCarrierId) ||
        $errors->has($kThirdName) || $errors->has($kThirdTruck) || $errors->has($kThirdTrailer) || $errors->has($kThirdPrice) ||
        $errors->has($kTripLoad) || $errors->has($kTripUnld);

    $hasWarns =
        $expWarn || $bankWarn ||
        ($needsCarrier && $isBlank($carrier_company_select) && !$errors->has($kCarrierSelect)) ||
        $thirdPartyNameWarn || $thirdPartyTruckWarn || $thirdPartyPriceWarn;
@endphp

<div class="min-h-screen pb-24 bg-gradient-to-b from-gray-50 via-gray-100 to-gray-100 dark:from-gray-950 dark:via-gray-900 dark:to-gray-900">

    {{-- HEADER --}}
    <div class="sticky top-0 z-20 bg-white/85 dark:bg-gray-900/80 border-b border-amber-200 dark:border-amber-900/40 backdrop-blur">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
            <h1 class="text-lg sm:text-2xl font-semibold text-gray-800 dark:text-gray-100 truncate">
                ➕ Создание рейса (multi-cargo)
            </h1>

            <button
                wire:click="save"
                wire:loading.attr="disabled"
                class="hidden sm:inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold
                       bg-amber-600 hover:bg-amber-700 disabled:bg-amber-400 text-white shadow">
                <span wire:loading.remove>💾 Создать рейс</span>
                <span wire:loading>⏳ Сохранение...</span>
            </button>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-3 sm:px-4 pt-4 space-y-6">

        {{-- GLOBAL ERRORS --}}
        @if ($errors->any())
            <div class="bg-red-50 border border-red-300 text-red-800 rounded-xl px-4 py-3 text-sm">
                <div class="font-semibold mb-1">Ошибки:</div>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>❗ {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @error('error')
            <div class="bg-red-50 border border-red-300 text-red-800 rounded-xl px-4 py-3 text-sm">
                ❗ {{ $message }}
            </div>
        @enderror

        {{-- EXPEDITOR + CARRIER --}}
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

                {{-- LEFT --}}
                <div class="space-y-3">
                    {{-- EXPEDITOR --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                            Выберите экспедитора {!! $reqBadge() !!}
                        </label>

                        <select
                            wire:model.live="expeditor_id"
                            @class([$baseInput, 'js-select2', $warnInput => $expWarn, $errInput => $errors->has($kExp), 'input-error' => $errors->has($kExp)])
                        >
                            <option value="">— выберите экспедитора —</option>
                            @foreach($expeditors as $exp)
                                <option value="{{ $exp->id }}">
                                    {{ $exp->name }}@if(!empty($exp->type)) — {{ $exp->type }}@endif
                                </option>
                            @endforeach
                        </select>

                        @error('expeditor_id')
                            <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div>
                        @enderror
                    </div>

                    {{-- BANK --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                            Банковский счёт {!! $bankRequired ? $reqBadge() : '' !!}
                        </label>

                        <select
                            wire:model.live="bank_index"
                            @class([$baseInput, 'js-select2', $warnInput => $bankWarn, $errInput => $errors->has($kBank), 'input-error' => $errors->has($kBank)])
                        >
                            <option value="">— выберите банк —</option>
                            @foreach(($banks ?? []) as $idx => $bank)
                                <option value="{{ (string)$idx }}">
                                    {{ $bank['name'] ?? ('Bank #'.$idx) }}
                                </option>
                            @endforeach
                        </select>

                        @error('bank_index')
                            <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div>
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
                                @class([
                                    $baseInput,
                                    'js-select2',
                                    $warnInput => ($isBlank($carrier_company_select) && !$errors->has($kCarrierSelect)),
                                    $errInput => ($errors->has($kCarrierSelect) || $errors->has($kCarrierId)),
                                    'input-error' => ($errors->has($kCarrierSelect) || $errors->has($kCarrierId))
                                ])
                            >
                                <option value="">— выберите перевозчика —</option>
                                <option value="__third_party__">➕ Третья сторона</option>

                                @foreach(($carrierCompanies ?? []) as $c)
                                    <option value="{{ $c->id }}">
                                        {{ $c->name }}@if(!empty($c->type)) — {{ $c->type }}@endif
                                    </option>
                                @endforeach
                            </select>

                            @error('carrier_company_select')
                                <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div>
                            @enderror
                            @error('carrier_company_id')
                                <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div>
                            @enderror

                            @if(!$thirdPartySelected)
                                <div class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                    Экспедитор-посредник не является перевозчиком. Выберите компанию, которая выполняет рейс.
                                </div>
                            @endif
                        </div>
                    @else
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

                {{-- RIGHT CARD --}}
                <div class="sm:col-span-2">
                    <div class="rounded-2xl px-4 py-3 text-xs sm:text-sm space-y-1.5 border
                                bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700">
                        <div class="font-semibold text-gray-900 dark:text-gray-100 flex items-center justify-between gap-2">
                            <span class="truncate">{{ $expeditorData['name'] ?? '—' }}</span>
                            <span class="text-[10px] text-gray-500">ID: {{ $expeditor_id ?? '—' }}</span>
                        </div>

                        <div class="text-gray-700 dark:text-gray-200">
                            <div>Reg. Nr / VAT: <span class="font-medium">{{ $expeditorData['reg_nr'] ?? '—' }}</span></div>
                            <div>Country / City:
                                <span class="font-medium">
                                    {{ $expeditorData['country'] ?? '—' }}{{ !empty($expeditorData['city']) ? ', '.$expeditorData['city'] : '' }}
                                </span>
                            </div>
                            <div>Address:
                                <span class="font-medium">
                                    {{ $expeditorData['address'] ?? '—' }}
                                    @if(!empty($expeditorData['post_code'])), {{ $expeditorData['post_code'] }}@endif
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

                {{-- THIRD PARTY --}}
                @if($needsCarrier && $thirdPartySelected)
                    <div class="sm:col-span-3">
                        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4 space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                                <div class="sm:col-span-5 min-w-0">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                        Название третьей стороны {!! $reqBadge() !!}
                                    </label>
                                    <input type="text"
                                           wire:model.defer="third_party_name"
                                           placeholder="Напр. SIA New Carrier"
                                           @class([$baseInput, $warnInput => $thirdPartyNameWarn, $errInput => $errors->has($kThirdName), 'input-error' => $errors->has($kThirdName)])>
                                    @error('third_party_name') <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                                </div>

                                <div class="sm:col-span-3 min-w-0">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                        Номер тягача {!! $reqBadge() !!}
                                    </label>
                                    <input type="text"
                                           wire:model.defer="third_party_truck_plate"
                                           placeholder="Напр. AB-1234"
                                           @class([$baseInput, $warnInput => $thirdPartyTruckWarn, $errInput => $errors->has($kThirdTruck), 'input-error' => $errors->has($kThirdTruck)])>
                                    @error('third_party_truck_plate') <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                                </div>

                                <div class="sm:col-span-2 min-w-0">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                        Прицеп <span class="ml-2 text-[11px] text-gray-400">(опц.)</span>
                                    </label>
                                    <input type="text"
                                           wire:model.defer="third_party_trailer_plate"
                                           placeholder="Напр. XY-9876"
                                           @class([$baseInput, $errInput => $errors->has($kThirdTrailer), 'input-error' => $errors->has($kThirdTrailer)])>
                                    @error('third_party_trailer_plate') <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                                </div>

                                <div class="sm:col-span-2 min-w-0">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                        Фрахт (EUR) {!! $reqBadge() !!}
                                    </label>
                                    <div class="relative">
                                        <input type="number"
                                               step="0.01"
                                               wire:model.defer="third_party_price"
                                               placeholder="0.00"
                                               @class([$baseInput.' pr-10', $warnInput => $thirdPartyPriceWarn, $errInput => $errors->has($kThirdPrice), 'input-error' => $errors->has($kThirdPrice)])>
                                        <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs text-gray-400">EUR</div>
                                    </div>
                                    @error('third_party_price') <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div> @enderror
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

        {{-- TRANSPORT (OUR ONLY) --}}
        @if(!$thirdPartySelected)
            <section class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm px-4 py-4 sm:px-6 sm:py-5 space-y-4 border border-gray-100 dark:border-gray-800">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100">
                        🚚 Транспорт
                    </h2>

                    {{-- trailer type badge --}}
                    @if(!empty($trailerTypeMeta))
                        <div class="inline-flex items-center gap-2 text-xs px-3 py-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-200">
                            <span class="text-base leading-none">{{ $trailerTypeMeta['icon'] ?? '🚚' }}</span>
                            <span class="font-semibold">{{ $trailerTypeMeta['label'] ?? 'Trailer' }}</span>
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                            Водитель {!! $reqBadge() !!}
                        </label>
                        <select wire:model.live="driver_id" class="{{ $baseInput }} js-select2">
                            <option value="">— выбрать —</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}">{{ $driver->first_name }} {{ $driver->last_name }}</option>
                            @endforeach
                        </select>
                        @error('driver_id') <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                            Тягач {!! $reqBadge() !!}
                        </label>
                        <select wire:model.live="truck_id" class="{{ $baseInput }} js-select2">
                            <option value="">— выбрать —</option>
                            @foreach($trucks as $truck)
                                <option value="{{ $truck->id }}">{{ $truck->plate }} ({{ $truck->brand }} {{ $truck->model }})</option>
                            @endforeach
                        </select>
                        @error('truck_id') <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                            Прицеп <span class="ml-2 text-[11px] text-gray-400">(опц.)</span>
                        </label>

                        <select wire:model.live="trailer_id" class="{{ $baseInput }} js-select2">
                            <option value="">— без прицепа —</option>
                            @foreach($trailers as $trailer)
                                @php
                                    $types  = config('trailer-types.types', []);
                                    $labels = config('trailer-types.labels', []);
                                    $icons  = config('trailer-types.icons', []);

                                    $key   = $types[$trailer->type_id] ?? null;
                                    $icon  = $key ? ($icons[$key] ?? '🚚') : '🚚';
                                    $label = $key ? ($labels[$key] ?? $key) : 'unknown';
                                @endphp

                                <option value="{{ $trailer->id }}">
                                    {{ $icon }} {{ $trailer->plate }} ({{ $label }}{{ $trailer->brand ? ' · '.$trailer->brand : '' }})
                                </option>
                            @endforeach
                        </select>

                        @error('trailer_id') <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- container/seal --}}
                @if($isContainerTrailer)
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                Номер контейнера {!! $reqBadge() !!}
                            </label>
                            <input type="text" wire:model.defer="cont_nr" placeholder="Напр. MSKU1234567" class="{{ $baseInput }}">
                            @error('cont_nr') <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                Номер пломбы {!! $reqBadge() !!}
                            </label>
                            <input type="text" wire:model.defer="seal_nr" placeholder="Напр. SEAL-000123" class="{{ $baseInput }}">
                            @error('seal_nr') <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                            Дата начала {!! $reqBadge() !!}
                        </label>
                        <input type="date" wire:model.defer="start_date" class="{{ $baseInput }}">
                        @error('start_date') <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                            Дата окончания {!! $reqBadge() !!}
                        </label>
                        <input type="date" wire:model.defer="end_date" class="{{ $baseInput }}">
                        @error('end_date') <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                            Валюта {!! $reqBadge() !!}
                        </label>
                        <input type="text" wire:model.defer="currency" class="{{ $baseInput }}" readonly>
                        @error('currency') <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                    </div>
                </div>
            </section>
        @endif

        {{-- TIR / CUSTOMS --}}
        <section class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm px-4 py-4 sm:px-6 sm:py-5 space-y-4 border border-gray-100 dark:border-gray-800">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100">
                    🧾 TIR / Таможня
                </h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-start">
                <div class="sm:col-span-1">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                        <input type="checkbox" wire:model.live="customs" class="rounded border-gray-300">
                        Таможенное оформление (TIR)
                    </label>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                        Если включено — нужно указать адрес таможенного пункта.
                    </div>
                </div>

                @if(($customs ?? false))
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                            Адрес таможенного пункта {!! $reqBadge() !!}
                        </label>

                        <input
                            type="text"
                            wire:model.defer="customs_address"
                            placeholder="Напр. Riga, Customs terminal ..."
                            @class([$baseInput, $errInput => $errors->has('customs_address'), 'input-error' => $errors->has('customs_address')])
                        >

                        @error('customs_address')
                            <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div>
                        @enderror
                    </div>
                @else
                    <div class="sm:col-span-2">
                        <div class="rounded-2xl border border-amber-200 dark:border-amber-900/40 bg-amber-50/70 dark:bg-amber-900/10 p-3 text-[12px] text-amber-900 dark:text-amber-200">
                            <span class="font-semibold">ℹ️</span> Адрес таможни появится после включения TIR.
                        </div>
                    </div>
                @endif
            </div>
        </section>

        {{-- STEPS --}}
        <section class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm px-4 py-4 sm:px-6 sm:py-5 space-y-4 border border-gray-100 dark:border-gray-800">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100">
                    🧭 Маршрут (steps)
                </h2>

                <button type="button"
                        wire:click="addStep"
                        class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold bg-amber-600 hover:bg-amber-700 text-white shadow-sm">
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
                                @error("steps.$index.type") <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Дата / время {!! $reqBadge() !!}
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="date" wire:model.defer="steps.{{ $index }}.date" class="{{ $baseInput }}">
                                    <input type="time" wire:model.defer="steps.{{ $index }}.time" class="{{ $baseInput }}">
                                </div>
                                @error("steps.$index.date") <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Order {!! $reqBadge() !!}
                                </label>
                                <input type="number" wire:model.defer="steps.{{ $index }}.order" class="{{ $baseInput }}">
                                @error("steps.$index.order") <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Страна {!! $reqBadge() !!}
                                </label>
                                <select wire:model.live="steps.{{ $index }}.country_id" class="{{ $baseInput }} js-select2">
                                    <option value="">— выбрать —</option>
                                    @foreach($countries as $countryId => $country)
                                        <option value="{{ $countryId }}">{{ $country['name'] }}</option>
                                    @endforeach
                                </select>
                                @error("steps.$index.country_id") <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Город {!! $reqBadge() !!}
                                </label>
                                <select wire:model.live="steps.{{ $index }}.city_id" class="{{ $baseInput }} js-select2">
                                    <option value="">— выбрать —</option>
                                    @foreach(($stepCities[$index]['cities'] ?? []) as $cityId => $city)
                                        <option value="{{ $cityId }}">{{ $city['name'] ?? ('#'.$cityId) }}</option>
                                    @endforeach
                                </select>
                                @error("steps.$index.city_id") <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Адрес {!! $reqBadge() !!}
                                </label>
                                <input type="text" wire:model.defer="steps.{{ $index }}.address" class="{{ $baseInput }}">
                                @error("steps.$index.address") <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div> @enderror
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

        {{-- GLOBAL STEPS SELECTION (ONE TIME) --}}
        @php
            $stepLabel = function ($si, $s) use ($countries, $stepCities) {
                $type = $s['type'] ?? '';
                $typeIcon = $type === 'loading' ? '📦' : ($type === 'unloading' ? '📭' : '📍');

                $countryName = $countries[$s['country_id']]['name'] ?? '';
                $cityName    = $stepCities[$si]['cities'][$s['city_id']]['name'] ?? '';
                $addr        = $s['address'] ?? '';
                $when        = trim(($s['date'] ?? '').' '.($s['time'] ?? ''));

                return trim($typeIcon.' #'.($si+1).' · '.$when.' · '.$countryName.' '.$cityName.' · '.$addr);
            };

            $selectedLoadingGlobal   = (array)($trip_loading_step_ids ?? []);
            $selectedUnloadingGlobal = (array)($trip_unloading_step_ids ?? []);
        @endphp

        <section class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm px-4 py-4 sm:px-6 sm:py-5 space-y-4 border border-gray-100 dark:border-gray-800">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100">
                    🔗 Шаги для грузов (выбирается один раз)
                </h2>
                <div class="text-[11px] text-gray-500 dark:text-gray-400">
                    Применяется ко всем грузам автоматически.
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                {{-- LOADING --}}
                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-xs font-semibold text-gray-700 dark:text-gray-200">
                            Погрузка {!! $reqBadge() !!}
                        </div>

                        <button type="button"
                                wire:click="$set('trip_loading_step_ids', [])"
                                class="text-[11px] px-2 py-1 rounded-lg text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-white hover:bg-white/60 dark:hover:bg-gray-900/30">
                            Очистить
                        </button>
                    </div>

                    @if(!empty($selectedLoadingGlobal))
                        <div class="flex flex-wrap gap-1.5 mb-2">
                            @foreach($steps as $si => $s)
                                @continue(($s['type'] ?? null) !== 'loading')
                                @php $uid = (string)($s['uid'] ?? ''); @endphp
                                @if($uid && in_array($uid, $selectedLoadingGlobal, true))
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-xl text-[11px]
                                                 bg-white dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700
                                                 text-gray-700 dark:text-gray-200">
                                        📦 #{{ $si+1 }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <div class="max-h-64 overflow-auto pr-1 space-y-1">
                        @foreach($steps as $si => $s)
                            @continue(($s['type'] ?? null) !== 'loading')
                            @php
                                $uid = (string)($s['uid'] ?? '');
                                if($uid === '') continue;
                                $label = $stepLabel($si, $s);
                                $checked = in_array($uid, $selectedLoadingGlobal, true);
                            @endphp

                            <button type="button"
                                    wire:click="toggleTripLoadingStep('{{ $uid }}')"
                                    class="w-full text-left flex items-start gap-2 p-2 rounded-xl border transition
                                           {{ $checked
                                                ? 'bg-amber-600 text-white border-amber-600 shadow'
                                                : 'bg-white/80 dark:bg-gray-900/40 border-gray-200 dark:border-gray-700 hover:bg-white dark:hover:bg-gray-900/60'
                                           }}">
                                <span class="mt-0.5 text-[13px]">📦</span>
                                <span class="text-[12px] leading-snug">
                                    <span class="font-semibold">#{{ $si+1 }}</span>
                                    <span class="{{ $checked ? 'opacity-90' : 'text-gray-700 dark:text-gray-200' }}">
                                        · {{ $label }}
                                    </span>
                                </span>
                            </button>
                        @endforeach
                    </div>

                    @error("trip_loading_step_ids")
                        <div class="text-[11px] text-red-600 mt-2">❗ {{ $message }}</div>
                    @enderror
                </div>

                {{-- UNLOADING --}}
                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-xs font-semibold text-gray-700 dark:text-gray-200">
                            Разгрузка {!! $reqBadge() !!}
                        </div>

                        <button type="button"
                                wire:click="$set('trip_unloading_step_ids', [])"
                                class="text-[11px] px-2 py-1 rounded-lg text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-white hover:bg-white/60 dark:hover:bg-gray-900/30">
                            Очистить
                        </button>
                    </div>

                    @if(!empty($selectedUnloadingGlobal))
                        <div class="flex flex-wrap gap-1.5 mb-2">
                            @foreach($steps as $si => $s)
                                @continue(($s['type'] ?? null) !== 'unloading')
                                @php $uid = (string)($s['uid'] ?? ''); @endphp
                                @if($uid && in_array($uid, $selectedUnloadingGlobal, true))
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-xl text-[11px]
                                                 bg-white dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700
                                                 text-gray-700 dark:text-gray-200">
                                        📭 #{{ $si+1 }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <div class="max-h-64 overflow-auto pr-1 space-y-1">
                        @foreach($steps as $si => $s)
                            @continue(($s['type'] ?? null) !== 'unloading')
                            @php
                                $uid = (string)($s['uid'] ?? '');
                                if($uid === '') continue;
                                $label = $stepLabel($si, $s);
                                $checked = in_array($uid, $selectedUnloadingGlobal, true);
                            @endphp

                            <button type="button"
                                    wire:click="toggleTripUnloadingStep('{{ $uid }}')"
                                    class="w-full text-left flex items-start gap-2 p-2 rounded-xl border transition
                                           {{ $checked
                                                ? 'bg-emerald-600 text-white border-emerald-600 shadow'
                                                : 'bg-white/80 dark:bg-gray-900/40 border-gray-200 dark:border-gray-700 hover:bg-white dark:hover:bg-gray-900/60'
                                           }}">
                                <span class="mt-0.5 text-[13px]">📭</span>
                                <span class="text-[12px] leading-snug">
                                    <span class="font-semibold">#{{ $si+1 }}</span>
                                    <span class="{{ $checked ? 'opacity-90' : 'text-gray-700 dark:text-gray-200' }}">
                                        · {{ $label }}
                                    </span>
                                </span>
                            </button>
                        @endforeach
                    </div>

                    <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-2">
                        Можно делать L→U→L→U. Ограничение только одно: первая разгрузка должна быть после первой погрузки.
                    </div>

                    @error("trip_unloading_step_ids")
                        <div class="text-[11px] text-red-600 mt-2">❗ {{ $message }}</div>
                    @enderror
                </div>

            </div>
        </section>

        {{-- =========================
             CARGOS (multi-cargo)
        ========================== --}}
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

                        {{-- Top: parties --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Заказчик {!! $reqBadge() !!}</label>
                                <select wire:model.live="cargos.{{ $index }}.customer_id" class="{{ $baseInput }} js-select2">
                                    <option value="">— выбрать —</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                    @endforeach
                                </select>
                                @error("cargos.$index.customer_id") <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Shipper {!! $reqBadge() !!}</label>
                                <select wire:model.live="cargos.{{ $index }}.shipper_id" class="{{ $baseInput }} js-select2">
                                    <option value="">— выбрать —</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                    @endforeach
                                </select>
                                @error("cargos.$index.shipper_id") <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Consignee {!! $reqBadge() !!}</label>
                                <select wire:model.live="cargos.{{ $index }}.consignee_id" class="{{ $baseInput }} js-select2">
                                    <option value="">— выбрать —</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                    @endforeach
                                </select>
                                @error("cargos.$index.consignee_id") <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Summary of applied steps (auto) --}}
                        @if(!empty($trip_loading_step_ids) || !empty($trip_unloading_step_ids))
                            <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3">
                                <div class="text-xs font-semibold text-gray-700 dark:text-gray-200 mb-2">
                                    Привязанные шаги (авто)
                                </div>

                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($steps as $si => $s)
                                        @php $uid = (string)($s['uid'] ?? ''); @endphp

                                        @if($uid && ($s['type'] ?? null) === 'loading' && in_array($uid, (array)($trip_loading_step_ids ?? []), true))
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-xl text-[11px]
                                                         bg-white dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700
                                                         text-gray-700 dark:text-gray-200">
                                                📦 #{{ $si+1 }}
                                            </span>
                                        @endif

                                        @if($uid && ($s['type'] ?? null) === 'unloading' && in_array($uid, (array)($trip_unloading_step_ids ?? []), true))
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-xl text-[11px]
                                                         bg-white dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700
                                                         text-gray-700 dark:text-gray-200">
                                                📭 #{{ $si+1 }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Price / tax --}}
                        <div class="grid grid-cols-2 sm:grid-cols-6 gap-3 border-t border-gray-100 dark:border-gray-800 pt-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Цена</label>
                                <input type="text" wire:model.live="cargos.{{ $index }}.price" class="{{ $baseInput }} text-xs">
                                @error("cargos.$index.price") <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">НДС %</label>
                                <select wire:model.live="cargos.{{ $index }}.tax_percent" class="{{ $baseInput }} text-xs js-select2">
                                    @foreach($taxRates as $rate)
                                        <option value="{{ $rate }}">{{ $rate }}</option>
                                    @endforeach
                                </select>
                                @error("cargos.$index.tax_percent") <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div> @enderror
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
                                <select wire:model.live="cargos.{{ $index }}.payer_type_id" class="{{ $baseInput }} text-xs js-select2">
                                    <option value="">— не выбрано —</option>
                                    @foreach($payers as $payerId => $payer)
                                        <option value="{{ $payerId }}">{{ $payer['label'] ?? $payerId }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- commercial invoice --}}
                        <div class="grid grid-cols-1 sm:grid-cols-6 gap-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Commercial invoice №</label>
                                <input type="text" wire:model.defer="cargos.{{ $index }}.commercial_invoice_nr" class="{{ $baseInput }} text-xs">
                                @error("cargos.$index.commercial_invoice_nr") <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Commercial invoice amount</label>
                                <input type="text" wire:model.defer="cargos.{{ $index }}.commercial_invoice_amount" class="{{ $baseInput }} text-xs">
                                @error("cargos.$index.commercial_invoice_amount") <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            <button type="button"
                                    wire:click="addItem({{ $index }})"
                                    class="text-xs px-3 py-1.5 rounded-lg bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200">
                                ➕ Добавить позицию
                            </button>
                        </div>

                        {{-- ITEMS --}}
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

                                    <div class="grid grid-cols-1 sm:grid-cols-6 gap-3">
                                        <div class="sm:col-span-3">
                                            <label class="block text-[11px] text-gray-500 mb-1">Описание</label>
                                            <input type="text"
                                                   wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.description"
                                                   class="{{ $baseInput }} text-xs">
                                            @error("cargos.$index.items.$itemIndex.description")
                                                <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="sm:col-span-1">
                                            <label class="block text-[11px] text-gray-500 mb-1">HS / Customs</label>
                                            <input type="text"
                                                   wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.customs_code"
                                                   class="{{ $baseInput }} text-xs">
                                            @error("cargos.$index.items.$itemIndex.customs_code")
                                                <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div>
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

                                    @error("cargos.$index.items.$itemIndex.measurements")
                                        <div class="text-[11px] text-red-600 mt-2">❗ {{ $message }}</div>
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

    {{-- BOTTOM BAR --}}
    <div class="fixed bottom-0 inset-x-0 z-30 bg-white/95 dark:bg-gray-900/95 border-t border-amber-200 dark:border-amber-900/40 backdrop-blur">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
            <div class="text-xs text-gray-500 truncate">
                После сохранения рейс, маршрут и грузы будут записаны в систему.
            </div>
            <button
                wire:click="save"
                wire:loading.attr="disabled"
                class="inline-flex items-center px-4 py-2 rounded-2xl text-sm font-semibold
                       bg-amber-600 hover:bg-amber-700 disabled:bg-amber-400 text-white shadow">
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
