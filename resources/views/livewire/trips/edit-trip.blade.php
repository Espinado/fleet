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
        return '<span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-semibold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-200">'.e(__('app.trip.edit.required')).'</span>';
    };

    // ⬇️ changed focus ring to amber (instead of blue)
    $baseInput = $baseInput ?? 'w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-300';
    $warnInput = $warnInput ?? 'border-amber-400 focus:ring-amber-300';
    $errInput  = $errInput  ?? 'border-2 border-red-500 bg-red-50/50 dark:bg-red-900/10 focus:ring-2 focus:ring-red-400 focus:border-red-500';

    $badgeError = $badgeError ?? 'inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-200';
    $badgeWarn  = $badgeWarn  ?? 'inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-200';

    // data safety
    $expeditors        = $expeditors        ?? collect();
    $carrierCompanies  = $carrierCompanies  ?? collect();
    $thirdPartyCarriers = $thirdPartyCarriers ?? collect();
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
    // В селекте перевозчика для третьей стороны показываем: «3. puse: Название — Номер тягача»
    $thirdPartyOptionLabel  = null;
    if ($thirdPartySelected) {
        $name = trim((string)($third_party_name ?? ''));
        $plate = trim((string)($third_party_truck_plate ?? ''));
        if ($name !== '' || $plate !== '') {
            $thirdPartyOptionLabel = __('app.trip.edit.third_party') . ': ' . ($name ?: '—') . ' — ' . ($plate ?: '—');
        }
    }

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

    // Human-readable field label for error list (steps.0.date → "Шаг 1, Дата")
    $errorFieldLabel = $errorFieldLabel ?? function ($key) {
        if (preg_match('/^steps\.(\d+)\.(.+)$/', $key, $m)) {
            $num = (int)$m[1] + 1;
            $field = $m[2];
            $labels = ['type' => __('app.trip.edit.type'), 'date' => __('app.trip.edit.date_time'), 'time' => __('app.trip.edit.date_time'), 'order' => __('app.trip.edit.order'), 'country_id' => __('app.trip.edit.country'), 'city_id' => __('app.trip.edit.city'), 'address' => __('app.trip.edit.address'), 'notes' => __('app.trip.edit.notes')];
            $stepLabel = str_replace(':n', (string)$num, __('app.trip.edit.step_n'));
            return $stepLabel.', '.($labels[$field] ?? $field);
        }
        if (preg_match('/^cargos\.(\d+)\.(.+)$/', $key, $m)) {
            $num = (int)$m[1] + 1;
            $rest = $m[2];
            $cargoLabel = str_replace(':n', (string)$num, __('app.trip.edit.cargo_n'));
            if (preg_match('/^items\.(\d+)\.(.+)$/', $rest, $m2)) {
                return $cargoLabel.', '.($m2[1]+1).'. '.$m2[2];
            }
            return $cargoLabel.', '.$rest;
        }
        $map = ['expeditor_id' => __('app.trip.edit.choose_expeditor'), 'bank_index' => __('app.trip.edit.bank_account'), 'carrier_company_select' => __('app.trip.edit.carrier'), 'driver_id' => __('app.trip.edit.driver'), 'truck_id' => __('app.trip.edit.truck'), 'trailer_id' => __('app.trip.edit.trailer'), 'start_date' => __('app.trip.edit.start_date'), 'end_date' => __('app.trip.edit.end_date'), 'trip_loading_step_ids' => __('app.trip.edit.loading_label'), 'trip_unloading_step_ids' => __('app.trip.edit.unloading_label')];
        return $map[$key] ?? $key;
    };
@endphp

<div class="pb-8 bg-gradient-to-b from-gray-50 via-gray-100 to-gray-100 dark:from-gray-950 dark:via-gray-900 dark:to-gray-900">

    {{-- HEADER --}}
    <div class="sticky top-0 z-20 bg-white/85 dark:bg-gray-900/80 border-b border-amber-200 dark:border-amber-900/40 backdrop-blur">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
            <h1 class="text-lg sm:text-2xl font-semibold text-gray-800 dark:text-gray-100 truncate">
                ➕ {{ __('app.trip.edit.header') }}
            </h1>

            <button
                wire:click="save"
                wire:loading.attr="disabled"
                class="hidden sm:inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold
                       bg-amber-600 hover:bg-amber-700 disabled:bg-amber-400 text-white shadow">
                <span wire:loading.remove>💾 {{ __('app.trip.edit.save') }}</span>
                <span wire:loading>⏳ {{ __('app.trip.edit.saving') }}</span>
            </button>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-3 sm:px-4 pt-4 space-y-6">

        {{-- GLOBAL ERRORS (field + message) --}}
        @if ($errors->any())
            <div class="bg-red-50 dark:bg-red-900/20 border-2 border-red-400 text-red-800 dark:text-red-200 rounded-xl px-4 py-3 text-sm shadow-sm">
                <div class="font-semibold mb-2">{{ __('app.trip.edit.errors') }}</div>
                <ul class="list-none space-y-1.5">
                    @foreach ($errors->messages() as $field => $messages)
                        @foreach ($messages as $msg)
                            <li class="flex items-start gap-2 rounded-lg bg-red-100/80 dark:bg-red-900/30 px-3 py-2">
                                <span class="font-medium shrink-0">{{ $errorFieldLabel($field) }}:</span>
                                <span>{{ $msg }}</span>
                            </li>
                        @endforeach
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
                    🧾 {{ __('app.trip.edit.expeditor') }}
                </h2>

                @if($hasErrors)
                    <span class="{{ $badgeError }}">{{ __('app.trip.edit.errors') }}</span>
                @elseif($hasWarns)
                    <span class="{{ $badgeWarn }}">{{ __('app.trip.edit.not_filled') }}</span>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-start">

                {{-- LEFT --}}
                <div class="space-y-3">
                    {{-- EXPEDITOR --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                            {{ __('app.trip.edit.choose_expeditor') }} {!! $reqBadge() !!}
                        </label>

                        <select
                            wire:model.live="expeditor_id"
                            @class([$baseInput, $warnInput => $expWarn, $errInput => $errors->has($kExp), 'input-error' => $errors->has($kExp)])
                        >
                            <option value="">— {{ __('app.trip.edit.choose_expeditor') }} —</option>
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
                            {{ __('app.trip.edit.bank_account') }} {!! $bankRequired ? $reqBadge() : '' !!}
                        </label>

                        <select
                            wire:model.live="bank_index"
                            @class([$baseInput, $warnInput => $bankWarn, $errInput => $errors->has($kBank), 'input-error' => $errors->has($kBank)])
                        >
                            <option value="">— {{ __('app.trip.edit.choose_bank') }} —</option>
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
                                {{ __('app.trip.edit.bank_empty_hint') }}
                            </div>
                        @endif
                    </div>

                    {{-- CARRIER SELECT --}}
                    @if($needsCarrier)
                        <div class="pt-2 border-t border-gray-200/70 dark:border-gray-700/70 space-y-2">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                {{ __('app.trip.edit.carrier') }} {!! $reqBadge() !!}
                            </label>

                            <select
                                wire:model.live="carrier_company_select"
                                @class([
                                    $baseInput,
                                    $warnInput => ($isBlank($carrier_company_select) && !$errors->has($kCarrierSelect)),
                                    $errInput => ($errors->has($kCarrierSelect) || $errors->has($kCarrierId)),
                                    'input-error' => ($errors->has($kCarrierSelect) || $errors->has($kCarrierId))
                                ])
                            >
                                <option value="">— {{ __('app.trip.edit.choose_carrier') }} —</option>
                                <option value="__third_party__">➕ {{ $thirdPartyOptionLabel ?? __('app.trip.edit.third_party') }}</option>

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
                                    {{ __('app.trip.edit.carrier_hint') }}
                                </div>
                            @endif
                        </div>
                    @else
                        @if(!empty($expeditor_id))
                            <div class="pt-2 border-t border-gray-200/70 dark:border-gray-700/70">
                                <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                    {{ __('app.trip.edit.carrier_auto') }}
                                    <span class="font-semibold">{{ $expeditorData['name'] ?? '—' }}</span>
                                    {{ __('app.trip.edit.carrier_auto_hint') }}
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
                            <div>{{ __('app.trip.edit.reg_nr_vat') }} <span class="font-medium">{{ $expeditorData['reg_nr'] ?? '—' }}</span></div>
                            <div>{{ __('app.trip.edit.country_city') }}
                                <span class="font-medium">
                                    {{ $expeditorData['country'] ?? '—' }}{{ !empty($expeditorData['city']) ? ', '.$expeditorData['city'] : '' }}
                                </span>
                            </div>
                            <div>{{ __('app.trip.edit.address') }}
                                <span class="font-medium">
                                    {{ $expeditorData['address'] ?? '—' }}
                                    @if(!empty($expeditorData['post_code'])), {{ $expeditorData['post_code'] }}@endif
                                </span>
                            </div>
                        </div>

                        <div class="text-gray-700 dark:text-gray-200 pt-1 border-t border-gray-200/70 dark:border-gray-700/70 mt-1">
                            <div>{{ __('app.trip.edit.phone') }} <span class="font-medium">{{ $expeditorData['phone'] ?? '—' }}</span></div>
                            <div>{{ __('app.trip.edit.email') }} <span class="font-medium">{{ $expeditorData['email'] ?? '—' }}</span></div>
                        </div>

                        <div class="text-gray-700 dark:text-gray-200 pt-1 border-t border-gray-200/70 dark:border-gray-700/70 mt-1">
                            <div>{{ __('app.trip.edit.bank') }} <span class="font-medium">{{ $expeditorData['bank'] ?? '—' }}</span></div>
                            <div>{{ __('app.trip.edit.iban') }} <span class="font-medium">{{ $expeditorData['iban'] ?? '—' }}</span></div>
                            <div>{{ __('app.trip.edit.bic') }} <span class="font-medium">{{ $expeditorData['bic'] ?? '—' }}</span></div>
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
                                        {{ __('app.trip.edit.third_party_name') }} {!! $reqBadge() !!}
                                    </label>
                                    <input
                                        type="text"
                                        wire:model.blur="third_party_name"
                                        list="third-party-carriers-list-edit"
                                        placeholder="{{ __('app.trip.edit.placeholder_carrier') }}"
                                        @class([$baseInput, $warnInput => $thirdPartyNameWarn, $errInput => $errors->has($kThirdName), 'input-error' => $errors->has($kThirdName)])
                                    >
                                    <datalist id="third-party-carriers-list-edit">
                                        @foreach(($thirdPartyCarriers ?? []) as $tp)
                                            <option value="{{ $tp->name }}">{{ $tp->name }}</option>
                                        @endforeach
                                    </datalist>
                                    @error('third_party_name') <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                                </div>

                                <div class="sm:col-span-3 min-w-0">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                        {{ __('app.trip.edit.third_party_truck') }} {!! $reqBadge() !!}
                                    </label>
                                    <input type="text"
                                           wire:model.blur="third_party_truck_plate"
                                           placeholder="{{ __('app.trip.edit.placeholder_truck') }}"
                                           @class([$baseInput, $warnInput => $thirdPartyTruckWarn, $errInput => $errors->has($kThirdTruck), 'input-error' => $errors->has($kThirdTruck)])>
                                    @error('third_party_truck_plate') <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                                </div>

                                <div class="sm:col-span-2 min-w-0">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                        {{ __('app.trip.edit.third_party_trailer') }} <span class="ml-2 text-[11px] text-gray-400">{{ __('app.trip.edit.third_party_trailer_opt') }}</span>
                                    </label>
                                    <input type="text"
                                           wire:model.blur="third_party_trailer_plate"
                                           placeholder="{{ __('app.trip.edit.placeholder_trailer') }}"
                                           @class([$baseInput, $errInput => $errors->has($kThirdTrailer), 'input-error' => $errors->has($kThirdTrailer)])>
                                    @error('third_party_trailer_plate') <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                                </div>

                                <div class="sm:col-span-2 min-w-0">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                        {{ __('app.trip.edit.freight_eur') }} {!! $reqBadge() !!}
                                    </label>
                                    <div class="relative">
                                        <input type="number"
                                               step="0.01"
                                               wire:model.blur="third_party_price"
                                               placeholder="0.00"
                                               @class([$baseInput.' pr-10', $warnInput => $thirdPartyPriceWarn, $errInput => $errors->has($kThirdPrice), 'input-error' => $errors->has($kThirdPrice)])>
                                        <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs text-gray-400">EUR</div>
                                    </div>
                                    @error('third_party_price') <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                {{ __('app.trip.edit.third_party_hint') }}
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
                        🚚 {{ __('app.trip.edit.transport') }}
                    </h2>

                    {{-- trailer type badge --}}
                    @if(!empty($trailerTypeMeta))
                        <div class="inline-flex items-center gap-2 text-xs px-3 py-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-200">
                            <span class="text-base leading-none">{{ $trailerTypeMeta['icon'] ?? '🚚' }}</span>
                            <span class="font-semibold">{{ $trailerTypeMeta['label'] ?? __('app.trip.edit.trailer_type') }}</span>
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                            {{ __('app.trip.edit.driver') }} {!! $reqBadge() !!}
                        </label>
                        <select wire:model.live="driver_id" class="{{ $baseInput }}">
                            <option value="">— {{ __('app.trip.edit.choose') }} —</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}">{{ $driver->first_name }} {{ $driver->last_name }}</option>
                            @endforeach
                        </select>
                        @error('driver_id') <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                            {{ __('app.trip.edit.truck') }} {!! $reqBadge() !!}
                        </label>
                        <select wire:model.live="truck_id" class="{{ $baseInput }}">
                            <option value="">— {{ __('app.trip.edit.choose') }} —</option>
                            @foreach($trucks as $truck)
                                <option value="{{ $truck->id }}">{{ $truck->plate }} ({{ $truck->brand }} {{ $truck->model }})</option>
                            @endforeach
                        </select>
                        @error('truck_id') <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                            {{ __('app.trip.edit.trailer') }} <span class="ml-2 text-[11px] text-gray-400">{{ __('app.trip.edit.third_party_trailer_opt') }}</span>
                        </label>

                        <select wire:model.live="trailer_id" class="{{ $baseInput }}">
                            <option value="">{{ __('app.trip.edit.no_trailer') }}</option>
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
                                {{ __('app.trip.edit.container_nr') }} {!! $reqBadge() !!}
                            </label>
                            <input type="text" wire:model.blur="cont_nr" placeholder="{{ __('app.trip.edit.placeholder_cont') }}" class="{{ $baseInput }}">
                            @error('cont_nr') <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                {{ __('app.trip.edit.seal_nr') }} {!! $reqBadge() !!}
                            </label>
                            <input type="text" wire:model.blur="seal_nr" placeholder="{{ __('app.trip.edit.placeholder_seal') }}" class="{{ $baseInput }}">
                            @error('seal_nr') <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                            {{ __('app.trip.edit.start_date') }} {!! $reqBadge() !!}
                        </label>
                        <input type="date" wire:model.blur="start_date" class="{{ $baseInput }}">
                        @error('start_date') <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                            {{ __('app.trip.edit.end_date') }} {!! $reqBadge() !!}
                        </label>
                        <input type="date" wire:model.blur="end_date" class="{{ $baseInput }}">
                        @error('end_date') <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                            {{ __('app.trip.edit.currency') }} {!! $reqBadge() !!}
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
                    🧾 {{ __('app.trip.edit.tir_customs') }}
                </h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-start">
                <div class="sm:col-span-1">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                        <input type="checkbox" wire:model.live="customs" class="rounded border-gray-300">
                        {{ __('app.trip.edit.customs_tir') }}
                    </label>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('app.trip.edit.customs_hint') }}
                    </div>
                </div>

                @if(($customs ?? false))
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                            {{ __('app.trip.edit.customs_address') }} {!! $reqBadge() !!}
                        </label>

                        <input
                            type="text"
                            wire:model.blur="customs_address"
                            placeholder="{{ __('app.trip.edit.customs_placeholder') }}"
                            @class([$baseInput, $errInput => $errors->has('customs_address'), 'input-error' => $errors->has('customs_address')])
                        >

                        @error('customs_address')
                            <div class="text-xs text-red-600 mt-1">❗ {{ $message }}</div>
                        @enderror
                    </div>
                @else
                    <div class="sm:col-span-2">
                        <div class="rounded-2xl border border-amber-200 dark:border-amber-900/40 bg-amber-50/70 dark:bg-amber-900/10 p-3 text-[12px] text-amber-900 dark:text-amber-200">
                            <span class="font-semibold">ℹ️</span> {{ __('app.trip.edit.customs_after_tir') }}
                        </div>
                    </div>
                @endif
            </div>
        </section>

        {{-- STEPS (sortable drag-drop) --}}
        <section class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm px-4 py-4 sm:px-6 sm:py-5 space-y-4 border border-gray-100 dark:border-gray-800"
                 wire:key="steps-section">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100">
                    🧭 {{ __('app.trip.edit.route_steps') }}
                </h2>

                <button type="button"
                        wire:click="addStep"
                        class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold bg-amber-600 hover:bg-amber-700 text-white shadow-sm">
                    ➕ {{ __('app.trip.edit.add_step') }}
                </button>
            </div>

            @if(count($steps) > 0)
                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                    {{ __('app.trip.route.subtitle') }}
                </p>

            <ul id="sortable-edit-steps-{{ $trip->id ?? 'new' }}"
                class="space-y-4 list-none p-0 m-0"
                x-data
                x-init="
                    if (typeof Sortable === 'undefined') return;
                    if ($el.dataset.sortableAttached === '1') return;
                    $el.dataset.sortableAttached = '1';
                    new Sortable($el, {
                        animation: 200,
                        handle: '.edit-step-drag-handle',
                        ghostClass: 'opacity-50',
                        onEnd: function() {
                            var indices = Array.from($el.querySelectorAll('li[data-step-index]'))
                                .map(function(li) { return li.getAttribute('data-step-index'); });
                            if (indices.length === 0) return;
                            var root = $el.closest('[wire\\\\:id]');
                            if (root) {
                                var id = root.getAttribute('wire:id');
                                var c = window.Livewire.find(id);
                                if (c) c.call('reorderSteps', indices);
                            }
                        }
                    });
                ">
            @foreach($steps as $index => $step)
                @php $stepKey = $step['uid'] ?? ($step['id'] ?? "step-$index"); @endphp

                <li class="border rounded-2xl overflow-hidden bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 edit-step-item"
                    data-step-index="{{ $index }}"
                    wire:key="step-{{ $stepKey }}">
                    <div class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-gray-100 bg-white/40 dark:bg-gray-900/20 flex items-center justify-between gap-2">
                        <span class="flex items-center gap-2">
                            <span class="edit-step-drag-handle cursor-move text-gray-400 hover:text-gray-600 select-none" title="{{ __('app.trip.route.drag_hint') ?? 'Velciet, lai mainītu secību' }}">⋮⋮</span>
                            {{ __('app.trip.edit.step_n', ['n' => $index + 1]) }}
                        </span>
                    </div>

                    <div class="px-4 py-4 space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    {{ __('app.trip.edit.type') }} {!! $reqBadge() !!}
                                </label>
                                <select wire:model.blur="steps.{{ $index }}.type" @class([$baseInput, $errInput => $errors->has("steps.$index.type"), 'input-error' => $errors->has("steps.$index.type")])>
                                    <option value="loading">{{ __('app.trip.edit.loading') }}</option>
                                    <option value="unloading">{{ __('app.trip.edit.unloading') }}</option>
                                </select>
                                @error("steps.$index.type") <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    {{ __('app.trip.edit.date_time') }} {!! $reqBadge() !!}
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="date" wire:model.blur="steps.{{ $index }}.date" @class([$baseInput, $errInput => $errors->has("steps.$index.date"), 'input-error' => $errors->has("steps.$index.date")])>
                                    <input type="time" wire:model.blur="steps.{{ $index }}.time" @class([$baseInput, $errInput => $errors->has("steps.$index.time"), 'input-error' => $errors->has("steps.$index.time")])>
                                </div>
                                @error("steps.$index.date") <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    {{ __('app.trip.edit.country') }} {!! $reqBadge() !!}
                                </label>
                                <select wire:model.live="steps.{{ $index }}.country_id" @class([$baseInput, $errInput => $errors->has("steps.$index.country_id"), 'input-error' => $errors->has("steps.$index.country_id")])>
                                    <option value="">— {{ __('app.trip.edit.choose') }} —</option>
                                    @foreach($countries as $countryId => $country)
                                        <option value="{{ $countryId }}">{{ is_array($country) ? ($country['name'] ?? $country) : $country }}</option>
                                    @endforeach
                                </select>
                                @error("steps.$index.country_id") <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    {{ __('app.trip.edit.city') }} {!! $reqBadge() !!}
                                </label>
                                <select wire:model.live="steps.{{ $index }}.city_id" @class([$baseInput, $errInput => $errors->has("steps.$index.city_id"), 'input-error' => $errors->has("steps.$index.city_id")])>
                                    <option value="">— {{ __('app.trip.edit.choose') }} —</option>
                                    @foreach(($stepCities[$index]['cities'] ?? []) as $cityId => $city)
                                        <option value="{{ $cityId }}">{{ is_array($city) ? ($city['name'] ?? ('#'.$cityId)) : $city }}</option>
                                    @endforeach
                                </select>
                                @error("steps.$index.city_id") <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    {{ __('app.trip.edit.address') }} {!! $reqBadge() !!}
                                </label>
                                <input type="text" wire:model.blur="steps.{{ $index }}.address" @class([$baseInput, $errInput => $errors->has("steps.$index.address"), 'input-error' => $errors->has("steps.$index.address")])>
                                @error("steps.$index.address") <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    {{ __('app.trip.step.contact_phone_1') }}
                                </label>
                                <input type="tel" wire:model.blur="steps.{{ $index }}.contact_phone_1" @class([$baseInput]) placeholder="+371 12345678">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    {{ __('app.trip.step.contact_phone_2') }}
                                </label>
                                <input type="tel" wire:model.blur="steps.{{ $index }}.contact_phone_2" @class([$baseInput]) placeholder="+371 12345678">
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            @if(count($steps) > 1)
                                <button type="button"
                                        wire:click="removeStep({{ $index }})"
                                        class="text-xs text-red-600 hover:text-red-700 px-3 py-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20">
                                    ✕ {{ __('app.trip.edit.remove_step') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
            </ul>
            @else
                <div class="text-xs text-gray-500">
                    {{ __('app.trip.edit.no_steps') }}
                </div>
            @endif
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
                    🔗 {{ __('app.trip.edit.steps_for_cargos') }}
                </h2>
                <div class="text-[11px] text-gray-500 dark:text-gray-400">
                    {{ __('app.trip.edit.steps_apply_all') }}
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                {{-- LOADING --}}
                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-xs font-semibold text-gray-700 dark:text-gray-200">
                            {{ __('app.trip.edit.loading_label') }} {!! $reqBadge() !!}
                        </div>

                        <button type="button"
                                wire:click="$set('trip_loading_step_ids', [])"
                                class="text-[11px] px-2 py-1 rounded-lg text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-white hover:bg-white/60 dark:hover:bg-gray-900/30">
                            {{ __('app.trip.edit.clear') }}
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
                            {{ __('app.trip.edit.unloading_label') }} {!! $reqBadge() !!}
                        </div>

                        <button type="button"
                                wire:click="$set('trip_unloading_step_ids', [])"
                                class="text-[11px] px-2 py-1 rounded-lg text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-white hover:bg-white/60 dark:hover:bg-gray-900/30">
                            {{ __('app.trip.edit.clear') }}
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
                        {{ __('app.trip.edit.steps_lu_hint') }}
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
                    📦 {{ __('app.trip.edit.cargos_title') }}
                </h2>

                <button type="button"
                        wire:click="addCargo"
                        class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold
                               bg-green-600 hover:bg-green-700 text-white shadow-sm">
                    ➕ {{ __('app.trip.edit.add_cargo') }}
                </button>
            </div>

            @forelse($cargos as $index => $cargo)
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800"
                     wire:key="cargo-{{ $cargo['uid'] ?? $index }}">

                    <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100 dark:border-gray-800">
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                            {{ __('app.trip.edit.cargo_n', ['n' => $index + 1]) }}
                        </div>

                        @if(count($cargos) > 1)
                            <button type="button"
                                    wire:click="removeCargo({{ $index }})"
                                    class="text-xs text-red-500 hover:text-red-600 px-2 py-1 rounded-lg hover:bg-red-50">
                                ✕ {{ __('app.trip.edit.remove') }}
                            </button>
                        @endif
                    </div>

                    <div class="px-4 py-4 space-y-4">

                        {{-- Top: parties --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('app.trip.edit.customer') }} {!! $reqBadge() !!}</label>
                                <select wire:model.live="cargos.{{ $index }}.customer_id" class="{{ $baseInput }}">
                                    <option value="">— {{ __('app.trip.edit.choose') }} —</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                    @endforeach
                                </select>
                                @error("cargos.$index.customer_id") <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('app.trip.edit.shipper') }} {!! $reqBadge() !!}</label>
                                <select wire:model.live="cargos.{{ $index }}.shipper_id" class="{{ $baseInput }}">
                                    <option value="">— {{ __('app.trip.edit.choose') }} —</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                    @endforeach
                                </select>
                                @error("cargos.$index.shipper_id") <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('app.trip.edit.consignee') }} {!! $reqBadge() !!}</label>
                                <select wire:model.live="cargos.{{ $index }}.consignee_id" class="{{ $baseInput }}">
                                    <option value="">— {{ __('app.trip.edit.choose') }} —</option>
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
                                    {{ __('app.trip.edit.linked_steps') }}
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
                                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('app.trip.edit.price') }}</label>
                                <input type="text" wire:model.live="cargos.{{ $index }}.price" class="{{ $baseInput }} text-xs">
                                @error("cargos.$index.price") <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('app.trip.edit.vat_percent') }}</label>
                                <select wire:model.live="cargos.{{ $index }}.tax_percent" class="{{ $baseInput }} text-xs">
                                    @foreach($taxRates as $rate)
                                        <option value="{{ $rate }}">{{ $rate }}</option>
                                    @endforeach
                                </select>
                                @error("cargos.$index.tax_percent") <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('app.trip.edit.vat_amount') }}</label>
                                <input type="number" wire:model.defer="cargos.{{ $index }}.total_tax_amount" class="{{ $baseInput }} text-xs" readonly>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('app.trip.edit.total_with_vat') }}</label>
                                <input type="number" wire:model.defer="cargos.{{ $index }}.price_with_tax" class="{{ $baseInput }} text-xs" readonly>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('app.trip.edit.payment_by') }}</label>
                                <select wire:model.blur="cargos.{{ $index }}.payment_days" class="{{ $baseInput }} text-xs">
                                    @foreach([7, 14, 21, 30] as $days)
                                        <option value="{{ $days }}">{{ __('app.trip.edit.payment_days', ['days' => $days]) }}</option>
                                    @endforeach
                                </select>
                                @if(isset($trip->cargos[$index]) && $trip->cargos[$index]->inv_created_at)
                                    <div class="text-[10px] text-gray-500 mt-0.5">{{ __('app.trip.edit.payment_from_inv') }}</div>
                                @endif
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('app.trip.edit.payer') }}</label>
                                <select wire:model.live="cargos.{{ $index }}.payer_type_id" class="{{ $baseInput }} text-xs">
                                    <option value="">{{ __('app.trip.edit.payer_not_selected') }}</option>
                                    @foreach($payers as $payerId => $payer)
                                        <option value="{{ $payerId }}">{{ $payer['label'] ?? $payerId }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- commercial invoice --}}
                        <div class="grid grid-cols-1 sm:grid-cols-6 gap-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('app.trip.edit.commercial_inv_nr') }}</label>
                                <input type="text" wire:model.blur="cargos.{{ $index }}.commercial_invoice_nr" class="{{ $baseInput }} text-xs">
                                @error("cargos.$index.commercial_invoice_nr") <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('app.trip.edit.commercial_inv_amount') }}</label>
                                <input type="text" wire:model.blur="cargos.{{ $index }}.commercial_invoice_amount" class="{{ $baseInput }} text-xs">
                                @error("cargos.$index.commercial_invoice_amount") <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            <button type="button"
                                    wire:click="addItem({{ $index }})"
                                    class="text-xs px-3 py-1.5 rounded-lg bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200">
                                ➕ {{ __('app.trip.edit.add_item') }}
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
                                            {{ __('app.trip.edit.item_n', ['n' => $itemIndex + 1]) }}
                                        </div>

                                        @if(count($cargo['items'] ?? []) > 1)
                                            <button type="button"
                                                    wire:click="removeItem({{ $index }}, {{ $itemIndex }})"
                                                    class="text-xs text-red-600 hover:text-red-700 px-2 py-1 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20">
                                                ✕ {{ __('app.trip.edit.remove_item') }}
                                            </button>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-6 gap-3">
                                        <div class="sm:col-span-3">
                                            <label class="block text-[11px] text-gray-500 mb-1">{{ __('app.trip.edit.description') }}</label>
                                            <input type="text"
                                                   wire:model.blur="cargos.{{ $index }}.items.{{ $itemIndex }}.description"
                                                   class="{{ $baseInput }} text-xs">
                                            @error("cargos.$index.items.$itemIndex.description")
                                                <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="sm:col-span-1">
                                            <label class="block text-[11px] text-gray-500 mb-1">{{ __('app.trip.edit.hs_customs') }}</label>
                                            <input type="text"
                                                   wire:model.blur="cargos.{{ $index }}.items.{{ $itemIndex }}.customs_code"
                                                   class="{{ $baseInput }} text-xs">
                                            @error("cargos.$index.items.$itemIndex.customs_code")
                                                <div class="text-[11px] text-red-600 mt-1">❗ {{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">{{ __('app.trip.edit.packages') }}</label>
                                            <input type="number" step="1"
                                                   wire:model.blur="cargos.{{ $index }}.items.{{ $itemIndex }}.packages"
                                                   class="{{ $baseInput }} text-xs">
                                        </div>

                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">{{ __('app.trip.edit.pallets') }}</label>
                                            <input type="number" step="1"
                                                   wire:model.blur="cargos.{{ $index }}.items.{{ $itemIndex }}.pallets"
                                                   class="{{ $baseInput }} text-xs">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 sm:grid-cols-6 gap-3 mt-3">
                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">{{ __('app.trip.edit.units') }}</label>
                                            <input type="number" step="1"
                                                   wire:model.blur="cargos.{{ $index }}.items.{{ $itemIndex }}.units"
                                                   class="{{ $baseInput }} text-xs">
                                        </div>

                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">{{ __('app.trip.edit.net_kg') }}</label>
                                            <input type="number" step="0.001"
                                                   wire:model.blur="cargos.{{ $index }}.items.{{ $itemIndex }}.net_weight"
                                                   class="{{ $baseInput }} text-xs">
                                        </div>

                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">{{ __('app.trip.edit.gross_kg') }}</label>
                                            <input type="number" step="0.001"
                                                   wire:model.blur="cargos.{{ $index }}.items.{{ $itemIndex }}.gross_weight"
                                                   class="{{ $baseInput }} text-xs">
                                        </div>

                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">{{ __('app.trip.edit.tonnes') }}</label>
                                            <input type="number" step="0.001"
                                                   wire:model.blur="cargos.{{ $index }}.items.{{ $itemIndex }}.tonnes"
                                                   class="{{ $baseInput }} text-xs">
                                        </div>

                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">{{ __('app.trip.edit.volume') }}</label>
                                            <input type="number" step="0.001"
                                                   wire:model.blur="cargos.{{ $index }}.items.{{ $itemIndex }}.volume"
                                                   class="{{ $baseInput }} text-xs">
                                        </div>

                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">{{ __('app.trip.edit.lm') }}</label>
                                            <input type="number" step="0.001"
                                                   wire:model.blur="cargos.{{ $index }}.items.{{ $itemIndex }}.loading_meters"
                                                   class="{{ $baseInput }} text-xs">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3">
                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">{{ __('app.trip.edit.adr_hazmat') }}</label>
                                            <input type="text"
                                                   wire:model.blur="cargos.{{ $index }}.items.{{ $itemIndex }}.hazmat"
                                                   class="{{ $baseInput }} text-xs">
                                        </div>

                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">{{ __('app.trip.edit.temperature') }}</label>
                                            <input type="text"
                                                   wire:model.blur="cargos.{{ $index }}.items.{{ $itemIndex }}.temperature"
                                                   class="{{ $baseInput }} text-xs">
                                        </div>

                                        <div class="flex items-end">
                                            <label class="inline-flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                                <input type="checkbox"
                                                       wire:model.blur="cargos.{{ $index }}.items.{{ $itemIndex }}.stackable"
                                                       class="rounded border-gray-300">
                                                {{ __('app.trip.edit.stackable') }}
                                            </label>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">{{ __('app.trip.edit.instructions') }}</label>
                                            <input type="text"
                                                   wire:model.blur="cargos.{{ $index }}.items.{{ $itemIndex }}.instructions"
                                                   class="{{ $baseInput }} text-xs">
                                        </div>

                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">{{ __('app.trip.edit.remarks') }}</label>
                                            <input type="text"
                                                   wire:model.blur="cargos.{{ $index }}.items.{{ $itemIndex }}.remarks"
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
                    {{ __('app.trip.edit.no_cargos') }}
                </div>
            @endforelse
        </section>

    </div>

    {{-- BOTTOM BAR (в потоке страницы, скроллится вместе с формой) --}}
    <div class="mt-8 pt-4 border-t border-amber-200 dark:border-amber-900/40 bg-white/95 dark:bg-gray-900/95">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
            <div class="text-xs text-gray-500 truncate">
                {{ __('app.trip.edit.bottom_hint') }}
            </div>
            <button
                wire:click="save"
                wire:loading.attr="disabled"
                class="inline-flex items-center px-4 py-2 rounded-2xl text-sm font-semibold
                       bg-amber-600 hover:bg-amber-700 disabled:bg-amber-400 text-white shadow">
                <span wire:loading.remove>💾 {{ __('app.trip.edit.save') }}</span>
                <span wire:loading>⏳ {{ __('app.trip.edit.saving') }}</span>
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener("livewire:initialized", () => {
    Livewire.hook('message.processed', () => {
        const firstError = document.querySelector('.input-error');
        if (!firstError) return;
        firstError.focus({ preventScroll: true });
        var scrollContainer = document.querySelector('main');
        if (!scrollContainer || scrollContainer.scrollHeight <= scrollContainer.clientHeight) {
            scrollContainer = null;
        }
        var yOffset = -100;
        if (scrollContainer) {
            var rect = firstError.getBoundingClientRect();
            var containerRect = scrollContainer.getBoundingClientRect();
            var scrollTop = scrollContainer.scrollTop + (rect.top - containerRect.top) + yOffset;
            scrollContainer.scrollTo({ top: Math.max(0, scrollTop), behavior: 'smooth' });
        } else {
            var y = firstError.getBoundingClientRect().top + window.scrollY + yOffset;
            window.scrollTo({ top: y, behavior: 'smooth' });
        }
    });
});
</script>
