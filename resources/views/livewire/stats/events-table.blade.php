@php
    use App\Models\TruckOdometerEvent;
    use App\Enums\TripExpenseCategory;
    use App\Enums\TripStepStatus;
    use App\Enums\OdometerEventType;

    $expenseLabel = function (?string $category) {
        if (!$category) return null;
        if ($category === TripExpenseCategory::SUBCONTRACTOR->value) return null; // hide

        try {
            return TripExpenseCategory::from($category)->label();
        } catch (\Throwable $e) {
            return $category;
        }
    };

    $isFuelLike = function (?string $category) {
        // Оdometer для driver expenses показываем только для Degviela / AdBlue
        return in_array($category, [
            TripExpenseCategory::FUEL->value,
            TripExpenseCategory::ADBLUE->value,
        ], true);
    };

    $stepStatusLabel = function ($stepStatus) {
        if ($stepStatus === null || $stepStatus === '') return null;

        try {
            $val = (int) $stepStatus;
            return TripStepStatus::from($val)->label();
        } catch (\Throwable $e) {
            return null;
        }
    };

@endphp

<div class="space-y-4">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">🧾 {{ __('app.stats.events.title') }}</h1>
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.stats.events.subtitle') }}</div>
        </div>

        <button
            type="button"
            wire:click="$toggle('filtersOpen')"
            class="md:hidden px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 font-semibold text-sm"
        >
            🔎 {{ __('app.stats.filters') }}
        </button>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 p-4 space-y-3 @if(!$filtersOpen) hidden md:block @endif">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
            <div class="md:col-span-2">
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('app.stats.events.search') }}</label>
                <input
                    type="text"
                    wire:model.live.debounce.400ms="search"
                    placeholder="{{ __('app.stats.events.search') }}"
                    class="w-full mt-1 rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm"
                >
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('app.stats.events.type') }}</label>
                <select wire:model.live="type" class="w-full mt-1 rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
                    <option value="">{{ __('app.stats.events.all') }}</option>
                    @foreach($types as $k => $v)
                        <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('app.stats.events.driver') }}</label>
                <select wire:model.live="driverId" class="w-full mt-1 rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
                    <option value="">{{ __('app.stats.events.all') }}</option>
                    @foreach($drivers as $d)
                        <option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('app.stats.events.truck') }}</label>
                <select wire:model.live="truckId" class="w-full mt-1 rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
                    <option value="">{{ __('app.stats.events.all') }}</option>
                    @foreach($trucks as $t)
                        <option value="{{ $t['id'] }}">{{ $t['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('app.stats.events.per_page') }}</label>
                <select wire:model.live="perPage" class="w-full mt-1 rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('app.stats.events.date_from') }}</label>
                <input type="date" wire:model.live="dateFrom" class="w-full mt-1 rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('app.stats.events.date_to') }}</label>
                <input type="date" wire:model.live="dateTo" class="w-full mt-1 rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
            </div>

            <div class="md:col-span-4 flex gap-2 justify-end">
                <button
                    type="button"
                    wire:click="clearFilters"
                    class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-800"
                >
                    {{ __('app.stats.events.clear') }}
                </button>
            </div>
        </div>
    </div>

    <div wire:loading class="text-sm text-gray-500 dark:text-gray-400">⏳ {{ __('app.stats.events.loading') }}</div>

    {{-- MOBILE cards (PWA) --}}
    <div class="space-y-3 md:hidden">
        @forelse($rows as $row)
            @php
                $driver = trim(($row->d_first_name ?? '').' '.($row->d_last_name ?? '')) ?: '—';

                $truckPlate = $row->tr_plate ?? '';
                $truckModel = $row->tr_model ?? '';
                $truck = trim(($row->tr_brand ?? '').' '.$truckModel.' '.$truckPlate) ?: '—';

                $rowKind = $row->row_kind ?? 'event';
                $typeVal = (int)($row->type ?? 0);
                $isEventRow = $rowKind === 'event';
                $isExpenseRow = $rowKind === 'expense';
                // Маппинг типов TruckOdometerEvent -> OdometerEventType только для RUN-событий
                $typeEnum = match ($typeVal) {
                    TruckOdometerEvent::TYPE_DEPARTURE => OdometerEventType::RUN_START,
                    TruckOdometerEvent::TYPE_RETURN    => OdometerEventType::RUN_END,
                    default                            => null,
                };

                $rawOccurred = $row->occurred_at ?? null;
                $rawExpenseDate = $row->expense_date ?? null;

                if (!empty($rawOccurred)) {
                    $ts = date('d.m.Y H:i', strtotime($rawOccurred));
                } elseif (!empty($rawExpenseDate)) {
                    $ts = date('d.m.Y', strtotime($rawExpenseDate));
                } else {
                    $ts = '—';
                }

                // Odo for mobile: for departure/return можем падать назад на trip.odo_start_km / odo_end_km
                $odoMainValue = $row->odometer_km ?? null;
                if ($rowKind === 'event' && $odoMainValue === null) {
                    if ($typeVal === TruckOdometerEvent::TYPE_DEPARTURE && $row->trip_odo_start_km !== null) {
                        $odoMainValue = $row->trip_odo_start_km;
                    } elseif ($typeVal === TruckOdometerEvent::TYPE_RETURN && $row->trip_odo_end_km !== null) {
                        $odoMainValue = $row->trip_odo_end_km;
                    }
                }
                // expense
                $cat = $isExpenseRow ? ($row->expense_category ?? null) : null;
                $isHiddenSubcontractor = $isExpenseRow && ($cat === TripExpenseCategory::SUBCONTRACTOR->value);

                $catLabel = $isExpenseRow ? $expenseLabel($cat) : null;
                $amount = $isExpenseRow ? ($row->amount ?? null) : null;
                $currency = $isExpenseRow ? ($row->te_currency ?? 'EUR') : null;

                $liters = $isExpenseRow ? ($row->te_liters ?? null) : null;
                $odoExpense = $isExpenseRow ? ($row->odometer_km ?? null) : null;
                $fuelLike = $isExpenseRow ? $isFuelLike($cat) : false;

                // Для driver expenses одометр в "шапке" показываем только для Degviela / AdBlue
                if ($isExpenseRow && !$fuelLike) {
                    $odoMainValue = null;
                    $odoExpense = null;
                }

                $odoMain = $odoMainValue !== null
                    ? number_format((float)$odoMainValue, 1, ',', ' ') . ' km'
                    : null;

                // Тип/бейдж:
                // - row_kind=expense  -> Driver expenses (зелёный)
                // - row_kind=event + TYPE_STEP      -> Step + статус шага (голубой)
                // - row_kind=event + RUN_START/END  -> OdometerEventType (amber/violet)
                // - остальное                       -> Event (серый)
                $typeLabel = null;
                $badgeClass = null;

                        if ($isExpenseRow) {
                            $typeLabel = __('app.stats.events.badge_expense');
                    $badgeClass = 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-200 dark:border-emerald-800';
                } elseif ($isEventRow && $typeVal === TruckOdometerEvent::TYPE_STEP) {
                            $stepLabel = $stepStatusLabel($row->step_status ?? null);
                            $typeLabel = $stepLabel
                                ? __('app.stats.events.badge_step_prefix', ['status' => $stepLabel])
                                : __('app.stats.events.badge_step');
                    $badgeClass = 'bg-sky-50 text-sky-800 border-sky-200 dark:bg-sky-900/20 dark:text-sky-200 dark:border-sky-800';
                } elseif ($isEventRow && $typeEnum) {
                    $typeLabel = $typeEnum->label();
                    $badgeClass = $typeEnum->badgeClass();
                } else {
                            $typeLabel = __('app.stats.events.badge_event');
                    $badgeClass = 'bg-gray-50 text-gray-800 border-gray-200 dark:bg-gray-800/40 dark:text-gray-200 dark:border-gray-700';
                }
            @endphp

            @if(!$isHiddenSubcontractor)
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm p-4 space-y-2">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-semibold text-gray-900 dark:text-gray-100 truncate">👨‍✈️ {{ $driver }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-300 truncate">🚛 {{ $truck }}</div>
                        </div>

                        <span class="shrink-0 inline-flex items-center px-2 py-1 rounded-lg border text-xs font-semibold {{ $badgeClass }}">
                            {{ $typeLabel }}
                        </span>
                    </div>

                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        🕒 {{ $ts }}
                        @if($odoMain)
                            <span class="ml-2">• ⛽ {{ $odoMain }}</span>
                        @endif
                    </div>

                    @if($isExpenseRow)
                        <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/40 p-3 space-y-1">
                            <div class="font-semibold text-gray-900 dark:text-gray-100">
                                {{ $catLabel ?? ($cat ?? '—') }}
                            </div>

                            <div class="text-sm text-gray-800 dark:text-gray-200">
                                @if($amount !== null)
                                    {{ $currency }} {{ number_format((float)$amount, 2, ',', ' ') }}
                                @else
                                    —
                                @endif
                            </div>

                            @if($fuelLike)
                                <div class="text-xs text-gray-600 dark:text-gray-300 flex flex-wrap gap-2 pt-1">
                                    <span class="inline-flex items-center rounded-full px-2 py-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                                        🧴 {{ $liters !== null ? number_format((float)$liters, 2, ',', ' ') : '—' }} L
                                    </span>
                                    <span class="inline-flex items-center rounded-full px-2 py-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                                        ⛽ {{ $odoExpense !== null ? number_format((float)$odoExpense, 1, ',', ' ') : '—' }} km
                                    </span>
                                </div>
                            @elseif(!empty($row->te_description))
                                <div class="text-xs text-gray-600 dark:text-gray-300">
                                    📝 {{ $row->te_description }}
                                </div>
                            @endif
                        </div>
                    @else
                        @if($row->note)
                            <div class="text-sm text-gray-600 dark:text-gray-300">📝 {{ $row->note }}</div>
                        @endif
                    @endif
                </div>
            @endif
        @empty
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 text-center text-gray-500 dark:text-gray-400">
                {{ __('app.stats.events.no_events') }}
            </div>
        @endforelse

        <div class="pt-2">
            {{ $rows->links() }}
        </div>
    </div>

    {{-- DESKTOP table --}}
    <div class="hidden md:block bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 text-xs uppercase text-gray-600 dark:text-gray-300">
                <tr>
                    <th class="px-4 py-3 text-left cursor-pointer" wire:click="sortBy('driver')">
                        {{ __('app.stats.events.driver') }} @if($sortField === 'driver') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                    </th>
                    <th class="px-4 py-3 text-left cursor-pointer" wire:click="sortBy('truck')">
                        {{ __('app.stats.events.truck') }} @if($sortField === 'truck') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                    </th>
                    <th class="px-4 py-3 text-left cursor-pointer" wire:click="sortBy('type')">
                        {{ __('app.stats.events.col_event') }} @if($sortField === 'type') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                    </th>
                    <th class="px-4 py-3 text-left cursor-pointer whitespace-nowrap" wire:click="sortBy('timestamp')">
                        {{ __('app.stats.events.col_timestamp') }} @if($sortField === 'timestamp') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                    </th>
                    <th class="px-4 py-3 text-right cursor-pointer whitespace-nowrap" wire:click="sortBy('odometer_km')">
                        {{ __('app.stats.events.col_odo') }} @if($sortField === 'odometer_km') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                    </th>
                    <th class="px-4 py-3 text-right cursor-pointer whitespace-nowrap" wire:click="sortBy('amount')">
                        {{ __('app.stats.events.col_amount') }} @if($sortField === 'amount') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                    </th>
                    <th class="px-4 py-3 text-left">
                        {{ __('app.stats.events.col_details') }}
                    </th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($rows as $row)
                    @php
                $driver = trim(($row->d_first_name ?? '').' '.($row->d_last_name ?? '')) ?: '—';

                        $truckPlate = $row->tr_plate ?? '';
                        $truckModel = $row->tr_model ?? '';
                        $truck = trim(($row->tr_brand ?? '').' '.$truckModel.' '.$truckPlate) ?: '—';

                        $rowKind = $row->row_kind ?? 'event';
                        $typeVal = (int)($row->type ?? 0);
                        $isEventRow = $rowKind === 'event';
                        $isExpenseRow = $rowKind === 'expense';
                // Маппинг типов TruckOdometerEvent -> OdometerEventType только для RUN-событий
                $typeEnum = match ($typeVal) {
                    TruckOdometerEvent::TYPE_DEPARTURE => OdometerEventType::RUN_START,
                    TruckOdometerEvent::TYPE_RETURN    => OdometerEventType::RUN_END,
                    default                            => null,
                };

                        $rawOccurred = $row->occurred_at ?? null;
                        $rawExpenseDate = $row->expense_date ?? null;

                        if (!empty($rawOccurred)) {
                            $ts = date('d.m.Y H:i', strtotime($rawOccurred));
                        } elseif (!empty($rawExpenseDate)) {
                            $ts = date('d.m.Y', strtotime($rawExpenseDate));
                        } else {
                            $ts = '—';
                        }

                        $odoMainValue = $row->odometer_km ?? null;
                        if ($rowKind === 'event' && $odoMainValue === null) {
                            if ($typeVal === TruckOdometerEvent::TYPE_DEPARTURE && $row->trip_odo_start_km !== null) {
                                $odoMainValue = $row->trip_odo_start_km;
                            } elseif ($typeVal === TruckOdometerEvent::TYPE_RETURN && $row->trip_odo_end_km !== null) {
                                $odoMainValue = $row->trip_odo_end_km;
                            }
                        }
                        // expense
                        $cat = $isExpenseRow ? ($row->expense_category ?? null) : null;
                        $isHiddenSubcontractor = $isExpenseRow && ($cat === TripExpenseCategory::SUBCONTRACTOR->value);

                        $catLabel = $isExpenseRow ? $expenseLabel($cat) : null;

                        $amount = $isExpenseRow ? ($row->amount ?? null) : null;
                        $currency = $isExpenseRow ? ($row->te_currency ?? 'EUR') : null;

                        $liters = $isExpenseRow ? ($row->te_liters ?? null) : null;
                        $odoExpense = $isExpenseRow ? ($row->odometer_km ?? null) : null;
                        $fuelLike = $isExpenseRow ? $isFuelLike($cat) : false;

                        // Для driver expenses одометр в колонке Odo показываем только для Degviela / AdBlue
                        if ($isExpenseRow && !$fuelLike) {
                            $odoMainValue = null;
                            $odoExpense = null;
                        }

                        $odo = $odoMainValue !== null
                            ? number_format((float)$odoMainValue, 1, ',', ' ')
                            : '—';

                        $typeLabel = null;
                        $badgeClass = null;

                if ($isExpenseRow) {
                    $typeLabel = __('app.stats.events.badge_expense');
                            $badgeClass = 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-200 dark:border-emerald-800';
                        } elseif ($isEventRow && $typeVal === TruckOdometerEvent::TYPE_STEP) {
                    $stepLabel = $stepStatusLabel($row->step_status ?? null);
                    $typeLabel = $stepLabel
                        ? __('app.stats.events.badge_step_prefix', ['status' => $stepLabel])
                        : __('app.stats.events.badge_step');
                            $badgeClass = 'bg-sky-50 text-sky-800 border-sky-200 dark:bg-sky-900/20 dark:text-sky-200 dark:border-sky-800';
                        } elseif ($isEventRow && $typeEnum) {
                            $typeLabel = $typeEnum->label();
                            $badgeClass = $typeEnum->badgeClass();
                        } else {
                    $typeLabel = __('app.stats.events.badge_event');
                            $badgeClass = 'bg-gray-50 text-gray-800 border-gray-200 dark:bg-gray-800/40 dark:text-gray-200 dark:border-gray-700';
                        }
                    @endphp

                    @if(!$isHiddenSubcontractor)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60">
                            <td class="px-4 py-3">{{ $driver }}</td>
                            <td class="px-4 py-3">{{ $truck }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-lg border text-xs font-semibold {{ $badgeClass }}">
                                    {{ $typeLabel }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $ts }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">{{ $odo }}</td>

                            {{-- Amount --}}
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                @if($isExpenseRow && $amount !== null)
                                    {{ $currency }} {{ number_format((float)$amount, 2, ',', ' ') }}
                                @else
                                    —
                                @endif
                            </td>

                            {{-- Details --}}
                            <td class="px-4 py-3">
                                @if($isExpenseRow)
                                    <div class="space-y-1">
                                        <div class="font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $catLabel ?? ($cat ?? '—') }}
                                        </div>

                                        @if($fuelLike)
                                            <div class="text-xs text-gray-600 dark:text-gray-300 flex flex-wrap gap-2">
                                                <span class="inline-flex items-center rounded-full px-2 py-1 bg-gray-100 dark:bg-gray-800">
                                                    🧴 {{ $liters !== null ? number_format((float)$liters, 2, ',', ' ') : '—' }} L
                                                </span>
                                                <span class="inline-flex items-center rounded-full px-2 py-1 bg-gray-100 dark:bg-gray-800">
                                                    ⛽ {{ $odoExpense !== null ? number_format((float)$odoExpense, 1, ',', ' ') : '—' }} km
                                                </span>
                                            </div>
                                        @elseif(!empty($row->te_description))
                                            <div class="text-xs text-gray-600 dark:text-gray-300">
                                                📝 {{ $row->te_description }}
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">{{ __('app.stats.events.no_events') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 border-t border-gray-200 dark:border-gray-800">
            {{ $rows->links() }}
        </div>
    </div>

</div>
