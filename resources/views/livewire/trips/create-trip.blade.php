{{-- resources/views/livewire/trips/create-trip.blade.php --}}
<div class="min-h-screen bg-gray-100 dark:bg-gray-900 pb-24">

    {{-- =========================
         HEADER
    ========================== --}}
    <div class="sticky top-0 z-20 bg-white/90 dark:bg-gray-900/90 border-b border-gray-200 dark:border-gray-700 backdrop-blur">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
            <h1 class="text-lg sm:text-2xl font-semibold text-gray-800 dark:text-gray-100 truncate">
                🚛 Новый рейс (multi-cargo)
            </h1>

            <button
                wire:click="save"
                wire:loading.attr="disabled"
                class="hidden sm:inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold
                       bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white shadow">
                <span wire:loading.remove>💾 Сохранить</span>
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
                <div class="font-semibold mb-1">Ошибки при сохранении:</div>
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
             EXPEDITOR
        ========================== --}}
        <section class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm px-4 py-4 sm:px-6 sm:py-5 space-y-4">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-base sm:text-lg font-semibold text-gray-800 dark:text-gray-100">
                    🧾 Экспедитор
                </h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-start">
                <div class="space-y-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">
                        Выберите экспедитора
                    </label>
                    <select
                        wire:model.live="expeditor_id"
                        class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                        <option value="">— выберите экспедитора —</option>
                        @foreach($expeditors as $id => $exp)
                            <option value="{{ $id }}">{{ $exp['name'] }}</option>
                        @endforeach
                    </select>

                    @error('expeditor_id')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror

                    <label class="block text-xs font-medium text-gray-500 mb-1 mt-3">
                        Банковский счёт
                    </label>
                    <select
                        wire:model.live="bank_index"
                        class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                        <option value="">— выберите банк —</option>
                        @foreach($banks ?? [] as $idx => $bank)
                            <option value="{{ $idx }}">{{ $bank['name'] }}</option>
                        @endforeach
                    </select>

                    @error('bank_index')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl px-4 py-3 text-xs sm:text-sm space-y-1.5 border border-gray-200 dark:border-gray-700">
                        <div class="font-semibold text-gray-800 dark:text-gray-100 flex items-center justify-between gap-2">
                            <span class="truncate">{{ $expeditorData['name'] ?? '—' }}</span>
                            <span class="text-[10px] text-gray-500">
                                ID: {{ $expeditor_id ?: '—' }}
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

                        <div class="text-gray-700 dark:text-gray-200 pt-1 border-top border-gray-200/70 dark:border-gray-700/70 mt-1">
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
            </div>
        </section>

        {{-- =========================
             TRANSPORT
        ========================== --}}
        <section class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm px-4 py-4 sm:px-6 sm:py-5 space-y-4">
            <h2 class="text-base sm:text-lg font-semibold text-gray-800 dark:text-gray-100">
                🚚 Транспорт
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                {{-- Driver --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">
                        Водитель
                    </label>
                    <select
                        wire:model.live="driver_id"
                        class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                        <option value="">— выбрать —</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}">
                                {{ $driver->first_name }} {{ $driver->last_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('driver_id')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Truck --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">
                        Тягач
                    </label>
                    <select
                        wire:model.live="truck_id"
                        class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                        <option value="">— выбрать —</option>
                        @foreach($trucks as $truck)
                            <option value="{{ $truck->id }}">
                                {{ $truck->plate }} ({{ $truck->brand }} {{ $truck->model }})
                            </option>
                        @endforeach
                    </select>
                    @error('truck_id')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Trailer --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">
                        Прицеп
                    </label>
                    <select
                        wire:model.live="trailer_id"
                        class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                        <option value="">— без прицепа —</option>
                        @foreach($trailers as $trailer)
                            <option value="{{ $trailer->id }}">
                                {{ $trailer->plate }} ({{ $trailer->brand }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-2">
                {{-- Start date --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">
                        Дата начала
                    </label>
                    <input type="date"
                           wire:model.defer="start_date"
                           class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                    @error('start_date')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- End date --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">
                        Дата окончания
                    </label>
                    <input type="date"
                           wire:model.defer="end_date"
                           class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                    @error('end_date')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Currency --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">
                        Валюта рейса
                    </label>
                    <input type="text"
                           wire:model.defer="currency"
                           class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                    @error('currency')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </section>

        {{-- =========================
             STEPS (МАРШРУТ / TripStep)
        ========================== --}}
        <section class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm px-4 py-4 sm:px-6 sm:py-5 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base sm:text-lg font-semibold text-gray-800 dark:text-gray-100">
                    🧭 Маршрут (steps)
                </h2>

                <button
                    type="button"
                    wire:click="addStep"
                    class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold
                           bg-blue-600 hover:bg-blue-700 text-white shadow-sm">
                    ➕ Добавить шаг
                </button>
            </div>

            @forelse($steps as $index => $step)
                @php
                    $type = $step['type'] ?? 'loading';
                    $typeLabel = $type === 'loading' ? 'Погрузка' : 'Разгрузка';
                    $icon = $type === 'loading' ? '⬆' : '⬇';

                    $stepCountry = !empty($step['country_id']) ? getCountryById($step['country_id']) : null;
                    $stepCity = (!empty($step['country_id']) && !empty($step['city_id']))
                        ? getCityNameByCountryId($step['country_id'], $step['city_id'])
                        : null;

                    if ($stepCity && $stepCountry) {
                        $stepLocation = $stepCity . ', ' . $stepCountry;
                    } elseif ($stepCity) {
                        $stepLocation = $stepCity;
                    } elseif ($stepCountry) {
                        $stepLocation = $stepCountry;
                    } else {
                        $stepLocation = '—';
                    }

                    $date = $step['date'] ?? null;
                    $time = $step['time'] ?? null;
                    $dateTimeShort = $date ? ($date . ($time ? ' '.$time : '')) : '—';

                    $addrKey = "steps.$index.address";
                    $addrErr = $errors->has($addrKey);
                @endphp

                <div
                    wire:key="step-{{ $step['uid'] ?? $index }}"
                    x-data="{ open: true }"
                    class="border border-gray-200 dark:border-gray-700 rounded-2xl bg-gray-50 dark:bg-gray-800 overflow-hidden">

                    {{-- STEP HEADER --}}
                    <button type="button"
                            class="w-full flex items-center justify-between px-4 py-2 text-sm font-medium text-left
                                   text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-800"
                            @click="open = !open">
                        <div class="flex items-center gap-2">
                            <span x-show="open">▾</span>
                            <span x-show="!open">▸</span>

                            <span class="font-semibold">
                                Шаг #{{ $index + 1 }}
                            </span>

                            <span class="inline-flex flex-wrap items-center gap-1 text-xs text-gray-600 dark:text-gray-300">
                                <span>{{ $icon }}</span>
                                <span>{{ $typeLabel }}</span>
                                <span class="text-gray-400">•</span>
                                <span>{{ $stepLocation }}</span>
                                <span class="text-gray-400">•</span>
                                <span>{{ $dateTimeShort }}</span>
                                @if($addrErr)
                                    <span class="text-gray-400">•</span>
                                    <span class="text-red-600 font-semibold">❗ Адрес обязателен</span>
                                @endif
                            </span>
                        </div>

                        @if(count($steps) > 1)
                            <button type="button"
                                    wire:click="removeStep({{ $index }})"
                                    class="text-xs text-red-500 hover:text-red-600 px-2 py-1 rounded-lg hover:bg-red-50"
                                    @click.stop>
                                ✕ Удалить
                            </button>
                        @endif
                    </button>

                    {{-- STEP BODY --}}
                    <div x-show="open" x-collapse class="px-4 py-4 space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            {{-- Тип шага --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Тип шага</label>
                                <select
                                    wire:model.live="steps.{{ $index }}.type"
                                    class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-xs">
                                    <option value="loading">Погрузка</option>
                                    <option value="unloading">Разгрузка</option>
                                </select>
                                @error("steps.$index.type")
                                    <div class="text-[11px] text-red-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Дата / время --}}
                            <div class="space-y-1.5 sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Дата / время</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="date"
                                           wire:model.live="steps.{{ $index }}.date"
                                           class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-xs">
                                    <input type="time"
                                           wire:model.live="steps.{{ $index }}.time"
                                           class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-xs">
                                </div>
                                @error("steps.$index.date")
                                    <div class="text-[11px] text-red-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Порядок --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Порядок (order)</label>
                                <input type="number"
                                       wire:model.live="steps.{{ $index }}.order"
                                       class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-xs"
                                       placeholder="#">
                                @error("steps.$index.order")
                                    <div class="text-[11px] text-red-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Локация --}}
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            {{-- Страна --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Страна</label>
                                <select
                                    wire:model.live="steps.{{ $index }}.country_id"
                                    class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-xs">
                                    <option value="">— выбрать —</option>
                                    @foreach($countries as $countryId => $country)
                                        <option value="{{ $countryId }}">{{ $country['name'] }}</option>
                                    @endforeach
                                </select>
                                @error("steps.$index.country_id")
                                    <div class="text-[11px] text-red-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Город --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Город</label>
                                <select
                                    wire:model.live="steps.{{ $index }}.city_id"
                                    class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-xs">
                                    <option value="">— выбрать —</option>
                                    @foreach(($stepCities[$index]['cities'] ?? []) as $cityId => $city)
                                        <option value="{{ $cityId }}">{{ $city['name'] ?? ('#'.$cityId) }}</option>
                                    @endforeach
                                </select>
                                @error("steps.$index.city_id")
                                    <div class="text-[11px] text-red-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Адрес --}}
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Адрес</label>
                                <input type="text"
                                       wire:model.live="steps.{{ $index }}.address"
                                       class="w-full rounded-xl border text-xs
                                            @if($addrErr) border-red-500 input-error @else border-gray-300 dark:border-gray-700 @endif
                                            dark:bg-gray-900 dark:text-gray-100">
                                @error("steps.$index.address")
                                    <div class="text-[11px] text-red-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Заметки --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Заметки (notes)</label>
                            <textarea
                                rows="2"
                                wire:model.live="steps.{{ $index }}.notes"
                                class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-xs"></textarea>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-xs text-gray-500">
                    Пока нет ни одного шага. Нажмите «Добавить шаг».
                </div>
            @endforelse
        </section>

        {{-- =========================
             CARGOS (MULTI-CARGO)
        ========================== --}}
        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base sm:text-lg font-semibold text-gray-800 dark:text-gray-100">
                    📦 Грузы (multi-cargo)
                </h2>

                <button
                    type="button"
                    wire:click="addCargo"
                    class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold
                           bg-green-600 hover:bg-green-700 text-white shadow-sm">
                    ➕ Добавить груз
                </button>
            </div>

            @forelse($cargos as $index => $cargo)
                @php
                    $customer  = $cargo['customer_id']  ? $clients->firstWhere('id', $cargo['customer_id'])  : null;
                    $shipper   = $cargo['shipper_id']   ? $clients->firstWhere('id', $cargo['shipper_id'])   : null;
                    $consignee = $cargo['consignee_id'] ? $clients->firstWhere('id', $cargo['consignee_id']) : null;

                    $summaryParts = [];
                    if (!empty($cargo['price_with_tax'])) {
                        $summaryParts[] = number_format((float)$cargo['price_with_tax'], 2, '.', ' ') . ' € с НДС';
                    }
                    if (!empty($cargo['loading_step_ids'] ?? []) && count($cargo['loading_step_ids']) > 0) {
                        $firstIndex = $cargo['loading_step_ids'][0];
                        if (isset($steps[$firstIndex])) {
                            $st = $steps[$firstIndex];
                            $country = !empty($st['country_id']) ? getCountryById($st['country_id']) : null;
                            $city = (!empty($st['country_id']) && !empty($st['city_id']))
                                ? getCityNameByCountryId($st['country_id'], $st['city_id'])
                                : null;
                            $from = $city ?: $country;
                            if ($from) $summaryParts[] = 'от ' . $from;
                        }
                    }
                    if (!empty($cargo['unloading_step_ids'] ?? []) && count($cargo['unloading_step_ids']) > 0) {
                        $firstIndex = $cargo['unloading_step_ids'][0];
                        if (isset($steps[$firstIndex])) {
                            $st = $steps[$firstIndex];
                            $country = !empty($st['country_id']) ? getCountryById($st['country_id']) : null;
                            $city = (!empty($st['country_id']) && !empty($st['city_id']))
                                ? getCityNameByCountryId($st['country_id'], $st['city_id'])
                                : null;
                            $to = $city ?: $country;
                            if ($to) $summaryParts[] = 'до ' . $to;
                        }
                    }
                @endphp

                <div
                    wire:key="cargo-{{ $cargo['uid'] ?? $index }}"
                    x-data="{ open: true }"
                    class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">

                    {{-- CARGO HEADER --}}
                    <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100 dark:border-gray-800">
                        <button type="button"
                                class="flex items-center gap-2 text-sm font-semibold text-gray-800 dark:text-gray-100"
                                @click="open = !open">
                            <span x-show="open">▾</span>
                            <span x-show="!open">▸</span>
                            <span>Груз #{{ $index + 1 }}</span>

                            @if($summaryParts)
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    — {{ implode(' / ', $summaryParts) }}
                                </span>
                            @endif
                        </button>

                        @if(count($cargos) > 1)
                            <button
                                type="button"
                                wire:click="removeCargo({{ $index }})"
                                class="text-xs text-red-500 hover:text-red-600 px-2 py-1 rounded-lg hover:bg-red-50">
                                ✕ Удалить
                            </button>
                        @endif
                    </div>

                    {{-- CARGO BODY --}}
                    <div x-show="open" x-collapse class="px-4 py-4 sm:px-5 sm:py-5 space-y-4">

                        {{-- ===== Клиенты: Customer / Shipper / Consignee ===== --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

                            {{-- Customer --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Заказчик (customer)</label>
                                <select
                                    wire:model.live="cargos.{{ $index }}.customer_id"
                                    class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-xs sm:text-sm">
                                    <option value="">— не указан —</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                    @endforeach
                                </select>
                                @error("cargos.$index.customer_id")
                                    <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div>
                                @enderror

                                @if($customer)
                                    <div class="mt-2 text-[11px] bg-gray-50 dark:bg-gray-800 rounded-xl px-2 py-1.5 border border-gray-200 dark:border-gray-700 space-y-0.5">
                                        <div class="font-semibold truncate">{{ $customer->company_name }}</div>
                                        <div>Reg/VAT: <span class="font-medium">{{ $customer->reg_nr ?? '—' }}</span></div>

                                        @php
                                            $country = $customer->jur_country_id ? getCountryById($customer->jur_country_id) : null;
                                            $city    = ($customer->jur_country_id && $customer->jur_city_id)
                                                ? getCityNameByCountryId($customer->jur_country_id, $customer->jur_city_id)
                                                : null;
                                        @endphp

                                        <div>Country/City:
                                            <span class="font-medium">{{ $country ?? '—' }}{{ $city ? ', '.$city : '' }}</span>
                                        </div>

                                        <div>Address:
                                            <span class="font-medium">
                                                {{ $customer->jur_address ?? '—' }}
                                                @if($customer->jur_post_code)
                                                    , {{ $customer->jur_post_code }}
                                                @endif
                                            </span>
                                        </div>

                                        <div>Phone: <span class="font-medium">{{ $customer->phone ?? '—' }}</span></div>
                                        <div>Email: <span class="font-medium">{{ $customer->email ?? '—' }}</span></div>
                                        <div>Bank: <span class="font-medium">{{ $customer->bank_name ?? '—' }}</span></div>
                                        <div>BIC: <span class="font-medium">{{ $customer->swift ?? '—' }}</span></div>
                                        <div>Contact: <span class="font-medium">{{ $customer->representative ?? '—' }}</span></div>
                                    </div>
                                @endif
                            </div>

                            {{-- Shipper --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Грузоотправитель (shipper)</label>
                                <select
                                    wire:model.live="cargos.{{ $index }}.shipper_id"
                                    class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-xs sm:text-sm">
                                    <option value="">— не указан —</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                    @endforeach
                                </select>
                                @error("cargos.$index.shipper_id")
                                    <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div>
                                @enderror

                                @if($shipper)
                                    <div class="mt-2 text-[11px] bg-gray-50 dark:bg-gray-800 rounded-xl px-2 py-1.5 border border-gray-200 dark:border-gray-700 space-y-0.5">
                                        <div class="font-semibold truncate">{{ $shipper->company_name }}</div>
                                        <div>Reg/VAT: <span class="font-medium">{{ $shipper->reg_nr ?? '—' }}</span></div>

                                        @php
                                            $country = $shipper->jur_country_id ? getCountryById($shipper->jur_country_id) : null;
                                            $city    = ($shipper->jur_country_id && $shipper->jur_city_id)
                                                ? getCityNameByCountryId($shipper->jur_country_id, $shipper->jur_city_id)
                                                : null;
                                        @endphp

                                        <div>Country/City:
                                            <span class="font-medium">{{ $country ?? '—' }}{{ $city ? ', '.$city : '' }}</span>
                                        </div>

                                        <div>Address:
                                            <span class="font-medium">
                                                {{ $shipper->jur_address ?? '—' }}
                                                @if($shipper->jur_post_code)
                                                    , {{ $shipper->jur_post_code }}
                                                @endif
                                            </span>
                                        </div>

                                        <div>Phone: <span class="font-medium">{{ $shipper->phone ?? '—' }}</span></div>
                                        <div>Email: <span class="font-medium">{{ $shipper->email ?? '—' }}</span></div>
                                        <div>Bank: <span class="font-medium">{{ $shipper->bank_name ?? '—' }}</span></div>
                                        <div>BIC: <span class="font-medium">{{ $shipper->swift ?? '—' }}</span></div>
                                        <div>Contact: <span class="font-medium">{{ $shipper->representative ?? '—' }}</span></div>
                                    </div>
                                @endif
                            </div>

                            {{-- Consignee --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Грузополучатель (consignee)</label>
                                <select
                                    wire:model.live="cargos.{{ $index }}.consignee_id"
                                    class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-xs sm:text-sm">
                                    <option value="">— не указан —</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                    @endforeach
                                </select>
                                @error("cargos.$index.consignee_id")
                                    <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div>
                                @enderror

                                @if($consignee)
                                    <div class="mt-2 text-[11px] bg-gray-50 dark:bg-gray-800 rounded-xl px-2 py-1.5 border border-gray-200 dark:border-gray-700 space-y-0.5">
                                        <div class="font-semibold truncate">{{ $consignee->company_name }}</div>
                                        <div>Reg/VAT: <span class="font-medium">{{ $consignee->reg_nr ?? '—' }}</span></div>

                                        @php
                                            $country = $consignee->jur_country_id ? getCountryById($consignee->jur_country_id) : null;
                                            $city    = ($consignee->jur_country_id && $consignee->jur_city_id)
                                                ? getCityNameByCountryId($consignee->jur_country_id, $consignee->jur_city_id)
                                                : null;
                                        @endphp

                                        <div>Country/City:
                                            <span class="font-medium">{{ $country ?? '—' }}{{ $city ? ', '.$city : '' }}</span>
                                        </div>

                                        <div>Address:
                                            <span class="font-medium">
                                                {{ $consignee->jur_address ?? '—' }}
                                                @if($consignee->jur_post_code)
                                                    , {{ $consignee->jur_post_code }}
                                                @endif
                                            </span>
                                        </div>

                                        <div>Phone: <span class="font-medium">{{ $consignee->phone ?? '—' }}</span></div>
                                        <div>Email: <span class="font-medium">{{ $consignee->email ?? '—' }}</span></div>
                                        <div>Bank: <span class="font-medium">{{ $consignee->bank_name ?? '—' }}</span></div>
                                        <div>BIC: <span class="font-medium">{{ $consignee->swift ?? '—' }}</span></div>
                                        <div>Contact: <span class="font-medium">{{ $consignee->representative ?? '—' }}</span></div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- ===== Привязка к шагам маршрута (multi-select) ===== --}}
                        <div class="border-t border-gray-100 dark:border-gray-800 pt-3 mt-2 space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                                {{-- Мультивыбор погрузки --}}
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-xs font-medium text-gray-500">⬆ Шаги погрузки (multi-select)</label>
                                        <span class="text-[10px] text-gray-400">Можно выбрать несколько</span>
                                    </div>

                                    <div class="space-y-2">
                                        @forelse($steps as $sIndex => $st)
                                            @php $t = $st['type'] ?? 'loading'; @endphp
                                            @continue($t !== 'loading')

                                            @php
                                                $country = !empty($st['country_id']) ? getCountryById($st['country_id']) : null;
                                                $city = (!empty($st['country_id']) && !empty($st['city_id']))
                                                    ? getCityNameByCountryId($st['country_id'], $st['city_id'])
                                                    : null;
                                                $location = $city ?: $country ?: '—';

                                                $date = $st['date'] ?? null;
                                                $time = $st['time'] ?? null;
                                                $dateFormatted = $date
                                                    ? \Carbon\Carbon::parse($date.' '.($time ?: '00:00'))->format('d.m.Y H:i')
                                                    : '—';
                                            @endphp

                                            <label class="block" wire:key="cargo-{{ $cargo['uid'] ?? $index }}-load-step-{{ $st['uid'] ?? $sIndex }}">
                                                <input
                                                    type="checkbox"
                                                    class="peer hidden"
                                                    value="{{ $sIndex }}"
                                                    wire:model="cargos.{{ $index }}.loading_step_ids"
                                                >
                                                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 px-3 py-2 text-xs
                                                            bg-gray-50/80 dark:bg-gray-900/80
                                                            peer-checked:bg-blue-50 peer-checked:border-blue-500
                                                            peer-checked:shadow-sm transition-colors">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-[13px]">⬆</span>
                                                            <span class="font-semibold text-gray-800 dark:text-gray-100">
                                                                Шаг #{{ $sIndex + 1 }}
                                                            </span>
                                                        </div>
                                                        <span class="text-[11px] text-gray-500 peer-checked:text-blue-700">
                                                            {{ $dateFormatted }}
                                                        </span>
                                                    </div>
                                                    <div class="mt-0.5 text-[11px] text-gray-600 dark:text-gray-300">
                                                        {{ $location }}
                                                    </div>
                                                </div>
                                            </label>
                                        @empty
                                            <div class="text-[11px] text-gray-400">Сначала добавьте шаги маршрута.</div>
                                        @endforelse
                                    </div>

                                    @error("cargos.$index.loading_step_ids")
                                        <div class="mt-1 text-[11px] text-red-500">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Мультивыбор разгрузки --}}
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-xs font-medium text-gray-500">⬇ Шаги разгрузки (multi-select)</label>
                                        <span class="text-[10px] text-gray-400">Можно выбрать несколько</span>
                                    </div>

                                    <div class="space-y-2">
                                        @forelse($steps as $sIndex => $st)
                                            @php $t = $st['type'] ?? 'loading'; @endphp
                                            @continue($t !== 'unloading')

                                            @php
                                                $country = !empty($st['country_id']) ? getCountryById($st['country_id']) : null;
                                                $city = (!empty($st['country_id']) && !empty($st['city_id']))
                                                    ? getCityNameByCountryId($st['country_id'], $st['city_id'])
                                                    : null;
                                                $location = $city ?: $country ?: '—';

                                                $date = $st['date'] ?? null;
                                                $time = $st['time'] ?? null;
                                                $dateFormatted = $date
                                                    ? \Carbon\Carbon::parse($date.' '.($time ?: '00:00'))->format('d.m.Y H:i')
                                                    : '—';
                                            @endphp

                                            <label class="block" wire:key="cargo-{{ $cargo['uid'] ?? $index }}-unload-step-{{ $st['uid'] ?? $sIndex }}">
                                                <input
                                                    type="checkbox"
                                                    class="peer hidden"
                                                    value="{{ $sIndex }}"
                                                    wire:model="cargos.{{ $index }}.unloading_step_ids"
                                                >
                                                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 px-3 py-2 text-xs
                                                            bg-gray-50/80 dark:bg-gray-900/80
                                                            peer-checked:bg-blue-50 peer-checked:border-blue-500
                                                            peer-checked:shadow-sm transition-colors">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-[13px]">⬇</span>
                                                            <span class="font-semibold text-gray-800 dark:text-gray-100">
                                                                Шаг #{{ $sIndex + 1 }}
                                                            </span>
                                                        </div>
                                                        <span class="text-[11px] text-gray-500 peer-checked:text-blue-700">
                                                            {{ $dateFormatted }}
                                                        </span>
                                                    </div>
                                                    <div class="mt-0.5 text-[11px] text-gray-600 dark:text-gray-300">
                                                        {{ $location }}
                                                    </div>
                                                </div>
                                            </label>
                                        @empty
                                            <div class="text-[11px] text-gray-400">Сначала добавьте шаги маршрута.</div>
                                        @endforelse
                                    </div>

                                    @error("cargos.$index.unloading_step_ids")
                                        <div class="mt-1 text-[11px] text-red-500">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                        </div>

                        {{-- Payment section --}}
                        <div class="grid grid-cols-2 sm:grid-cols-6 gap-3 border-t border-gray-100 dark:border-gray-800 pt-3 mt-2">
                            {{-- Цена без НДС --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Цена (без НДС)</label>
                                <input
                                    type="text"
                                    inputmode="decimal"
                                    wire:model.live="cargos.{{ $index }}.price"
                                    class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-xs">
                                @error("cargos.$index.price")
                                    <div class="text-[11px] text-red-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- НДС % --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">НДС, %</label>
                                <select
                                    wire:model.live="cargos.{{ $index }}.tax_percent"
                                    class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-xs">
                                    @foreach($taxRates as $rate)
                                        <option value="{{ $rate }}">{{ $rate }}</option>
                                    @endforeach
                                </select>
                                @error("cargos.$index.tax_percent")
                                    <div class="text-[11px] text-red-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Сумма НДС --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Сумма НДС</label>
                                <input
                                    type="number"
                                    wire:model.defer="cargos.{{ $index }}.total_tax_amount"
                                    class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-xs"
                                    readonly>
                            </div>

                            {{-- Цена с НДС --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Итого с НДС</label>
                                <input
                                    type="number"
                                    wire:model.defer="cargos.{{ $index }}.price_with_tax"
                                    class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-xs"
                                    readonly>
                            </div>

                            {{-- Оплата до --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Оплата до</label>
                                <input
                                    type="date"
                                    wire:model.defer="cargos.{{ $index }}.payment_terms"
                                    class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-xs">
                            </div>

                            {{-- Плательщик --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Плательщик (тип)</label>
                                <select
                                    wire:model.live="cargos.{{ $index }}.payer_type_id"
                                    class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-xs">
                                    <option value="">— не выбрано —</option>
                                    @foreach($payers as $payerId => $payer)
                                        <option value="{{ $payerId }}">
                                            {{ $payer['label'] ?? $payerId }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- =========================
                             CARGO ITEMS (EU METRICS)
                        ========================== --}}
                        <div class="border-t border-gray-100 dark:border-gray-800 pt-3 mt-2 space-y-2">

                            <div class="flex items-center justify-between">
                                <div class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                    📑 Позиции груза
                                </div>

                                <button type="button"
                                        wire:click="addItem({{ $index }})"
                                        class="text-xs px-2 py-1 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100">
                                    ➕ Добавить позицию
                                </button>
                            </div>

                            <div class="space-y-2">
                                @foreach($cargo['items'] as $itemIndex => $item)

                                    @php
                                        $key = "cargos.$index.items.$itemIndex.measurements";
                                        $itemError = $errors->has($key);
                                    @endphp

                                    <div
                                        wire:key="item-{{ $cargo['uid'] ?? $index }}-{{ $item['uid'] ?? $itemIndex }}"
                                        x-data="{ open: {{ $itemError ? 'false' : 'true' }} }"
                                        class="rounded-2xl px-3 py-3 space-y-3 border transition
                                               @if($itemError)
                                                   border-red-500 bg-red-50 dark:bg-red-900/20
                                               @else
                                                   border-gray-100 dark:border-gray-800
                                               @endif">

                                        {{-- HEADER --}}
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">
                                                    Позиция #{{ $itemIndex + 1 }}
                                                </span>

                                                @if($itemError)
                                                    <span class="text-xs text-red-600 font-semibold">
                                                        ❗ Укажите хотя бы одну единицу измерения
                                                    </span>
                                                @endif
                                            </div>

                                            @if(count($cargo['items']) > 1)
                                                <button type="button"
                                                        wire:click="removeItem({{ $index }}, {{ $itemIndex }})"
                                                        class="text-[10px] text-red-500 hover:text-red-600 px-1.5 py-0.5 rounded-lg hover:bg-red-50">
                                                    ✕
                                                </button>
                                            @endif
                                        </div>

                                        {{-- Описание --}}
                                        <div>
                                            <div class="text-[10px] uppercase font-semibold text-gray-500 mb-1">Описание</div>
                                            <input type="text"
                                                   placeholder="Например: мебель, техника, продуктовая группа…"
                                                   wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.description"
                                                   class="w-full rounded-xl border text-xs
                                                          @if($itemError) border-red-500 input-error @else border-gray-300 dark:border-gray-700 @endif
                                                          dark:bg-gray-800 dark:text-gray-100">
                                        </div>

                                        {{-- Количества --}}
                                        <div>
                                            <div class="text-[10px] uppercase font-semibold text-gray-500 mb-1">Количества</div>
                                            <div class="grid grid-cols-3 gap-2">
                                                @foreach(['packages'=>'Упаковок', 'pallets'=>'Паллет', 'units'=>'Штук'] as $field => $placeholder)
                                                    <input type="text"
                                                           inputmode="numeric"
                                                           placeholder="{{ $placeholder }}"
                                                           wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.{{ $field }}"
                                                           class="w-full rounded-xl border text-[11px]
                                                                  @if($itemError) border-red-500 @else border-gray-300 dark:border-gray-700 @endif
                                                                  dark:bg-gray-800 dark:text-gray-100">
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Вес --}}
                                        <div>
                                            <div class="text-[10px] uppercase font-semibold text-gray-500 mb-1">Вес</div>
                                            <div class="grid grid-cols-3 gap-2">
                                                @foreach(['net_weight'=>'Нетто, кг', 'gross_weight'=>'Брутто, кг', 'tonnes'=>'Тонны, т'] as $field => $placeholder)
                                                    <input type="text"
                                                           inputmode="decimal"
                                                           placeholder="{{ $placeholder }}"
                                                           wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.{{ $field }}"
                                                           class="w-full rounded-xl border text-[11px]
                                                                  @if($itemError) border-red-500 @else border-gray-300 dark:border-gray-700 @endif
                                                                  dark:bg-gray-800 dark:text-gray-100">
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Объём / LM --}}
                                        <div>
                                            <div class="text-[10px] uppercase font-semibold text-gray-500 mb-1">Объём / Длина загрузки</div>
                                            <div class="grid grid-cols-2 gap-2">
                                                @foreach(['volume'=>'Объём (м³)', 'loading_meters'=>'LM — погрузочные метры'] as $field => $placeholder)
                                                    <input type="text"
                                                           inputmode="decimal"
                                                           placeholder="{{ $placeholder }}"
                                                           wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.{{ $field }}"
                                                           class="w-full rounded-xl border text-[11px]
                                                                  @if($itemError) border-red-500 @else border-gray-300 dark:border-gray-700 @endif
                                                                  dark:bg-gray-800 dark:text-gray-100">
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Условия перевозки --}}
                                        <div>
                                            <div class="text-[10px] uppercase font-semibold text-gray-500 mb-1">Условия перевозки</div>
                                            <div class="grid grid-cols-3 gap-2 items-center">
                                                <input type="text"
                                                       placeholder="Темп. +2..+6"
                                                       wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.temperature"
                                                       class="w-full rounded-xl border text-[11px]
                                                              @if($itemError) border-red-500 @else border-gray-300 dark:border-gray-700 @endif
                                                              dark:bg-gray-800 dark:text-gray-100">

                                                <select
                                                    wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.hazmat"
                                                    class="w-full rounded-xl border text-[11px]
                                                           @if($itemError) border-red-500 @else border-gray-300 dark:border-gray-700 @endif
                                                           dark:bg-gray-800 dark:text-gray-100">
                                                    <option value="">ADR</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4.1">4.1</option>
                                                    <option value="4.2">4.2</option>
                                                    <option value="4.3">4.3</option>
                                                    <option value="5.1">5.1</option>
                                                    <option value="5.2">5.2</option>
                                                    <option value="6.1">6.1</option>
                                                    <option value="6.2">6.2</option>
                                                    <option value="8">8</option>
                                                    <option value="9">9</option>
                                                </select>

                                                <div class="flex items-center gap-1">
                                                    <input type="checkbox"
                                                           id="stackable_{{ $cargo['uid'] ?? $index }}_{{ $item['uid'] ?? $itemIndex }}"
                                                           wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.stackable"
                                                           class="rounded border text-[11px]
                                                                  @if($itemError) border-red-500 @else border-gray-300 dark:border-gray-600 @endif
                                                                  dark:bg-gray-800">
                                                    <label for="stackable_{{ $cargo['uid'] ?? $index }}_{{ $item['uid'] ?? $itemIndex }}"
                                                           class="text-[11px] text-gray-600 dark:text-gray-300">
                                                        Штабелируется
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- COMMENT / INSTRUCTIONS --}}
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <textarea rows="2"
                                                      placeholder="Инструкции"
                                                      wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.instructions"
                                                      class="w-full rounded-xl border text-[11px]
                                                             @if($itemError) border-red-500 @else border-gray-300 dark:border-gray-700 @endif
                                                             dark:bg-gray-800 dark:text-gray-100"></textarea>

                                            <textarea rows="2"
                                                      placeholder="Комментарии"
                                                      wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.remarks"
                                                      class="w-full rounded-xl border text-[11px]
                                                             @if($itemError) border-red-500 @else border-gray-300 dark:border-gray-700 @endif
                                                             dark:bg-gray-800 dark:text-gray-100"></textarea>
                                        </div>

                                        @error($key)
                                            <div class="text-[11px] text-red-600 mt-1 font-semibold">
                                                {{ $message }}
                                            </div>
                                        @enderror

                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div> {{-- /CARGO BODY END --}}
                </div> {{-- /CARGO CONTAINER END --}}
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
                <span wire:loading.remove>💾 Сохранить рейс</span>
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
