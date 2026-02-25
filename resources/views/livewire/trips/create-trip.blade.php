{{-- resources/views/livewire/trips/create-trip.blade.php --}}
<div class="min-h-screen bg-gray-100 dark:bg-gray-900 pb-24">

    @php
        // =========================
        // UI helpers
        // =========================
        $baseInput = "w-full rounded-xl text-sm border bg-white dark:bg-gray-800 dark:text-gray-100
                      border-gray-300 dark:border-gray-700
                      focus:outline-none focus:ring-2 focus:ring-blue-500/25 focus:border-blue-500
                      placeholder:text-gray-400 dark:placeholder:text-gray-500";

        $warnInput = "border-amber-400 dark:border-amber-500 bg-amber-50/70 dark:bg-amber-900/10";
        $errInput  = "border-red-500 bg-red-50/70 dark:bg-red-900/20 focus:ring-red-500/25 focus:border-red-600";

        $reqBadge = function () {
            return '<span class="ml-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[11px] font-semibold
                           bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-200">
                        <span>❗</span><span>обяз.</span>
                    </span>';
        };

        $isBlank = function ($v) {
            if ($v === null) return true;
            if (is_string($v) && trim($v) === '') return true;
            if (is_array($v) && count($v) === 0) return true;
            return false;
        };

        // helper: get step by token (uid or index fallback)
        $stepByToken = function ($token) use (&$steps) {
            if ($token === null || $token === '') return null;

            if (is_numeric($token)) {
                return $steps[(int)$token] ?? null;
            }

            $token = (string)$token;
            foreach ($steps as $s) {
                if (($s['uid'] ?? null) === $token) return $s;
            }
            return null;
        };
    @endphp

    {{-- =========================
         HEADER
    ========================== --}}
    <div class="sticky top-0 z-20 bg-white/90 dark:bg-gray-900/90 border-b border-gray-200 dark:border-gray-700 backdrop-blur">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-lg sm:text-2xl font-semibold text-gray-900 dark:text-gray-100 truncate">
                    🚛 Новый рейс (multi-cargo)
                </h1>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Обязательные поля подсвечены янтарным, ошибки — красным.
                </div>
            </div>

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
        @php
            $kExp = 'expeditor_id';
            $kBank = 'bank_index';
            $expWarn  = $isBlank($expeditor_id) && !$errors->has($kExp);
            $bankWarn = $isBlank($bank_index)   && !$errors->has($kBank);
        @endphp

        <section class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm px-4 py-4 sm:px-6 sm:py-5 space-y-4 border
                        border-gray-100 dark:border-gray-800">

            <div class="flex items-center justify-between gap-2">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100">
                    🧾 Экспедитор
                </h2>

                @if($errors->has($kExp) || $errors->has($kBank))
                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold
                                 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-200">
                        Ошибки
                    </span>
                @elseif($expWarn || $bankWarn)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold
                                 bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-200">
                        Не заполнено
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-start">
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                            Выберите экспедитора {!! $reqBadge() !!}
                        </label>

                        <select
                            wire:model.live="expeditor_id"
                            @class([$baseInput, $warnInput => $expWarn, $errInput => $errors->has($kExp), 'input-error' => $errors->has($kExp)])
                        >
                            <option value="">— выберите экспедитора —</option>
                            @foreach($expeditors as $id => $exp)
                                <option value="{{ $id }}">{{ $exp['name'] }}</option>
                            @endforeach
                        </select>

                        @error('expeditor_id')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                            Банковский счёт {!! $reqBadge() !!}
                        </label>

                        <select
                            wire:model.live="bank_index"
                            @class([$baseInput, $warnInput => $bankWarn, $errInput => $errors->has($kBank), 'input-error' => $errors->has($kBank)])
                        >
                            <option value="">— выберите банк —</option>
                            @foreach($banks ?? [] as $idx => $bank)
                                <option value="{{ $idx }}">{{ $bank['name'] }}</option>
                            @endforeach
                        </select>

                        @error('bank_index')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <div class="rounded-2xl px-4 py-3 text-xs sm:text-sm space-y-1.5 border
                                bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700">
                        <div class="font-semibold text-gray-900 dark:text-gray-100 flex items-center justify-between gap-2">
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
            </div>
        </section>

        {{-- =========================
             TRANSPORT
        ========================== --}}
        @php
            $kDriver = 'driver_id';
            $kTruck  = 'truck_id';
            $kStart  = 'start_date';
            $kEnd    = 'end_date';
            $kCur    = 'currency';

            $driverWarn = $isBlank($driver_id) && !$errors->has($kDriver);
            $truckWarn  = $isBlank($truck_id)  && !$errors->has($kTruck);
            $startWarn  = $isBlank($start_date) && !$errors->has($kStart);
            $endWarn    = $isBlank($end_date) && !$errors->has($kEnd);
            $curWarn    = $isBlank($currency) && !$errors->has($kCur);

            $transportHasErrors = $errors->has($kDriver) || $errors->has($kTruck) || $errors->has($kStart) || $errors->has($kEnd) || $errors->has($kCur);
            $transportMissing   = $driverWarn || $truckWarn || $startWarn || $endWarn || $curWarn;
        @endphp

        <section class="rounded-2xl shadow-sm px-4 py-4 sm:px-6 sm:py-5 space-y-4 border
                        @if($transportHasErrors)
                            bg-red-50/40 dark:bg-red-900/10 border-red-300 dark:border-red-900/40
                        @elseif($transportMissing)
                            bg-amber-50/40 dark:bg-amber-900/10 border-amber-200 dark:border-amber-900/50
                        @else
                            bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-800
                        @endif">

            <div class="flex items-center justify-between gap-2">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100">
                    🚚 Транспорт
                </h2>

                @if($transportHasErrors)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold
                                 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-200">
                        Ошибки
                    </span>
                @elseif($transportMissing)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold
                                 bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-200">
                        Не заполнено
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Водитель {!! $reqBadge() !!}
                    </label>
                    <select
                        wire:model.live="driver_id"
                        @class([$baseInput, $warnInput => $driverWarn, $errInput => $errors->has($kDriver), 'input-error' => $errors->has($kDriver)])
                    >
                        <option value="">— выбрать —</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->first_name }} {{ $driver->last_name }}</option>
                        @endforeach
                    </select>
                    @error('driver_id')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Тягач {!! $reqBadge() !!}
                    </label>
                    <select
                        wire:model.live="truck_id"
                        @class([$baseInput, $warnInput => $truckWarn, $errInput => $errors->has($kTruck), 'input-error' => $errors->has($kTruck)])
                    >
                        <option value="">— выбрать —</option>
                        @foreach($trucks as $truck)
                            <option value="{{ $truck->id }}">{{ $truck->plate }} ({{ $truck->brand }} {{ $truck->model }})</option>
                        @endforeach
                    </select>
                    @error('truck_id')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Прицеп <span class="ml-2 text-[11px] text-gray-400">(опц.)</span>
                    </label>

                    <select
                        wire:model.live="trailer_id"
                        class="{{ $baseInput }}"
                    >
                        <option value="">— без прицепа —</option>
                        @foreach($trailers as $trailer)
                            <option value="{{ $trailer->id }}">{{ $trailer->plate }} ({{ $trailer->brand }})</option>
                        @endforeach
                    </select>

                    @error('trailer_id')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror

                    @if($this->trailerTypeMeta)
                        <div class="mt-2 inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold
                                    border border-gray-200 dark:border-gray-700 bg-white/60 dark:bg-gray-900/40">
                            <span class="text-sm leading-none">{{ $this->trailerTypeMeta['icon'] }}</span>
                            <span class="text-gray-700 dark:text-gray-200">{{ $this->trailerTypeMeta['label'] }}</span>
                            <span class="text-[10px] text-gray-400">#{{ $this->trailerTypeMeta['id'] }}</span>

                            @if($this->isContainerTrailer)
                                <span class="ml-1 px-2 py-0.5 rounded-lg text-[11px] font-semibold
                                             bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200">
                                    контейнер
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            @if($this->isContainerTrailer)
                <div class="rounded-2xl border border-blue-200 dark:border-blue-900/60 bg-blue-50/60 dark:bg-blue-900/10 p-4">
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ config('trailer-types.icons.container', '📦') }} Контейнер
                        </div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">
                            Можно оставить пустым
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                Номер контейнера (cont_nr) <span class="ml-2 text-[11px] text-gray-400">(опц.)</span>
                            </label>
                            <input
                                type="text"
                                wire:model.defer="cont_nr"
                                placeholder="Напр. MSKU1234567"
                                class="{{ $baseInput }}"
                            >
                            @error('cont_nr')
                                <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                Номер пломбы (seal_nr) <span class="ml-2 text-[11px] text-gray-400">(опц.)</span>
                            </label>
                            <input
                                type="text"
                                wire:model.defer="seal_nr"
                                placeholder="Напр. 998877"
                                class="{{ $baseInput }}"
                            >
                            @error('seal_nr')
                                <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Дата начала {!! $reqBadge() !!}
                    </label>
                    <input
                        type="date"
                        wire:model.defer="start_date"
                        @class([$baseInput, $warnInput => $startWarn, $errInput => $errors->has($kStart), 'input-error' => $errors->has($kStart)])
                    >
                    @error('start_date')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Дата окончания {!! $reqBadge() !!}
                    </label>
                    <input
                        type="date"
                        wire:model.defer="end_date"
                        @class([$baseInput, $warnInput => $endWarn, $errInput => $errors->has($kEnd), 'input-error' => $errors->has($kEnd)])
                    >
                    @error('end_date')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Валюта рейса {!! $reqBadge() !!}
                    </label>
                    <input
                        type="text"
                        wire:model.defer="currency"
                        @class([$baseInput, $warnInput => $curWarn, $errInput => $errors->has($kCur), 'input-error' => $errors->has($kCur)])
                    >
                    @error('currency')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </section>

        {{-- =========================
             STEPS
        ========================== --}}
        <section class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm px-4 py-4 sm:px-6 sm:py-5 space-y-4 border border-gray-100 dark:border-gray-800">
            <div class="flex items-center justify-between">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100">
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

                    $stepLocation = $stepCity ? ($stepCity . ($stepCountry ? ', '.$stepCountry : '')) : ($stepCountry ?: '—');

                    $date = $step['date'] ?? null;
                    $time = $step['time'] ?? null;
                    $dateTimeShort = $date ? ($date . ($time ? ' '.$time : '')) : '—';

                    $reqFields = ['type','country_id','city_id','address','date','order'];
                    $hasStepErrors = collect($reqFields)->some(fn($f) => $errors->has("steps.$index.$f"));
                    $hasStepMissing =
                        $isBlank($step['type'] ?? null) ||
                        $isBlank($step['country_id'] ?? null) ||
                        $isBlank($step['city_id'] ?? null) ||
                        $isBlank($step['address'] ?? null) ||
                        $isBlank($step['date'] ?? null) ||
                        $isBlank($step['order'] ?? null);
                @endphp

                <div
                    wire:key="step-{{ $step['uid'] ?? $index }}"
                    x-data="{ open: true }"
                    @class([
                        "border rounded-2xl overflow-hidden",
                        "bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700" => !$hasStepErrors && !$hasStepMissing,
                        "bg-amber-50/60 dark:bg-amber-900/10 border-amber-300 dark:border-amber-700" => $hasStepMissing && !$hasStepErrors,
                        "bg-red-50/70 dark:bg-red-900/20 border-red-500" => $hasStepErrors,
                    ])
                >
                    {{-- STEP HEADER --}}
                    <button type="button"
                            class="w-full flex items-center justify-between px-4 py-2 text-sm font-medium text-left
                                   text-gray-800 dark:text-gray-100 bg-white/40 dark:bg-gray-900/20"
                            @click="open = !open">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="shrink-0" x-show="open">▾</span>
                            <span class="shrink-0" x-show="!open">▸</span>

                            <span class="font-semibold shrink-0">Шаг #{{ $index + 1 }}</span>

                            <span class="inline-flex flex-wrap items-center gap-1 text-xs text-gray-600 dark:text-gray-300 min-w-0">
                                <span class="shrink-0">{{ $icon }}</span>
                                <span class="shrink-0">{{ $typeLabel }}</span>
                                <span class="text-gray-400 shrink-0">•</span>
                                <span class="truncate">{{ $stepLocation }}</span>
                                <span class="text-gray-400 shrink-0">•</span>
                                <span class="shrink-0">{{ $dateTimeShort }}</span>
                            </span>

                            @if($hasStepErrors)
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold
                                             bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-200">
                                    Ошибки
                                </span>
                            @elseif($hasStepMissing)
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold
                                             bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-200">
                                    Не заполнено
                                </span>
                            @endif
                        </div>

                        @if(count($steps) > 1)
                            <button type="button"
                                    wire:click="removeStep({{ $index }})"
                                    class="text-xs text-red-600 hover:text-red-700 px-2 py-1 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20"
                                    @click.stop>
                                ✕ Удалить
                            </button>
                        @endif
                    </button>

                    {{-- STEP BODY --}}
                    <div x-show="open" x-collapse class="px-4 py-4 space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            @php
                                $kType = "steps.$index.type";
                                $kDate = "steps.$index.date";
                                $kOrder= "steps.$index.order";
                                $typeWarn = $isBlank($step['type'] ?? null) && !$errors->has($kType);
                                $dateWarn = $isBlank($step['date'] ?? null) && !$errors->has($kDate);
                                $orderWarn= $isBlank($step['order'] ?? null) && !$errors->has($kOrder);
                            @endphp

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Тип шага {!! $reqBadge() !!}
                                </label>
                                <select
                                    wire:model.live="steps.{{ $index }}.type"
                                    @class([$baseInput, $warnInput => $typeWarn, $errInput => $errors->has($kType), 'input-error' => $errors->has($kType)])
                                >
                                    <option value="loading">Погрузка</option>
                                    <option value="unloading">Разгрузка</option>
                                </select>
                                @error("steps.$index.type")
                                    <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="space-y-1.5 sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Дата / время {!! $reqBadge() !!}
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="date"
                                           wire:model.live="steps.{{ $index }}.date"
                                           @class([$baseInput, $warnInput => $dateWarn, $errInput => $errors->has($kDate), 'input-error' => $errors->has($kDate)])
                                    >
                                    <input type="time"
                                           wire:model.live="steps.{{ $index }}.time"
                                           class="{{ $baseInput }}"
                                    >
                                </div>
                                @error("steps.$index.date")
                                    <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Порядок (order) {!! $reqBadge() !!}
                                </label>
                                <input type="number"
                                       wire:model.live="steps.{{ $index }}.order"
                                       placeholder="#"
                                       @class([$baseInput, $warnInput => $orderWarn, $errInput => $errors->has($kOrder), 'input-error' => $errors->has($kOrder)])
                                >
                                @error("steps.$index.order")
                                    <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        @php
                            $kCountry = "steps.$index.country_id";
                            $kCity    = "steps.$index.city_id";
                            $kAddr    = "steps.$index.address";

                            $countryWarn = $isBlank($step['country_id'] ?? null) && !$errors->has($kCountry);
                            $cityWarn    = $isBlank($step['city_id'] ?? null) && !$errors->has($kCity);
                            $addrWarn    = $isBlank($step['address'] ?? null) && !$errors->has($kAddr);
                        @endphp

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Страна {!! $reqBadge() !!}
                                </label>
                                <select
                                    wire:model.live="steps.{{ $index }}.country_id"
                                    @class([$baseInput, $warnInput => $countryWarn, $errInput => $errors->has($kCountry), 'input-error' => $errors->has($kCountry)])
                                >
                                    <option value="">— выбрать —</option>
                                    @foreach($countries as $countryId => $country)
                                        <option value="{{ $countryId }}">{{ $country['name'] }}</option>
                                    @endforeach
                                </select>
                                @error("steps.$index.country_id")
                                    <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Город {!! $reqBadge() !!}
                                </label>
                                <select
                                    wire:model.live="steps.{{ $index }}.city_id"
                                    @class([$baseInput, $warnInput => $cityWarn, $errInput => $errors->has($kCity), 'input-error' => $errors->has($kCity)])
                                >
                                    <option value="">— выбрать —</option>
                                    @foreach(($stepCities[$index]['cities'] ?? []) as $cityId => $city)
                                        <option value="{{ $cityId }}">{{ $city['name'] ?? ('#'.$cityId) }}</option>
                                    @endforeach
                                </select>
                                @error("steps.$index.city_id")
                                    <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Адрес {!! $reqBadge() !!}
                                </label>
                                <input type="text"
                                       wire:model.live="steps.{{ $index }}.address"
                                       @class([$baseInput, $warnInput => $addrWarn, $errInput => $errors->has($kAddr), 'input-error' => $errors->has($kAddr)])
                                >
                                @error("steps.$index.address")
                                    <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                Заметки (notes) <span class="ml-2 text-[11px] text-gray-400">(опц.)</span>
                            </label>
                            <textarea rows="2"
                                      wire:model.live="steps.{{ $index }}.notes"
                                      class="{{ $baseInput }} text-xs"></textarea>
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
             CARGOS
        ========================== --}}
        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100">
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

                    // header summary
                    $summaryParts = [];
                    if (!empty($cargo['price_with_tax'])) {
                        $summaryParts[] = number_format((float)$cargo['price_with_tax'], 2, '.', ' ') . ' € с НДС';
                    }

                    $loadToken = $cargo['loading_step_ids'][0] ?? null;
                    $unloadToken = $cargo['unloading_step_ids'][0] ?? null;

                    if ($loadToken) {
                        $st = $stepByToken($loadToken);
                        if ($st) {
                            $country = !empty($st['country_id']) ? getCountryById($st['country_id']) : null;
                            $city = (!empty($st['country_id']) && !empty($st['city_id']))
                                ? getCityNameByCountryId($st['country_id'], $st['city_id'])
                                : null;
                            $from = $city ?: $country;
                            if ($from) $summaryParts[] = 'от ' . $from;
                        }
                    }

                    if ($unloadToken) {
                        $st = $stepByToken($unloadToken);
                        if ($st) {
                            $country = !empty($st['country_id']) ? getCountryById($st['country_id']) : null;
                            $city = (!empty($st['country_id']) && !empty($st['city_id']))
                                ? getCityNameByCountryId($st['country_id'], $st['city_id'])
                                : null;
                            $to = $city ?: $country;
                            if ($to) $summaryParts[] = 'до ' . $to;
                        }
                    }

                    if (!empty($cargo['supplier_invoice_nr'] ?? null)) {
                        $summaryParts[] = 'Inv: ' . $cargo['supplier_invoice_nr'];
                    }
                    if (!empty($cargo['supplier_invoice_amount'] ?? null)) {
                        $summaryParts[] = 'Inv€ ' . $cargo['supplier_invoice_amount'];
                    }

                    // card status
                    $reqKeys = [
                        "cargos.$index.customer_id",
                        "cargos.$index.shipper_id",
                        "cargos.$index.consignee_id",
                        "cargos.$index.loading_step_ids",
                        "cargos.$index.unloading_step_ids",
                        "cargos.$index.price",
                        "cargos.$index.tax_percent",
                    ];

                    $cargoHasErrors = collect($reqKeys)->some(fn($k) => $errors->has($k));
                    $cargoHasMissing =
                        $isBlank($cargo['customer_id'] ?? null) ||
                        $isBlank($cargo['shipper_id'] ?? null) ||
                        $isBlank($cargo['consignee_id'] ?? null) ||
                        $isBlank($cargo['loading_step_ids'] ?? []) ||
                        $isBlank($cargo['unloading_step_ids'] ?? []) ||
                        $isBlank($cargo['price'] ?? null);
                @endphp

                <div
                    wire:key="cargo-{{ $cargo['uid'] ?? $index }}"
                    x-data="{ open: true }"
                    @class([
                        "rounded-2xl shadow-sm border overflow-hidden",
                        "bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-800" => !$cargoHasErrors && !$cargoHasMissing,
                        "bg-amber-50/40 dark:bg-amber-900/10 border-amber-200 dark:border-amber-900/50" => $cargoHasMissing && !$cargoHasErrors,
                        "bg-red-50/60 dark:bg-red-900/20 border-red-500" => $cargoHasErrors,
                    ])
                >
                    {{-- CARGO HEADER --}}
                    <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100 dark:border-gray-800">
                        <button type="button"
                                class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-gray-100 min-w-0"
                                @click="open = !open">
                            <span class="shrink-0" x-show="open">▾</span>
                            <span class="shrink-0" x-show="!open">▸</span>
                            <span class="shrink-0">Груз #{{ $index + 1 }}</span>

                            @if($cargoHasErrors)
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold
                                             bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-200">
                                    Ошибки
                                </span>
                            @elseif($cargoHasMissing)
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold
                                             bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-200">
                                    Не заполнено
                                </span>
                            @endif

                            @if($summaryParts)
                                <span class="ml-2 text-xs text-gray-500 dark:text-gray-400 truncate">
                                    — {{ implode(' / ', $summaryParts) }}
                                </span>
                            @endif
                        </button>

                        @if(count($cargos) > 1)
                            <button type="button"
                                    wire:click="removeCargo({{ $index }})"
                                    class="text-xs text-red-600 hover:text-red-700 px-2 py-1 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20">
                                ✕ Удалить
                            </button>
                        @endif
                    </div>

                    {{-- CARGO BODY --}}
                    <div x-show="open" x-collapse class="px-4 py-4 sm:px-5 sm:py-5 space-y-4">

                        {{-- Клиенты --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            @php
                                $kCust = "cargos.$index.customer_id";
                                $kShip = "cargos.$index.shipper_id";
                                $kCons = "cargos.$index.consignee_id";

                                $custWarn = $isBlank($cargo['customer_id'] ?? null) && !$errors->has($kCust);
                                $shipWarn = $isBlank($cargo['shipper_id'] ?? null) && !$errors->has($kShip);
                                $consWarn = $isBlank($cargo['consignee_id'] ?? null) && !$errors->has($kCons);
                            @endphp

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Заказчик (customer) {!! $reqBadge() !!}
                                </label>
                                <select
                                    wire:model.live="cargos.{{ $index }}.customer_id"
                                    @class([$baseInput, $warnInput => $custWarn, $errInput => $errors->has($kCust), 'input-error' => $errors->has($kCust)])
                                >
                                    <option value="">— не указан —</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                    @endforeach
                                </select>
                                @error("cargos.$index.customer_id")
                                    <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div>
                                @enderror

                                @if($customer)
                                    <div class="mt-2 text-[11px] bg-white/60 dark:bg-gray-900/30 rounded-xl px-2 py-1.5 border border-gray-200 dark:border-gray-700 space-y-0.5">
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
                                                @if($customer->jur_post_code), {{ $customer->jur_post_code }} @endif
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

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Грузоотправитель (shipper) {!! $reqBadge() !!}
                                </label>
                                <select
                                    wire:model.live="cargos.{{ $index }}.shipper_id"
                                    @class([$baseInput, $warnInput => $shipWarn, $errInput => $errors->has($kShip), 'input-error' => $errors->has($kShip)])
                                >
                                    <option value="">— не указан —</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                    @endforeach
                                </select>
                                @error("cargos.$index.shipper_id")
                                    <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div>
                                @enderror

                                @if($shipper)
                                    <div class="mt-2 text-[11px] bg-white/60 dark:bg-gray-900/30 rounded-xl px-2 py-1.5 border border-gray-200 dark:border-gray-700 space-y-0.5">
                                        <div class="font-semibold truncate">{{ $shipper->company_name }}</div>
                                        <div>Reg/VAT: <span class="font-medium">{{ $shipper->reg_nr ?? '—' }}</span></div>
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Грузополучатель (consignee) {!! $reqBadge() !!}
                                </label>
                                <select
                                    wire:model.live="cargos.{{ $index }}.consignee_id"
                                    @class([$baseInput, $warnInput => $consWarn, $errInput => $errors->has($kCons), 'input-error' => $errors->has($kCons)])
                                >
                                    <option value="">— не указан —</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                    @endforeach
                                </select>
                                @error("cargos.$index.consignee_id")
                                    <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div>
                                @enderror

                                @if($consignee)
                                    <div class="mt-2 text-[11px] bg-white/60 dark:bg-gray-900/30 rounded-xl px-2 py-1.5 border border-gray-200 dark:border-gray-700 space-y-0.5">
                                        <div class="font-semibold truncate">{{ $consignee->company_name }}</div>
                                        <div>Reg/VAT: <span class="font-medium">{{ $consignee->reg_nr ?? '—' }}</span></div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- привязка к шагам --}}
                        @php
                            $kLoad = "cargos.$index.loading_step_ids";
                            $kUnld = "cargos.$index.unloading_step_ids";
                            $loadWarn = $isBlank($cargo['loading_step_ids'] ?? []) && !$errors->has($kLoad);
                            $unldWarn = $isBlank($cargo['unloading_step_ids'] ?? []) && !$errors->has($kUnld);
                        @endphp

                        <div class="border-t border-gray-100 dark:border-gray-800 pt-3 mt-2 space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                                {{-- loading --}}
                                <div @class([
                                    "rounded-2xl p-2 border space-y-2",
                                    "border-gray-200 dark:border-gray-700 bg-white/40 dark:bg-gray-900/20" => !$loadWarn && !$errors->has($kLoad),
                                    "border-amber-300 dark:border-amber-700 bg-amber-50/50 dark:bg-amber-900/10" => $loadWarn,
                                    "border-red-500 bg-red-50/60 dark:bg-red-900/20" => $errors->has($kLoad),
                                ])>
                                    <div class="flex items-center justify-between">
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                                            ⬆ Шаги погрузки {!! $reqBadge() !!}
                                        </label>
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

                                                $token = $st['uid'] ?? $sIndex; // ✅ отправляем UID
                                            @endphp

                                            <label class="block" wire:key="cargo-{{ $cargo['uid'] ?? $index }}-load-step-{{ $token }}">
                                                <input
                                                    type="checkbox"
                                                    class="peer hidden"
                                                    value="{{ $token }}"
                                                    wire:model="cargos.{{ $index }}.loading_step_ids"
                                                >
                                                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 px-3 py-2 text-xs
                                                            bg-white/60 dark:bg-gray-900/30
                                                            peer-checked:bg-blue-50 peer-checked:border-blue-500
                                                            peer-checked:shadow-sm transition-colors">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-[13px]">⬆</span>
                                                            <span class="font-semibold text-gray-900 dark:text-gray-100">
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
                                        <div class="mt-1 text-[11px] text-red-600 font-semibold">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- unloading --}}
                                <div @class([
                                    "rounded-2xl p-2 border space-y-2",
                                    "border-gray-200 dark:border-gray-700 bg-white/40 dark:bg-gray-900/20" => !$unldWarn && !$errors->has($kUnld),
                                    "border-amber-300 dark:border-amber-700 bg-amber-50/50 dark:bg-amber-900/10" => $unldWarn,
                                    "border-red-500 bg-red-50/60 dark:bg-red-900/20" => $errors->has($kUnld),
                                ])>
                                    <div class="flex items-center justify-between">
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                                            ⬇ Шаги разгрузки {!! $reqBadge() !!}
                                        </label>
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

                                                $token = $st['uid'] ?? $sIndex; // ✅ отправляем UID
                                            @endphp

                                            <label class="block" wire:key="cargo-{{ $cargo['uid'] ?? $index }}-unload-step-{{ $token }}">
                                                <input
                                                    type="checkbox"
                                                    class="peer hidden"
                                                    value="{{ $token }}"
                                                    wire:model="cargos.{{ $index }}.unloading_step_ids"
                                                >
                                                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 px-3 py-2 text-xs
                                                            bg-white/60 dark:bg-gray-900/30
                                                            peer-checked:bg-blue-50 peer-checked:border-blue-500
                                                            peer-checked:shadow-sm transition-colors">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-[13px]">⬇</span>
                                                            <span class="font-semibold text-gray-900 dark:text-gray-100">
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
                                        <div class="mt-1 text-[11px] text-red-600 font-semibold">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Payment --}}
                        <div class="border-t border-gray-100 dark:border-gray-800 pt-3 mt-2 space-y-3">

                            {{-- Supplier invoice --}}
                            <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white/40 dark:bg-gray-900/20 p-3">
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <div class="text-xs font-semibold text-gray-800 dark:text-gray-100">
                                        🧾 Supplier invoice (costs)
                                    </div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400">
                                        Инвойс поставщика / расход по грузу
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Supplier invoice № <span class="ml-2 text-[11px] text-gray-400">(опц.)</span></label>
                                        <input
                                            type="text"
                                            wire:model.defer="cargos.{{ $index }}.supplier_invoice_nr"
                                            placeholder="INV-..."
                                            class="{{ $baseInput }}"
                                        >
                                        @error("cargos.$index.supplier_invoice_nr")
                                            <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Supplier invoice amount <span class="ml-2 text-[11px] text-gray-400">(опц.)</span></label>
                                        <input
                                            type="text"
                                            inputmode="decimal"
                                            wire:model.defer="cargos.{{ $index }}.supplier_invoice_amount"
                                            placeholder="0.00"
                                            class="{{ $baseInput }}"
                                        >
                                        @error("cargos.$index.supplier_invoice_amount")
                                            <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Валюта (опц.)</label>
                                        <input
                                            type="text"
                                            wire:model.defer="cargos.{{ $index }}.supplier_invoice_currency"
                                            placeholder="{{ $currency ?? 'EUR' }}"
                                            class="{{ $baseInput }}"
                                        >
                                    </div>
                                </div>
                            </div>

                            {{-- Client freight --}}
                            @php
                                $kPrice = "cargos.$index.price";
                                $kTax   = "cargos.$index.tax_percent";
                                $priceWarn = $isBlank($cargo['price'] ?? null) && !$errors->has($kPrice);
                                $taxWarn   = $isBlank($cargo['tax_percent'] ?? null) && !$errors->has($kTax);
                            @endphp

                            <div class="rounded-2xl border border-blue-200 dark:border-blue-900/60 bg-blue-50/60 dark:bg-blue-900/10 p-3">
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <div class="text-xs font-semibold text-gray-900 dark:text-gray-100">
                                        💶 Client freight (revenue)
                                    </div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400">
                                        Стоимость фрахта для клиента / доход
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-6 gap-3">
                                   <div>
    <label class="flex items-center gap-2 text-xs font-medium text-gray-700 dark:text-gray-200 mb-1">
        <span class="whitespace-nowrap">
            Фрахт (без НДС)
        </span>

        <span class="shrink-0">
            {!! $reqBadge() !!}
        </span>
    </label>

    <input
        type="text"
        inputmode="decimal"
        wire:model.live="cargos.{{ $index }}.price"
        @class([
            $baseInput,
            $warnInput => $priceWarn,
            $errInput => $errors->has($kPrice),
            'input-error' => $errors->has($kPrice)
        ])
    >

    @error("cargos.$index.price")
        <div class="text-[11px] text-red-600 mt-1">
            {{ $message }}
        </div>
    @enderror
</div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200 mb-1">
                                            НДС, % {!! $reqBadge() !!}
                                        </label>
                                        <select
                                            wire:model.live="cargos.{{ $index }}.tax_percent"
                                            @class([$baseInput, $warnInput => $taxWarn, $errInput => $errors->has($kTax), 'input-error' => $errors->has($kTax)])
                                        >
                                            @foreach($taxRates as $rate)
                                                <option value="{{ $rate }}">{{ $rate }}</option>
                                            @endforeach
                                        </select>
                                        @error("cargos.$index.tax_percent")
                                            <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200 mb-1">Сумма НДС</label>
                                        <input type="number" wire:model.defer="cargos.{{ $index }}.total_tax_amount" class="{{ $baseInput }}" readonly>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200 mb-1">Итого с НДС</label>
                                        <input type="number" wire:model.defer="cargos.{{ $index }}.price_with_tax" class="{{ $baseInput }}" readonly>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200 mb-1">Оплата до <span class="ml-2 text-[11px] text-gray-400">(опц.)</span></label>
                                        <input type="date" wire:model.defer="cargos.{{ $index }}.payment_terms" class="{{ $baseInput }}">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200 mb-1">Плательщик (тип) <span class="ml-2 text-[11px] text-gray-400">(опц.)</span></label>
                                        <select wire:model.live="cargos.{{ $index }}.payer_type_id" class="{{ $baseInput }}">
                                            <option value="">— не выбрано —</option>
                                            @foreach($payers as $payerId => $payer)
                                                <option value="{{ $payerId }}">{{ $payer['label'] ?? $payerId }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- CARGO ITEMS --}}
                     {{-- =========================
     CARGO ITEMS (EU METRICS) — REQUIRED (at least 1 measurement)
========================= --}}
@php
    // ✅ cargo-level status: нужно заполнить хотя бы 1 измерение в ЛЮБОЙ позиции
    $hasAnyMeasureInCargo = false;

    foreach (($cargo['items'] ?? []) as $it) {
        $has =
            !empty($it['packages']) ||
            !empty($it['pallets']) ||
            !empty($it['units']) ||
            !empty($it['net_weight']) ||
            !empty($it['gross_weight']) ||
            !empty($it['tonnes']) ||
            !empty($it['volume']) ||
            !empty($it['loading_meters']);

        if ($has) { $hasAnyMeasureInCargo = true; break; }
    }

    // ✅ есть ли errors.measurements в любом item
    $itemsHasErrors = false;
    foreach (($cargo['items'] ?? []) as $ii => $_) {
        if ($errors->has("cargos.$index.items.$ii.measurements")) { $itemsHasErrors = true; break; }
    }

    // amber-state: ещё не заполнено, но и ошибки нет (обычно до submit)
    $itemsMissing = !$hasAnyMeasureInCargo && !$itemsHasErrors;
@endphp

<div
    class="mt-2 pt-3 space-y-2 rounded-2xl border transition
        @if($itemsHasErrors)
            border-red-500 bg-red-50/60 dark:bg-red-900/20
        @elseif($itemsMissing)
            border-amber-300 dark:border-amber-700 bg-amber-50/60 dark:bg-amber-900/10
        @else
            border-gray-200 dark:border-gray-700 bg-white/40 dark:bg-gray-900/20
        @endif"
>
    {{-- header --}}
    <div class="flex items-center justify-between px-3 pt-2">
        <div class="flex items-center gap-2 min-w-0">
            <div class="text-xs font-semibold text-gray-700 dark:text-gray-200">
                📑 Позиции груза
            </div>

            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[11px] font-semibold
                         bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-200">
                ❗ обяз.
            </span>

            @if($itemsHasErrors)
                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold
                             bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-200">
                    Ошибка
                </span>
            @elseif($itemsMissing)
                <span class="text-[11px] text-amber-800 dark:text-amber-200 font-semibold truncate">
                    Нужно заполнить хотя бы 1 параметр (упаковки/паллеты/шт/вес/объём/LM)
                </span>
            @else
                <span class="text-[11px] text-gray-500 dark:text-gray-400 truncate">
                    OK: есть значения
                </span>
            @endif
        </div>

        <button type="button"
                wire:click="addItem({{ $index }})"
                class="text-xs px-2 py-1 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100">
            ➕ Добавить позицию
        </button>
    </div>

    <div class="px-3 pb-3 space-y-2">
        @foreach($cargo['items'] as $itemIndex => $item)
            @php
                $measureKey = "cargos.$index.items.$itemIndex.measurements";
                $itemError = $errors->has($measureKey);
            @endphp

            <div
                wire:key="item-{{ $cargo['uid'] ?? $index }}-{{ $item['uid'] ?? $itemIndex }}"
                class="rounded-2xl px-3 py-3 space-y-3 border transition
                       @if($itemError)
                           border-red-500 bg-red-50 dark:bg-red-900/20
                       @else
                           border-gray-200 dark:border-gray-700 bg-white/40 dark:bg-gray-900/20
                       @endif"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-gray-800 dark:text-gray-100">
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
                                class="text-[10px] text-red-600 hover:text-red-700 px-1.5 py-0.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20">
                            ✕
                        </button>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-2">
                    <div class="sm:col-span-3">
                        <div class="text-[10px] uppercase font-semibold text-gray-500 mb-1">Описание</div>
                        <input type="text"
                               placeholder="Например: мебель, техника…"
                               wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.description"
                               class="{{ $baseInput }} text-xs @if($itemError) border-red-500 @endif">
                    </div>

                    <div>
                        <div class="text-[10px] uppercase font-semibold text-gray-500 mb-1">Customs code</div>
                        <input type="text"
                               placeholder="HS/CN"
                               wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.customs_code"
                               class="{{ $baseInput }} text-xs @if($itemError) border-red-500 @endif">
                    </div>
                </div>

                <div>
                    <div class="text-[10px] uppercase font-semibold text-gray-500 mb-1">Количества</div>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['packages'=>'Упаковок', 'pallets'=>'Паллет', 'units'=>'Штук'] as $field => $placeholder)
                            <input type="text"
                                   inputmode="numeric"
                                   placeholder="{{ $placeholder }}"
                                   wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.{{ $field }}"
                                   class="{{ $baseInput }} text-[11px] @if($itemError) border-red-500 @endif">
                        @endforeach
                    </div>
                </div>

                <div>
                    <div class="text-[10px] uppercase font-semibold text-gray-500 mb-1">Вес</div>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['net_weight'=>'Нетто, кг', 'gross_weight'=>'Брутто, кг', 'tonnes'=>'Тонны, т'] as $field => $placeholder)
                            <input type="text"
                                   inputmode="decimal"
                                   placeholder="{{ $placeholder }}"
                                   wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.{{ $field }}"
                                   class="{{ $baseInput }} text-[11px] @if($itemError) border-red-500 @endif">
                        @endforeach
                    </div>
                </div>

                <div>
                    <div class="text-[10px] uppercase font-semibold text-gray-500 mb-1">Объём / Длина загрузки</div>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['volume'=>'Объём (м³)', 'loading_meters'=>'LM — погрузочные метры'] as $field => $placeholder)
                            <input type="text"
                                   inputmode="decimal"
                                   placeholder="{{ $placeholder }}"
                                   wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.{{ $field }}"
                                   class="{{ $baseInput }} text-[11px] @if($itemError) border-red-500 @endif">
                        @endforeach
                    </div>
                </div>

                <div>
                    <div class="text-[10px] uppercase font-semibold text-gray-500 mb-1">Условия перевозки</div>
                    <div class="grid grid-cols-3 gap-2 items-center">
                        <input type="text"
                               placeholder="Темп. +2..+6"
                               wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.temperature"
                               class="{{ $baseInput }} text-[11px] @if($itemError) border-red-500 @endif">

                        <select wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.hazmat"
                                class="{{ $baseInput }} text-[11px] @if($itemError) border-red-500 @endif">
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
                                   class="rounded border dark:bg-gray-800">
                            <label for="stackable_{{ $cargo['uid'] ?? $index }}_{{ $item['uid'] ?? $itemIndex }}"
                                   class="text-[11px] text-gray-600 dark:text-gray-300">
                                Штабелируется
                            </label>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <textarea rows="2" placeholder="Инструкции"
                              wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.instructions"
                              class="{{ $baseInput }} text-[11px] @if($itemError) border-red-500 @endif"></textarea>

                    <textarea rows="2" placeholder="Комментарии"
                              wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.remarks"
                              class="{{ $baseInput }} text-[11px] @if($itemError) border-red-500 @endif"></textarea>
                </div>

                @error($measureKey)
                    <div class="text-[11px] text-red-600 mt-1 font-semibold">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        @endforeach
    </div>
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
                <span wire:loading.remove>💾 Сохранить рейс</span>
                <span wire:loading>⏳ Сохранение...</span>
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener("livewire:init", () => {
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
