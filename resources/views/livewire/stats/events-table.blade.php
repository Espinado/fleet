@php
    $badge = function (int $type) {
        return match ($type) {
            1 => 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-900/20 dark:text-amber-200 dark:border-amber-800', // departure
            2 => 'bg-violet-50 text-violet-800 border-violet-200 dark:bg-violet-900/20 dark:text-violet-200 dark:border-violet-800', // return
            3 => 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-200 dark:border-emerald-800', // fuel
            default => 'bg-gray-50 text-gray-800 border-gray-200 dark:bg-gray-800/40 dark:text-gray-200 dark:border-gray-700',
        };
    };
@endphp

<div class="space-y-4">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">🧾 Driver events</h1>
            <div class="text-sm text-gray-500">Departure / Return / Fuel-odometer</div>
        </div>

        <button
            type="button"
            wire:click="$toggle('filtersOpen')"
            class="md:hidden px-4 py-2 rounded-xl border border-gray-300 bg-white font-semibold text-sm"
        >
            🔎 Filters
        </button>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 space-y-3 @if(!$filtersOpen) hidden md:block @endif">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
            <div class="md:col-span-2">
                <label class="text-xs font-semibold text-gray-600">Search</label>
                <input
                    type="text"
                    wire:model.live.debounce.400ms="search"
                    placeholder="Driver / Truck..."
                    class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                >
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600">Type</label>
                <select wire:model.live="type" class="w-full mt-1 rounded-lg border-gray-300 text-sm">
                    <option value="">— all —</option>
                    @foreach($types as $k => $v)
                        <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600">Driver</label>
                <select wire:model.live="driverId" class="w-full mt-1 rounded-lg border-gray-300 text-sm">
                    <option value="">— all —</option>
                    @foreach($drivers as $d)
                        <option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600">Truck</label>
                <select wire:model.live="truckId" class="w-full mt-1 rounded-lg border-gray-300 text-sm">
                    <option value="">— all —</option>
                    @foreach($trucks as $t)
                        <option value="{{ $t['id'] }}">{{ $t['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600">Per page</label>
                <select wire:model.live="perPage" class="w-full mt-1 rounded-lg border-gray-300 text-sm">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
            <div>
                <label class="text-xs font-semibold text-gray-600">Date from</label>
                <input type="date" wire:model.live="dateFrom" class="w-full mt-1 rounded-lg border-gray-300 text-sm">
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600">Date to</label>
                <input type="date" wire:model.live="dateTo" class="w-full mt-1 rounded-lg border-gray-300 text-sm">
            </div>

            <div class="md:col-span-4 flex gap-2 justify-end">
                <button
                    type="button"
                    wire:click="clearFilters"
                    class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-semibold hover:bg-gray-50"
                >
                    Clear
                </button>
            </div>
        </div>
    </div>

    <div wire:loading class="text-sm text-gray-500">⏳ Loading…</div>

    {{-- MOBILE cards (PWA) --}}
    <div class="space-y-3 md:hidden">
        @forelse($rows as $row)
            @php
                $driver = trim(($row->d_first_name ?? '').' '.($row->d_last_name ?? '')) ?: '—';

                $truckPlate = $row->tr_plate ?? '';
                $truckModel = $row->tr_model ?? '';
                $truck = trim(($row->tr_brand ?? '').' '.$truckModel.' '.$truckPlate) ?: '—';

                $typeVal = (int)($row->type ?? 0);
                $typeLabel = $types[$typeVal] ?? ('Type #'.$typeVal);

                $ts = optional($row->occurred_at)->format('d.m.Y H:i') ?? '—';
                $odo = $row->odometer_km !== null ? number_format((float)$row->odometer_km, 1, ',', ' ') . ' km' : null;
            @endphp

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-2">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-semibold text-gray-900 truncate">👨‍✈️ {{ $driver }}</div>
                        <div class="text-sm text-gray-600 truncate">🚛 {{ $truck }}</div>
                    </div>

                    <span class="shrink-0 inline-flex items-center px-2 py-1 rounded-lg border text-xs font-semibold {{ $badge($typeVal) }}">
                        {{ $typeLabel }}
                    </span>
                </div>

                <div class="text-sm text-gray-500">
                    🕒 {{ $ts }}
                    @if($odo)
                        <span class="ml-2">• ⛽ {{ $odo }}</span>
                    @endif
                </div>

                @if($row->note)
                    <div class="text-sm text-gray-600">📝 {{ $row->note }}</div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-gray-200 p-6 text-center text-gray-500">
                No events
            </div>
        @endforelse

        <div class="pt-2">
            {{ $rows->links() }}
        </div>
    </div>

    {{-- DESKTOP table --}}
    <div class="hidden md:block bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-600">
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
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                @forelse($rows as $row)
                    @php
                        $driver = trim(($row->d_first_name ?? '').' '.($row->d_last_name ?? '')) ?: '—';

                        $truckPlate = $row->tr_plate ?? '';
                        $truckModel = $row->tr_model ?? '';
                        $truck = trim(($row->tr_brand ?? '').' '.$truckModel.' '.$truckPlate) ?: '—';

                        $typeVal = (int)($row->type ?? 0);
                        $typeLabel = $types[$typeVal] ?? ('Type #'.$typeVal);

                        $ts = optional($row->occurred_at)->format('d.m.Y H:i') ?? '—';
                        $odo = $row->odometer_km !== null ? number_format((float)$row->odometer_km, 1, ',', ' ') : '—';
                    @endphp

                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $driver }}</td>
                        <td class="px-4 py-3">{{ $truck }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-1 rounded-lg border text-xs font-semibold {{ $badge($typeVal) }}">
                                {{ $typeLabel }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $ts }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">{{ $odo }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">No events</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 border-t border-gray-200">
            {{ $rows->links() }}
        </div>
    </div>

</div>
