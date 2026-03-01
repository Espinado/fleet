@php
    use App\Models\TruckOdometerEvent;
    use App\Enums\TripExpenseCategory;
    use App\Enums\TripStepStatus;

    $badge = function (int $type) {
        return match ($type) {
            TruckOdometerEvent::TYPE_DEPARTURE => 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-900/20 dark:text-amber-200 dark:border-amber-800',
            TruckOdometerEvent::TYPE_RETURN    => 'bg-violet-50 text-violet-800 border-violet-200 dark:bg-violet-900/20 dark:text-violet-200 dark:border-violet-800',
            TruckOdometerEvent::TYPE_EXPENSE   => 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-200 dark:border-emerald-800',
            TruckOdometerEvent::TYPE_STEP      => 'bg-sky-50 text-sky-800 border-sky-200 dark:bg-sky-900/20 dark:text-sky-200 dark:border-sky-800',
            default => 'bg-gray-50 text-gray-800 border-gray-200 dark:bg-gray-800/40 dark:text-gray-200 dark:border-gray-700',
        };
    };

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
        return in_array($category, [
            TripExpenseCategory::FUEL->value,
            TripExpenseCategory::ADBLUE->value,
            TripExpenseCategory::WASHER_FLUID->value,
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
            <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">🧾 Driver events</h1>
            <div class="text-sm text-gray-500 dark:text-gray-400">Departure / Return / Step / Driver expenses</div>
        </div>

        <button
            type="button"
            wire:click="$toggle('filtersOpen')"
            class="md:hidden px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 font-semibold text-sm"
        >
            🔎 Filters
        </button>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 p-4 space-y-3 @if(!$filtersOpen) hidden md:block @endif">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
            <div class="md:col-span-2">
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Search</label>
                <input
                    type="text"
                    wire:model.live.debounce.400ms="search"
                    placeholder="Driver / Truck / Expense / Note..."
                    class="w-full mt-1 rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm"
                >
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Type</label>
                <select wire:model.live="type" class="w-full mt-1 rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
                    <option value="">— all —</option>
                    @foreach($types as $k => $v)
                        <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Driver</label>
                <select wire:model.live="driverId" class="w-full mt-1 rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
                    <option value="">— all —</option>
                    @foreach($drivers as $d)
                        <option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Truck</label>
                <select wire:model.live="truckId" class="w-full mt-1 rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
                    <option value="">— all —</option>
                    @foreach($trucks as $t)
                        <option value="{{ $t['id'] }}">{{ $t['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Per page</label>
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
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Date from</label>
                <input type="date" wire:model.live="dateFrom" class="w-full mt-1 rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Date to</label>
                <input type="date" wire:model.live="dateTo" class="w-full mt-1 rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
            </div>

            <div class="md:col-span-4 flex gap-2 justify-end">
                <button
                    type="button"
                    wire:click="clearFilters"
                    class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-800"
                >
                    Clear
                </button>
            </div>
        </div>
    </div>

    <div wire:loading class="text-sm text-gray-500 dark:text-gray-400">⏳ Loading…</div>

    {{-- MOBILE cards (PWA) --}}
    <div class="space-y-3 md:hidden">
        @forelse($rows as $row)
            @php
                $driver = trim(($row->d_first_name ?? '').' '.($row->d_last_name ?? '')) ?: '—';

                $truckPlate = $row->tr_plate ?? '';
                $truckModel = $row->tr_model ?? '';
                $truck = trim(($row->tr_brand ?? '').' '.$truckModel.' '.$truckPlate) ?: '—';

                $typeVal = (int)($row->type ?? 0);

                $ts = optional($row->occurred_at)->format('d.m.Y H:i') ?? '—';
                $odoMain = $row->odometer_km !== null ? number_format((float)$row->odometer_km, 1, ',', ' ') . ' km' : null;

                // expense
                $cat = $row->expense_category ?: ($row->te_category ?? null);
                $isExpenseRow = $typeVal === TruckOdometerEvent::TYPE_EXPENSE;
                $isHiddenSubcontractor = $isExpenseRow && ($cat === TripExpenseCategory::SUBCONTRACTOR->value);

                $catLabel = $expenseLabel($cat);
                $amount = $row->expense_amount ?? null;
                $currency = $row->te_currency ?? 'EUR';

                $liters = $row->te_liters ?? null;
                $odoExpense = $row->odometer_km ?? ($row->te_odometer_km ?? null);
                $fuelLike = $isFuelLike($cat);

                // step label
                $stepLabel = $typeVal === TruckOdometerEvent::TYPE_STEP
                    ? ($stepStatusLabel($row->step_status) ?? null)
                    : null;

                $typeLabel = match ($typeVal) {
                    TruckOdometerEvent::TYPE_DEPARTURE => 'Garage departure',
                    TruckOdometerEvent::TYPE_RETURN => 'Garage return',
                    TruckOdometerEvent::TYPE_EXPENSE => 'Driver expenses',
                    TruckOdometerEvent::TYPE_STEP => ($stepLabel ? ('Step: '.$stepLabel) : 'Step status'),
                    default => 'Event',
                };
            @endphp

            @if(!$isHiddenSubcontractor)
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm p-4 space-y-2">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-semibold text-gray-900 dark:text-gray-100 truncate">👨‍✈️ {{ $driver }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-300 truncate">🚛 {{ $truck }}</div>
                        </div>

                        <span class="shrink-0 inline-flex items-center px-2 py-1 rounded-lg border text-xs font-semibold {{ $badge($typeVal) }}">
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
                No events
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
                        Driver @if($sortField === 'driver') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                    </th>
                    <th class="px-4 py-3 text-left cursor-pointer" wire:click="sortBy('truck')">
                        Truck @if($sortField === 'truck') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                    </th>
                    <th class="px-4 py-3 text-left cursor-pointer" wire:click="sortBy('type')">
                        Event @if($sortField === 'type') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                    </th>
                    <th class="px-4 py-3 text-left cursor-pointer whitespace-nowrap" wire:click="sortBy('timestamp')">
                        Timestamp @if($sortField === 'timestamp') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                    </th>
                    <th class="px-4 py-3 text-right cursor-pointer whitespace-nowrap" wire:click="sortBy('odometer_km')">
                        Odo @if($sortField === 'odometer_km') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                    </th>
                    <th class="px-4 py-3 text-right cursor-pointer whitespace-nowrap" wire:click="sortBy('amount')">
                        Amount @if($sortField === 'amount') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                    </th>
                    <th class="px-4 py-3 text-left">
                        Details
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

                        $typeVal = (int)($row->type ?? 0);

                        $ts = optional($row->occurred_at)->format('d.m.Y H:i') ?? '—';
                        $odo = $row->odometer_km !== null ? number_format((float)$row->odometer_km, 1, ',', ' ') : '—';

                        // expense
                        $cat = $row->expense_category ?: ($row->te_category ?? null);
                        $isExpenseRow = $typeVal === TruckOdometerEvent::TYPE_EXPENSE;
                        $isHiddenSubcontractor = $isExpenseRow && ($cat === TripExpenseCategory::SUBCONTRACTOR->value);

                        $catLabel = $expenseLabel($cat);

                        $amount = $row->expense_amount ?? null;
                        $currency = $row->te_currency ?? 'EUR';

                        $liters = $row->te_liters ?? null;
                        $odoExpense = $row->odometer_km ?? ($row->te_odometer_km ?? null);
                        $fuelLike = $isFuelLike($cat);

                        // step label
                        $stepLabel = $typeVal === TruckOdometerEvent::TYPE_STEP
                            ? ($stepStatusLabel($row->step_status) ?? null)
                            : null;

                        $typeLabel = match ($typeVal) {
                            TruckOdometerEvent::TYPE_DEPARTURE => 'Garage departure',
                            TruckOdometerEvent::TYPE_RETURN => 'Garage return',
                            TruckOdometerEvent::TYPE_EXPENSE => 'Driver expenses',
                            TruckOdometerEvent::TYPE_STEP => ($stepLabel ? ('Step: '.$stepLabel) : 'Step status'),
                            default => 'Event',
                        };
                    @endphp

                    @if(!$isHiddenSubcontractor)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60">
                            <td class="px-4 py-3">{{ $driver }}</td>
                            <td class="px-4 py-3">{{ $truck }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-lg border text-xs font-semibold {{ $badge($typeVal) }}">
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
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No events</td>
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
