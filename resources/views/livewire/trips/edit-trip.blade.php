{{-- resources/views/livewire/trips/edit-trip.blade.php --}}

@php
    /**
     * Local UI helpers (чтобы не было Undefined variable / function)
     */
    $isBlank = function ($v): bool {
        if (is_null($v)) return true;
        if (is_string($v)) return trim($v) === '';
        if (is_array($v)) return count($v) === 0;
        return false;
    };

    // Inputs / badges (единый стиль как в Create)
    $baseInput = 'w-full rounded-xl border text-sm border-gray-300 dark:border-gray-700 bg-white/80 dark:bg-gray-800/70 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition';
    $warnInput = 'border-amber-300 dark:border-amber-700 bg-amber-50/40 dark:bg-amber-900/10';
    $errInput  = 'border-red-500 bg-red-50/50 dark:bg-red-900/15';

    $badgeWarn  = 'inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-200 border border-amber-200 dark:border-amber-800/40';
    $badgeError = 'inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200 border border-red-200 dark:border-red-800/40';

    $reqBadge = function (): string {
        return '<span class="ml-1 text-[10px] font-bold text-red-500">*</span>';
    };

    // Accent cards (как в create): мягкие градиенты
    $cardBase = 'rounded-2xl shadow-sm border px-4 py-4 sm:px-6 sm:py-5 space-y-4';

    $cardBlue   = $cardBase.' bg-gradient-to-br from-white to-blue-50/70 dark:from-gray-900 dark:to-blue-900/10 border-blue-100 dark:border-blue-900/40';
    $cardGreen  = $cardBase.' bg-gradient-to-br from-white to-emerald-50/70 dark:from-gray-900 dark:to-emerald-900/10 border-emerald-100 dark:border-emerald-900/40';
    $cardPurple = $cardBase.' bg-gradient-to-br from-white to-violet-50/70 dark:from-gray-900 dark:to-violet-900/10 border-violet-100 dark:border-violet-900/40';

    $subCard = 'rounded-2xl border px-4 py-3 bg-white/70 dark:bg-gray-900/40 border-gray-200 dark:border-gray-700';
@endphp

<div class="min-h-screen pb-24 bg-gradient-to-b from-gray-50 via-gray-100 to-gray-100 dark:from-gray-950 dark:via-gray-900 dark:to-gray-900">

    {{-- =========================
         HEADER
    ========================== --}}
    <div class="sticky top-0 z-20 bg-white/85 dark:bg-gray-900/80 border-b border-blue-100 dark:border-blue-900/40 backdrop-blur">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
            <h1 class="text-lg sm:text-2xl font-semibold text-gray-800 dark:text-gray-100 truncate">
                ✏️ Редактирование рейса #{{ $trip->id ?? '—' }} (multi-cargo)
            </h1>

            <button
                wire:click="save"
                wire:loading.attr="disabled"
                class="hidden sm:inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold
                       bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white shadow">
                <span wire:loading.remove>💾 Сохранить изменения</span>
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
            $expHasErrors = $errors->has('expeditor_id') || $errors->has('bank_index');
            $expMissing = $isBlank($expeditor_id) || ($isBlank($bank_index) && !empty($banks));
        @endphp

        <section class="{{ $cardBlue }}">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100">
                    🧾 Экспедитор
                </h2>

                @if($expHasErrors)
                    <span class="{{ $badgeError }}">Ошибки</span>
                @elseif($expMissing)
                    <span class="{{ $badgeWarn }}">Не заполнено</span>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-start">
                <div class="space-y-2">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Выберите экспедитора {!! $reqBadge() !!}
                    </label>

                    <select
                        wire:model.live="expeditor_id"
                        @class([
                            $baseInput,
                            $warnInput => ($isBlank($expeditor_id) && !$errors->has('expeditor_id')),
                            $errInput  => $errors->has('expeditor_id'),
                            'input-error' => $errors->has('expeditor_id')
                        ])
                    >
                        <option value="">— выберите экспедитора —</option>
                        @foreach($expeditors as $id => $exp)
                            <option value="{{ $id }}">{{ $exp['name'] }}</option>
                        @endforeach
                    </select>

                    @error('expeditor_id')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror

                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1 mt-3">
                        Банковский счёт {!! !empty($banks) ? $reqBadge() : '' !!}
                    </label>

                    <select
                        wire:model.live="bank_index"
                        @class([
                            $baseInput,
                            $warnInput => ($isBlank($bank_index) && !empty($banks) && !$errors->has('bank_index')),
                            $errInput  => $errors->has('bank_index'),
                            'input-error' => $errors->has('bank_index')
                        ])
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

                <div class="sm:col-span-2">
                    <div class="{{ $subCard }} text-xs sm:text-sm space-y-1.5">
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

                        <div class="text-gray-700 dark:text-gray-200 pt-2 border-t border-gray-200/70 dark:border-gray-700/70">
                            <div>Phone: <span class="font-medium">{{ $expeditorData['phone'] ?? '—' }}</span></div>
                            <div>Email: <span class="font-medium">{{ $expeditorData['email'] ?? '—' }}</span></div>
                        </div>

                        <div class="text-gray-700 dark:text-gray-200 pt-2 border-t border-gray-200/70 dark:border-gray-700/70">
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

            $transportHasErrors = $errors->has($kDriver) || $errors->has($kTruck) || $errors->has($kStart) || $errors->has($kEnd) || $errors->has($kCur);
            $transportMissing = $isBlank($driver_id) || $isBlank($truck_id) || $isBlank($start_date) || $isBlank($end_date) || $isBlank($currency);

            $driverWarn = $isBlank($driver_id) && !$errors->has($kDriver);
            $truckWarn  = $isBlank($truck_id) && !$errors->has($kTruck);
            $startWarn  = $isBlank($start_date) && !$errors->has($kStart);
            $endWarn    = $isBlank($end_date) && !$errors->has($kEnd);
            $curWarn    = $isBlank($currency) && !$errors->has($kCur);
        @endphp

        <section class="{{ $cardGreen }}">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100">
                    🚚 Транспорт
                </h2>

                @if($transportHasErrors)
                    <span class="{{ $badgeError }}">Ошибки</span>
                @elseif($transportMissing)
                    <span class="{{ $badgeWarn }}">Не заполнено</span>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                {{-- Driver --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Водитель {!! $reqBadge() !!}
                    </label>
                    <select wire:model.live="driver_id"
                            @class([$baseInput, $warnInput => $driverWarn, $errInput => $errors->has($kDriver), 'input-error' => $errors->has($kDriver)])>
                        <option value="">— выбрать —</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->first_name }} {{ $driver->last_name }}</option>
                        @endforeach
                    </select>
                    @error('driver_id') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                {{-- Truck --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Тягач {!! $reqBadge() !!}
                    </label>
                    <select wire:model.live="truck_id"
                            @class([$baseInput, $warnInput => $truckWarn, $errInput => $errors->has($kTruck), 'input-error' => $errors->has($kTruck)])>
                        <option value="">— выбрать —</option>
                        @foreach($trucks as $truck)
                            <option value="{{ $truck->id }}">{{ $truck->plate }} ({{ $truck->brand }} {{ $truck->model }})</option>
                        @endforeach
                    </select>
                    @error('truck_id') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                {{-- Trailer --}}
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

                    @if($this->trailerTypeMeta)
                        <div class="mt-2 inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold
                                    border border-gray-200 dark:border-gray-700 bg-white/60 dark:bg-gray-900/40">
                            <span class="text-sm leading-none">{{ $this->trailerTypeMeta['icon'] }}</span>
                            <span class="text-gray-700 dark:text-gray-200">{{ $this->trailerTypeMeta['label'] }}</span>
                            <span class="text-[10px] text-gray-400">#{{ $this->trailerTypeMeta['id'] }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- container-only fields --}}
            @if($this->isContainerTrailer)
                <div class="rounded-2xl border border-blue-200 dark:border-blue-900/60 bg-blue-50/60 dark:bg-blue-900/10 p-4">
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            📦 Контейнер
                        </div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">
                            Можно оставить пустым (если номера ещё неизвестны)
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                Номер контейнера (cont_nr) <span class="ml-2 text-[11px] text-gray-400">(опц.)</span>
                            </label>
                            <input type="text"
                                   wire:model.defer="cont_nr"
                                   placeholder="Напр. MSKU1234567"
                                   class="{{ $baseInput }}">
                            @error('cont_nr') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                Номер пломбы (seal_nr) <span class="ml-2 text-[11px] text-gray-400">(опц.)</span>
                            </label>
                            <input type="text"
                                   wire:model.defer="seal_nr"
                                   placeholder="Напр. 998877"
                                   class="{{ $baseInput }}">
                            @error('seal_nr') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            @endif

            {{-- dates/currency --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Дата начала {!! $reqBadge() !!}
                    </label>
                    <input type="date" wire:model.defer="start_date"
                           @class([$baseInput, $warnInput => $startWarn, $errInput => $errors->has($kStart), 'input-error' => $errors->has($kStart)])>
                    @error('start_date') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Дата окончания {!! $reqBadge() !!}
                    </label>
                    <input type="date" wire:model.defer="end_date"
                           @class([$baseInput, $warnInput => $endWarn, $errInput => $errors->has($kEnd), 'input-error' => $errors->has($kEnd)])>
                    @error('end_date') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Валюта рейса {!! $reqBadge() !!}
                    </label>
                    <input type="text" wire:model.defer="currency"
                           @class([$baseInput, $warnInput => $curWarn, $errInput => $errors->has($kCur), 'input-error' => $errors->has($kCur)])>
                    @error('currency') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>
            </div>
        </section>


        {{-- =========================
             STEPS (МАРШРУТ / TripStep)
        ========================== --}}
        @php
            $stepsHasAnyErrors = collect($steps ?? [])->some(function ($s, $i) use ($errors) {
                foreach (['type','country_id','city_id','address','date','order'] as $f) {
                    if ($errors->has("steps.$i.$f")) return true;
                }
                return false;
            });

            $stepsHasMissing = collect($steps ?? [])->some(function ($s) use ($isBlank) {
                return
                    $isBlank($s['type'] ?? null) ||
                    $isBlank($s['country_id'] ?? null) ||
                    $isBlank($s['city_id'] ?? null) ||
                    $isBlank($s['address'] ?? null) ||
                    $isBlank($s['date'] ?? null) ||
                    $isBlank($s['order'] ?? null);
            });
        @endphp

        <section class="{{ $cardPurple }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100">
                        🧭 Маршрут (steps)
                    </h2>

                    @if($stepsHasAnyErrors)
                        <span class="{{ $badgeError }}">Ошибки</span>
                    @elseif($stepsHasMissing)
                        <span class="{{ $badgeWarn }}">Не заполнено</span>
                    @endif
                </div>

                <button type="button"
                        wire:click="addStep"
                        class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold
                               bg-blue-600 hover:bg-blue-700 text-white shadow-sm">
                    ➕ Добавить шаг
                </button>
            </div>

            @forelse($steps as $index => $step)
                @php
                    $stepKey = $step['id'] ?? "new-$index";
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

                <div x-data="{ open: true }"
                     wire:key="step-{{ $stepKey }}"
                     @class([
                        'border rounded-2xl overflow-hidden',
                        'bg-white/70 dark:bg-gray-900/40 border-violet-100 dark:border-violet-900/40' => (!$hasStepErrors && !$hasStepMissing),
                        'bg-amber-50/60 dark:bg-amber-900/10 border-amber-300 dark:border-amber-700' => ($hasStepMissing && !$hasStepErrors),
                        'bg-red-50/70 dark:bg-red-900/20 border-red-500' => $hasStepErrors,
                     ])>

                    {{-- STEP HEADER --}}
                    <button type="button"
                            class="w-full flex items-center justify-between px-4 py-2 text-sm font-medium text-left
                                   text-gray-800 dark:text-gray-100 bg-white/50 dark:bg-gray-900/20"
                            @click="open = !open">
                        <div class="flex items-center gap-2 min-w-0">
                            <span x-show="open">▾</span>
                            <span x-show="!open">▸</span>

                            <span class="font-semibold shrink-0">Шаг #{{ $index + 1 }}</span>

                            <span class="inline-flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300 truncate">
                                {{ $this->stepLabelByIndex($index) }}
                            </span>

                            @if($hasStepErrors)
                                <span class="{{ $badgeError }}">Ошибки</span>
                            @elseif($hasStepMissing)
                                <span class="{{ $badgeWarn }}">Не заполнено</span>
                            @endif
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
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Тип шага {!! $reqBadge() !!}
                                </label>
                                <select wire:model.defer="steps.{{ $index }}.type"
                                        @class([$baseInput, $warnInput => ($isBlank($step['type'] ?? null) && !$errors->has("steps.$index.type")), $errInput => $errors->has("steps.$index.type"), 'input-error' => $errors->has("steps.$index.type")])>
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
                                           wire:model.defer="steps.{{ $index }}.date"
                                           @class([$baseInput, $warnInput => ($isBlank($step['date'] ?? null) && !$errors->has("steps.$index.date")), $errInput => $errors->has("steps.$index.date"), 'input-error' => $errors->has("steps.$index.date")])>
                                    <input type="time"
                                           wire:model.defer="steps.{{ $index }}.time"
                                           class="{{ $baseInput }}">
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
                                       wire:model.defer="steps.{{ $index }}.order"
                                       placeholder="#"
                                       @class([$baseInput, $warnInput => ($isBlank($step['order'] ?? null) && !$errors->has("steps.$index.order")), $errInput => $errors->has("steps.$index.order"), 'input-error' => $errors->has("steps.$index.order")])>
                                @error("steps.$index.order")
                                <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                    Страна {!! $reqBadge() !!}
                                </label>
                                <select wire:model.live="steps.{{ $index }}.country_id"
                                        @class([$baseInput, $warnInput => ($isBlank($step['country_id'] ?? null) && !$errors->has("steps.$index.country_id")), $errInput => $errors->has("steps.$index.country_id"), 'input-error' => $errors->has("steps.$index.country_id")])>
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
                                <select wire:model.live="steps.{{ $index }}.city_id"
                                        @class([$baseInput, $warnInput => ($isBlank($step['city_id'] ?? null) && !$errors->has("steps.$index.city_id")), $errInput => $errors->has("steps.$index.city_id"), 'input-error' => $errors->has("steps.$index.city_id")])>
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
                                       wire:model.defer="steps.{{ $index }}.address"
                                       @class([$baseInput, $warnInput => ($isBlank($step['address'] ?? null) && !$errors->has("steps.$index.address")), $errInput => $errors->has("steps.$index.address"), 'input-error' => $errors->has("steps.$index.address")])>
                                @error("steps.$index.address")
                                <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Заметки (notes)</label>
                            <textarea rows="2"
                                      wire:model.defer="steps.{{ $index }}.notes"
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
             CARGOS (MULTI-CARGO)
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
                @php
                    $customer  = $cargo['customer_id']  ? $clients->firstWhere('id', $cargo['customer_id'])  : null;
                    $shipper   = $cargo['shipper_id']   ? $clients->firstWhere('id', $cargo['shipper_id'])   : null;
                    $consignee = $cargo['consignee_id'] ? $clients->firstWhere('id', $cargo['consignee_id']) : null;

                    $summaryParts = [];
                    if (!empty($cargo['price_with_tax'])) {
                        $summaryParts[] = number_format($cargo['price_with_tax'], 2, '.', ' ') . ' € с НДС';
                    }
                    if (!empty($cargo['loading_step_ids'] ?? []) && count($cargo['loading_step_ids']) > 0) {
                        $summaryParts[] = 'от: ' . $this->stepLabelByIndex((int)$cargo['loading_step_ids'][0]);
                    }
                    if (!empty($cargo['unloading_step_ids'] ?? []) && count($cargo['unloading_step_ids']) > 0) {
                        $summaryParts[] = 'до: ' . $this->stepLabelByIndex((int)$cargo['unloading_step_ids'][0]);
                    }
                @endphp

                <div x-data="{ open: true }"
                     wire:key="cargo-{{ $index }}"
                     class="rounded-2xl shadow-sm border border-emerald-100 dark:border-emerald-900/40 bg-gradient-to-br from-white to-emerald-50/50 dark:from-gray-900 dark:to-emerald-900/10">

                    {{-- CARGO HEADER --}}
                    <div class="flex items-center justify-between px-4 py-2 border-b border-emerald-100/70 dark:border-emerald-900/40">
                        <button type="button"
                                class="flex items-center gap-2 text-sm font-semibold text-gray-800 dark:text-gray-100 min-w-0"
                                @click="open = !open">
                            <span x-show="open">▾</span>
                            <span x-show="!open">▸</span>
                            <span class="shrink-0">Груз #{{ $index + 1 }}</span>

                            @if($summaryParts)
                                <span class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                    — {{ implode(' / ', $summaryParts) }}
                                </span>
                            @endif
                        </button>

                        <button type="button"
                                wire:click="removeCargo({{ $index }})"
                                class="text-xs text-red-500 hover:text-red-600 px-2 py-1 rounded-lg hover:bg-red-50">
                            ✕ Удалить
                        </button>
                    </div>

                    {{-- CARGO BODY --}}
                    <div x-show="open" x-collapse class="px-4 py-4 sm:px-5 sm:py-5 space-y-4">

                        {{-- ===== Клиенты: Customer / Shipper / Consignee ===== --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            {{-- Customer --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Заказчик (customer)</label>
                                <select wire:model.live="cargos.{{ $index }}.customer_id" class="{{ $baseInput }} text-xs sm:text-sm">
                                    <option value="">— не указан —</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                    @endforeach
                                </select>
                                @error("cargos.$index.customer_id") <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div> @enderror

                                @if($customer)
                                    <div class="mt-2 text-[11px] bg-white/60 dark:bg-gray-900/40 rounded-xl px-2 py-1.5 border border-gray-200 dark:border-gray-700 space-y-0.5">
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

                            {{-- Shipper --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Грузоотправитель (shipper)</label>
                                <select wire:model.live="cargos.{{ $index }}.shipper_id" class="{{ $baseInput }} text-xs sm:text-sm">
                                    <option value="">— не указан —</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                    @endforeach
                                </select>
                                @error("cargos.$index.shipper_id") <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div> @enderror

                                @if($shipper)
                                    <div class="mt-2 text-[11px] bg-white/60 dark:bg-gray-900/40 rounded-xl px-2 py-1.5 border border-gray-200 dark:border-gray-700 space-y-0.5">
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
                                                @if($shipper->jur_post_code), {{ $shipper->jur_post_code }} @endif
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
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Грузополучатель (consignee)</label>
                                <select wire:model.live="cargos.{{ $index }}.consignee_id" class="{{ $baseInput }} text-xs sm:text-sm">
                                    <option value="">— не указан —</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                    @endforeach
                                </select>
                                @error("cargos.$index.consignee_id") <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div> @enderror

                                @if($consignee)
                                    <div class="mt-2 text-[11px] bg-white/60 dark:bg-gray-900/40 rounded-xl px-2 py-1.5 border border-gray-200 dark:border-gray-700 space-y-0.5">
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
                                                @if($consignee->jur_post_code), {{ $consignee->jur_post_code }} @endif
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
                        <div class="border-t border-emerald-100/70 dark:border-emerald-900/30 pt-3 mt-2 space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                {{-- Loading --}}
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">📦 Шаги погрузки (multi-select)</label>
                                        <span class="text-[10px] text-gray-400">Можно выбрать несколько</span>
                                    </div>

                                    <div class="space-y-2">
                                        @php $hasLoadingSteps = false; @endphp

                                        @foreach($steps as $sIndex => $stepRow)
                                            @if(($stepRow['type'] ?? 'loading') !== 'loading') @continue @endif
                                            @php $hasLoadingSteps = true; @endphp

                                            <label class="block">
                                                <input type="checkbox"
                                                       class="peer hidden"
                                                       value="{{ $sIndex }}"
                                                       wire:model="cargos.{{ $index }}.loading_step_ids">

                                                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 px-3 py-2 text-xs
                                                            bg-white/70 dark:bg-gray-900/40
                                                            peer-checked:bg-blue-50 peer-checked:border-blue-500
                                                            peer-checked:shadow-sm transition-colors">
                                                    <div class="font-semibold text-gray-800 dark:text-gray-100">
                                                        {{ $this->stepLabelByIndex($sIndex) }}
                                                    </div>
                                                </div>
                                            </label>
                                        @endforeach

                                        @if(!$hasLoadingSteps)
                                            <div class="text-[11px] text-gray-400">
                                                Нет шагов погрузки. Добавьте шаг и выберите тип “Погрузка”.
                                            </div>
                                        @endif
                                    </div>

                                    @error("cargos.$index.loading_step_ids")
                                    <div class="mt-1 text-[11px] text-red-500">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Unloading --}}
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">📤 Шаги разгрузки (multi-select)</label>
                                        <span class="text-[10px] text-gray-400">Можно выбрать несколько</span>
                                    </div>

                                    <div class="space-y-2">
                                        @php $hasUnloadingSteps = false; @endphp

                                        @foreach($steps as $sIndex => $stepRow)
                                            @if(($stepRow['type'] ?? 'loading') !== 'unloading') @continue @endif
                                            @php $hasUnloadingSteps = true; @endphp

                                            <label class="block">
                                                <input type="checkbox"
                                                       class="peer hidden"
                                                       value="{{ $sIndex }}"
                                                       wire:model="cargos.{{ $index }}.unloading_step_ids">

                                                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 px-3 py-2 text-xs
                                                            bg-white/70 dark:bg-gray-900/40
                                                            peer-checked:bg-blue-50 peer-checked:border-blue-500
                                                            peer-checked:shadow-sm transition-colors">
                                                    <div class="font-semibold text-gray-800 dark:text-gray-100">
                                                        {{ $this->stepLabelByIndex($sIndex) }}
                                                    </div>
                                                </div>
                                            </label>
                                        @endforeach

                                        @if(!$hasUnloadingSteps)
                                            <div class="text-[11px] text-gray-400">
                                                Нет шагов разгрузки. Добавьте шаг и выберите тип “Разгрузка”.
                                            </div>
                                        @endif
                                    </div>

                                    @error("cargos.$index.unloading_step_ids")
                                    <div class="mt-1 text-[11px] text-red-500">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Payment section --}}
                        <div class="grid grid-cols-2 sm:grid-cols-6 gap-3 border-t border-emerald-100/70 dark:border-emerald-900/30 pt-3 mt-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Цена (без НДС)</label>
                                <input type="text"
                                       wire:model.live="cargos.{{ $index }}.price"
                                       class="{{ $baseInput }} text-xs">
                                @error("cargos.$index.price")
                                <div class="text-[11px] text-red-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">НДС, %</label>
                                <select wire:model.live="cargos.{{ $index }}.tax_percent"
                                        class="{{ $baseInput }} text-xs">
                                    @foreach($taxRates as $rate)
                                        <option value="{{ $rate }}">{{ $rate }}</option>
                                    @endforeach
                                </select>
                                @error("cargos.$index.tax_percent")
                                <div class="text-[11px] text-red-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Сумма НДС</label>
                                <input type="number"
                                       wire:model.defer="cargos.{{ $index }}.total_tax_amount"
                                       class="{{ $baseInput }} text-xs"
                                       readonly>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Итого с НДС</label>
                                <input type="number"
                                       wire:model.defer="cargos.{{ $index }}.price_with_tax"
                                       class="{{ $baseInput }} text-xs"
                                       readonly>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Оплата до</label>
                                <input type="date"
                                       wire:model.defer="cargos.{{ $index }}.payment_terms"
                                       class="{{ $baseInput }} text-xs">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Плательщик (тип)</label>
                                <select wire:model.live="cargos.{{ $index }}.payer_type_id"
                                        class="{{ $baseInput }} text-xs">
                                    <option value="">— не выбрано —</option>
                                    @foreach($payers as $payerId => $payer)
                                        <option value="{{ $payerId }}">{{ $payer['label'] ?? $payerId }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Supplier invoice --}}
                        <div class="rounded-2xl border border-emerald-100/70 dark:border-emerald-900/30 bg-white/60 dark:bg-gray-900/30 p-4">
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <div class="text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-200">
                                    📄 Supplier invoice
                                </div>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                    Необязательно
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-6 gap-3">
                                <div class="sm:col-span-4">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                        Номер инвойса поставщика
                                    </label>
                                    <input type="text"
                                           wire:model.defer="cargos.{{ $index }}.supplier_invoice_nr"
                                           placeholder="Напр. INV-2026-001"
                                           class="{{ $baseInput }}">
                                    @error("cargos.$index.supplier_invoice_nr")
                                    <div class="text-[11px] text-red-500 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                                        Сумма по инвойсу
                                    </label>
                                    <div class="relative">
                                        <input type="number" step="0.01"
                                               wire:model.defer="cargos.{{ $index }}.supplier_invoice_amount"
                                               placeholder="0.00"
                                               class="{{ $baseInput }} pr-10">
                                        <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs text-gray-400">
                                            €
                                        </div>
                                    </div>
                                    @error("cargos.$index.supplier_invoice_amount")
                                    <div class="text-[11px] text-red-500 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- ITEMS --}}
                        <div class="border-t border-emerald-100/70 dark:border-emerald-900/30 pt-3 mt-2 space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                    📑 Позиции груза
                                </div>

                                <button type="button"
                                        wire:click="addItem({{ $index }})"
                                        class="text-xs px-2 py-1 rounded-lg bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200">
                                    ➕ Добавить позицию
                                </button>
                            </div>

                            <div class="space-y-2">
                                @foreach($cargo['items'] as $itemIndex => $item)
                                    @php
                                        $key = "cargos.$index.items.$itemIndex.measurements";
                                        $itemError = $errors->has($key);
                                    @endphp

                                    <div x-data="{ open: {{ $itemError ? 'false' : 'true' }} }"
                                         wire:key="cargo-item-{{ $index }}-{{ $itemIndex }}"
                                         class="rounded-2xl px-3 py-3 space-y-3 border transition
                                                @if($itemError)
                                                    border-red-500 bg-red-50 dark:bg-red-900/20
                                                @else
                                                    border-emerald-100/70 dark:border-emerald-900/30 bg-white/60 dark:bg-gray-900/30
                                                @endif">

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

                                        <div class="grid grid-cols-1 sm:grid-cols-6 gap-2">
                                            <div class="sm:col-span-4">
                                                <div class="text-[10px] uppercase font-semibold text-gray-500 mb-1">Описание</div>
                                                <input type="text"
                                                       placeholder="Например: мебель, техника, продуктовая группа…"
                                                       wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.description"
                                                       class="w-full rounded-xl border text-xs
                                                              @if($itemError) border-red-500 input-error @else border-gray-300 dark:border-gray-700 @endif
                                                              dark:bg-gray-800 dark:text-gray-100">
                                            </div>

                                            <div class="sm:col-span-2">
                                                <div class="text-[10px] uppercase font-semibold text-gray-500 mb-1">Customs code</div>
                                                <input type="text"
                                                       placeholder="HS/TARIC"
                                                       wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.customs_code"
                                                       class="w-full rounded-xl border text-xs
                                                              @if($itemError) border-red-500 @else border-gray-300 dark:border-gray-700 @endif
                                                              dark:bg-gray-800 dark:text-gray-100">
                                                @error("cargos.$index.items.$itemIndex.customs_code")
                                                <div class="text-[11px] text-red-500 mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div>
                                            <div class="text-[10px] uppercase font-semibold text-gray-500 mb-1">Количества</div>
                                            <div class="grid grid-cols-3 gap-2">
                                                @foreach(['packages'=>'Упаковок', 'pallets'=>'Паллет', 'units'=>'Штук'] as $field => $placeholder)
                                                    <input type="text"
                                                           placeholder="{{ $placeholder }}"
                                                           wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.{{ $field }}"
                                                           class="w-full rounded-xl border text-[11px]
                                                                  @if($itemError) border-red-500 @else border-gray-300 dark:border-gray-700 @endif
                                                                  dark:bg-gray-800 dark:text-gray-100">
                                                @endforeach
                                            </div>
                                        </div>

                                        <div>
                                            <div class="text-[10px] uppercase font-semibold text-gray-500 mb-1">Вес</div>
                                            <div class="grid grid-cols-3 gap-2">
                                                @foreach(['net_weight'=>'Нетто, кг', 'gross_weight'=>'Брутто, кг', 'tonnes'=>'Тонны, т'] as $field => $placeholder)
                                                    <input type="text"
                                                           placeholder="{{ $placeholder }}"
                                                           wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.{{ $field }}"
                                                           class="w-full rounded-xl border text-[11px]
                                                                  @if($itemError) border-red-500 @else border-gray-300 dark:border-gray-700 @endif
                                                                  dark:bg-gray-800 dark:text-gray-100">
                                                @endforeach
                                            </div>
                                        </div>

                                        <div>
                                            <div class="text-[10px] uppercase font-semibold text-gray-500 mb-1">Объём / Длина загрузки</div>
                                            <div class="grid grid-cols-2 gap-2">
                                                @foreach(['volume'=>'Объём (м³)', 'loading_meters'=>'LM — погрузочные метры'] as $field => $placeholder)
                                                    <input type="text"
                                                           placeholder="{{ $placeholder }}"
                                                           wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.{{ $field }}"
                                                           class="w-full rounded-xl border text-[11px]
                                                                  @if($itemError) border-red-500 @else border-gray-300 dark:border-gray-700 @endif
                                                                  dark:bg-gray-800 dark:text-gray-100">
                                                @endforeach
                                            </div>
                                        </div>

                                        <div>
                                            <div class="text-[10px] uppercase font-semibold text-gray-500 mb-1">Условия перевозки</div>
                                            <div class="grid grid-cols-3 gap-2 items-center">
                                                <input type="text"
                                                       placeholder="Темп. +2..+6"
                                                       wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.temperature"
                                                       class="w-full rounded-xl border text-[11px]
                                                              @if($itemError) border-red-500 @else border-gray-300 dark:border-gray-700 @endif
                                                              dark:bg-gray-800 dark:text-gray-100">

                                                <select wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.hazmat"
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
                                                           id="stackable_{{ $index }}_{{ $itemIndex }}"
                                                           wire:model.defer="cargos.{{ $index }}.items.{{ $itemIndex }}.stackable"
                                                           class="rounded border text-[11px]
                                                                  @if($itemError) border-red-500 @else border-gray-300 dark:border-gray-600 @endif
                                                                  dark:bg-gray-800">
                                                    <label for="stackable_{{ $index }}_{{ $itemIndex }}"
                                                           class="text-[11px] text-gray-600 dark:text-gray-300">
                                                        Штабелируется
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

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
    <div class="fixed bottom-0 inset-x-0 z-30 bg-white/95 dark:bg-gray-900/95 border-t border-blue-100 dark:border-blue-900/40 backdrop-blur">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
            <div class="text-xs text-gray-500 truncate">
                После сохранения изменения рейса, маршрута и грузов будут записаны в систему.
            </div>
            <button
                wire:click="save"
                wire:loading.attr="disabled"
                class="inline-flex items-center px-4 py-2 rounded-2xl text-sm font-semibold
                       bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white shadow">
                <span wire:loading.remove>💾 Сохранить изменения</span>
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
