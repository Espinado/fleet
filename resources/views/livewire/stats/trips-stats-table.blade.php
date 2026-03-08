<div class="min-h-screen bg-gray-100 pb-28">

    {{-- =========================
         TOP BAR (sticky)
    ========================== --}}
    <div class="sticky top-0 z-30 bg-white/95 border-b border-gray-200 backdrop-blur">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-lg sm:text-2xl font-semibold text-gray-800 truncate">
                    📊 Statistika
                </h1>

                {{-- Mobile: tiny subtitle with applied dates --}}
                <div class="sm:hidden text-xs text-gray-500 truncate">
                    @if($dateFrom || $dateTo)
                        {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d.m.Y') : '…' }}
                        →
                        {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d.m.Y') : '…' }}
                    @else
                        Periods: viss laiks
                    @endif
                </div>
            </div>

            {{-- Rows badge (desktop) --}}
            <div class="hidden sm:flex text-sm text-gray-600 items-center">
                <span class="px-3 py-2 rounded-xl bg-gray-200/60">
                    Rindu skaits: {{ $rows->total() }}
                </span>
            </div>
        </div>

        {{-- Desktop filters --}}
        <div class="hidden sm:block max-w-7xl mx-auto px-4 pb-3">
            <div class="grid grid-cols-4 gap-2">
                <input
                    type="text"
                    wire:model.live.debounce.400ms="search"
                    placeholder="Meklēt: reisa ID, vadītājs, numurzīme..."
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-900
                           focus:outline-none focus:ring-2 focus:ring-blue-200"
                />

                <input
                    type="date"
                    wire:model.live="dateFrom"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-900
                           focus:outline-none focus:ring-2 focus:ring-blue-200"
                />

                <input
                    type="date"
                    wire:model.live="dateTo"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-900
                           focus:outline-none focus:ring-2 focus:ring-blue-200"
                />

                <div class="flex items-center justify-end gap-2">
                    <button wire:click="quickRange(7)"  class="px-3 py-2.5 rounded-xl bg-gray-100 border border-gray-200 text-sm">7d</button>
                    <button wire:click="quickRange(30)" class="px-3 py-2.5 rounded-xl bg-gray-100 border border-gray-200 text-sm">30d</button>
                    <button wire:click="quickRange(90)" class="px-3 py-2.5 rounded-xl bg-gray-100 border border-gray-200 text-sm">90d</button>

                    <button
                        wire:click="clearDates"
                        class="px-3 py-2.5 rounded-xl bg-gray-100 border border-gray-200 text-sm font-semibold"
                        title="Всё время"
                    >
                        ∞
                    </button>

                    <button
                        wire:click="resetFilters"
                        class="px-3 py-2.5 rounded-xl bg-gray-100 border border-gray-200 text-sm font-semibold"
                        title="Сбросить всё"
                    >
                        ♻️
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile quick row (chips + rows) --}}
        <div class="sm:hidden max-w-7xl mx-auto px-4 pb-3">
            <div class="flex items-center justify-between gap-2">
                <div class="text-sm text-gray-700">
                    <span class="font-semibold">{{ $rows->total() }}</span>
                    <span class="text-gray-500">rindas</span>
                </div>

                <div class="flex gap-2 overflow-x-auto no-scrollbar">
                    <button wire:click="quickRange(7)"
                            class="shrink-0 px-3 py-1.5 rounded-full text-sm bg-gray-200/70 text-gray-800">
                        7d
                    </button>
                    <button wire:click="quickRange(30)"
                            class="shrink-0 px-3 py-1.5 rounded-full text-sm bg-gray-200/70 text-gray-800">
                        30d
                    </button>
                    <button wire:click="quickRange(90)"
                            class="shrink-0 px-3 py-1.5 rounded-full text-sm bg-gray-200/70 text-gray-800">
                        90d
                    </button>
                    <button wire:click="clearDates"
                            class="shrink-0 px-3 py-1.5 rounded-full text-sm bg-gray-200/70 text-gray-800">
                        ∞
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================
         SUMMARY (сводка по фильтрам)
    ========================== --}}
    <div class="max-w-7xl mx-auto px-4 pt-4">
        <div class="mb-4 rounded-2xl bg-white border border-gray-200 shadow-sm p-4">
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
                {{ __('app.stats.summary_period') }}
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
                <div class="rounded-xl bg-gray-50 border border-gray-100 p-3">
                    <div class="text-xs text-gray-500 mb-0.5">{{ __('app.stats.summary_trips') }}</div>
                    <div class="text-lg font-bold text-gray-900">{{ number_format($summary->trips_count, 0, '.', ' ') }}</div>
                </div>
                <div class="rounded-xl bg-gray-50 border border-gray-100 p-3">
                    <div class="text-xs text-gray-500 mb-0.5">{{ __('app.stats.freight_total') }}</div>
                    <div class="text-lg font-bold text-gray-900">{{ number_format($summary->total_freight, 2, '.', ' ') }} <span class="text-xs font-normal text-gray-500">EUR</span></div>
                </div>
                <div class="rounded-xl bg-gray-50 border border-gray-100 p-3">
                    <div class="text-xs text-gray-500 mb-0.5">{{ __('app.stats.expenses_total') }}</div>
                    <div class="text-lg font-bold text-gray-900">{{ number_format($summary->total_expenses, 2, '.', ' ') }} <span class="text-xs font-normal text-gray-500">EUR</span></div>
                </div>
                <div class="rounded-xl bg-gray-50 border border-gray-100 p-3">
                    <div class="text-xs text-gray-500 mb-0.5">{{ __('app.stats.profit') }}</div>
                    <div class="text-lg font-bold {{ $summary->total_profit >= 0 ? 'text-green-700' : 'text-red-600' }}">{{ number_format($summary->total_profit, 2, '.', ' ') }} <span class="text-xs font-normal text-gray-500">EUR</span></div>
                </div>
                <div class="rounded-xl bg-gray-50 border border-gray-100 p-3">
                    <div class="text-xs text-gray-500 mb-0.5">{{ __('app.stats.margin') }} %</div>
                    @if($summary->avg_margin_percent !== null)
                        <div class="text-lg font-bold text-gray-900">{{ number_format($summary->avg_margin_percent, 1, '.', ' ') }}%</div>
                    @else
                        <div class="text-lg font-bold text-gray-400">—</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- График по периодам --}}
        @if(!empty($chartData['labels']))
        <div class="mb-4 rounded-2xl bg-white border border-gray-200 shadow-sm p-4">
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
                {{ __('app.stats.chart_by_period') }}
            </div>
            <div class="h-64 sm:h-80 relative">
                <canvas id="stats-chart-canvas" width="400" height="300"></canvas>
            </div>
            <script type="application/json" id="stats-chart-data">@json($chartData)</script>
        </div>
        @endif

    {{-- =========================
         CONTENT
    ========================== --}}
        {{-- MOBILE: CARDS --}}
        <div class="space-y-3 md:hidden">
            @forelse($rows as $trip)
                @php
                    $freight  = (float)($trip->freight_total ?? 0);
                    $expenses = (float)($trip->expenses_total ?? 0);
                    $profit   = (float)($trip->profit ?? ($freight - $expenses));
                    $cur      = $trip->currency ?? '';
                    $isThirdPartyMobile = $trip->carrierCompany?->is_third_party ?? false;
                @endphp

                <div class="bg-white rounded-2xl shadow border border-gray-200 overflow-hidden {{ $isThirdPartyMobile ? 'ring-1 ring-amber-300' : '' }}">
                    <div class="p-4">

                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="font-semibold text-gray-900 flex items-center gap-2">
                                    #{{ $trip->id }}
                                    @if($isThirdPartyMobile)
                                        <span class="px-1.5 py-0.5 rounded bg-amber-200 text-amber-900 text-[10px] font-semibold">3rd party</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ \Carbon\Carbon::parse($trip->start_date)->format('d.m.Y') }}
                                    →
                                    {{ \Carbon\Carbon::parse($trip->end_date)->format('d.m.Y') }}
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <div class="font-semibold text-gray-900">
                                    {{ number_format($freight, 2, '.', ' ') }}
                                    <span class="text-xs text-gray-500">{{ $cur }}</span>
                                </div>
                                <div class="text-xs text-gray-500">Kravas pārvadājums (Σ)</div>
                            </div>
                        </div>

                        <div class="mt-3 rounded-xl bg-gray-50 border border-gray-200 p-3">
                            @if($isThirdPartyMobile)
                                <div class="font-semibold text-gray-900">
                                    {{ $trip->carrierCompany?->name ?? '—' }}
                                </div>
                                <div class="text-sm text-gray-700 mt-1">
                                    {{ $trip->truck?->plate ?? '—' }}
                                    @if($trip->trailer?->plate)
                                        / {{ $trip->trailer->plate }}
                                    @endif
                                </div>
                            @else
                                <div class="font-semibold text-gray-900">
                                    🚚 {{ $trip->truck?->plate ?? '—' }}
                                    <span class="text-gray-500 font-normal">
                                        {{ $trip->truck?->brand ?? '' }} {{ $trip->truck?->model ?? '' }}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-700 mt-1">
                                    👤 {{ $trip->driver?->full_name ?? '—' }}
                                    @if($trip->driver?->pers_code)
                                        <span class="text-gray-400">• {{ $trip->driver->pers_code }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Money row --}}
                        <div class="mt-3 grid grid-cols-3 gap-2">
                            <div class="rounded-xl border border-gray-200 p-3">
                                <div class="text-xs text-gray-500 mb-1">Izdevumi (Σ)</div>
                                <div class="font-semibold text-gray-900">
                                    {{ number_format($expenses, 2, '.', ' ') }}
                                    <span class="text-xs text-gray-500">{{ $cur }}</span>
                                </div>
                            </div>

                            <div class="rounded-xl border border-gray-200 p-3">
                                <div class="text-xs text-gray-500 mb-1">Peļņa</div>
                                <div class="font-semibold {{ $profit < 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ number_format($profit, 2, '.', ' ') }}
                                    <span class="text-xs text-gray-500">{{ $cur }}</span>
                                </div>
                            </div>

                            <div class="rounded-xl border border-gray-200 p-3">
                                <div class="text-xs text-gray-500 mb-1">Marža</div>
                                @php
                                    $margin = $freight > 0 ? ($profit / $freight) * 100 : null;
                                @endphp
                                @if($margin !== null)
                                    <div class="font-semibold text-gray-900">
                                        {{ number_format($margin, 1, '.', ' ') }}%
                                    </div>
                                @else
                                    <div class="text-gray-400">—</div>
                                @endif
                            </div>
                        </div>

                        {{-- Odometer row --}}
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <div class="rounded-xl border border-gray-200 p-3">
                                <div class="text-xs text-gray-500 mb-1">Izbraukšana</div>
                                @if($trip->started_at)
                                    <div class="font-semibold text-gray-900">
                                        {{ $trip->started_at->format('d.m.Y H:i') }}
                                    </div>
                                @else
                                    <div class="text-gray-400 mb-0.5">—</div>
                                @endif
                                @if(!is_null($trip->departure_odometer))
                                    <div class="text-xs text-gray-600">
                                        {{ number_format((float)$trip->departure_odometer, 1, '.', ' ') }} km
                                    </div>
                                @elseif(!is_null($trip->odo_start_km))
                                    <div class="text-xs text-gray-600">
                                        {{ number_format((float)$trip->odo_start_km, 1, '.', ' ') }} km
                                    </div>
                                @endif
                            </div>

                            <div class="rounded-xl border border-gray-200 p-3">
                                <div class="text-xs text-gray-500 mb-1">Atgriešanās</div>
                                @if($trip->ended_at)
                                    <div class="font-semibold text-gray-900">
                                        {{ $trip->ended_at->format('d.m.Y H:i') }}
                                    </div>
                                @else
                                    <div class="text-gray-400 mb-0.5">—</div>
                                @endif
                                @if(!is_null($trip->return_odometer))
                                    <div class="text-xs text-gray-600">
                                        {{ number_format((float)$trip->return_odometer, 1, '.', ' ') }} km
                                    </div>
                                @elseif(!is_null($trip->odo_end_km))
                                    <div class="text-xs text-gray-600">
                                        {{ number_format((float)$trip->odo_end_km, 1, '.', ' ') }} km
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-3 flex gap-2">
                            <button wire:click="sortBy('departure_at')"
                                    class="flex-1 px-3 py-2 rounded-xl text-sm bg-gray-100 border border-gray-200">
                                ⇅ Izbraukšana
                            </button>
                            <button wire:click="sortBy('freight_total')"
                                    class="flex-1 px-3 py-2 rounded-xl text-sm bg-gray-100 border border-gray-200">
                                ⇅ Kravas pārvadājums
                            </button>
                        </div>

                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow border border-gray-200 p-8 text-center text-gray-500">
                    Nav datu
                </div>
            @endforelse

            <div class="pt-2">
                {{ $rows->links() }}
            </div>
        </div>

        {{-- DESKTOP: TABLE --}}
        <div class="hidden md:block">
            <div class="bg-white rounded-2xl shadow border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left whitespace-nowrap">
                                    <button wire:click="sortBy('id')" class="font-semibold hover:underline">
                                        # Reiss
                                    </button>
                                </th>

                                <th class="px-4 py-3 text-left whitespace-nowrap">Mašīna / Vadītājs</th>

                                <th class="px-4 py-3 text-left whitespace-nowrap">
                                    <button wire:click="sortBy('departure_at')" class="font-semibold hover:underline">
                                        Izbraukšana no garāžas
                                    </button>
                                </th>

                                <th class="px-4 py-3 text-left whitespace-nowrap">
                                    <button wire:click="sortBy('return_at')" class="font-semibold hover:underline">
                                        Atgriešanās garāžā
                                    </button>
                                </th>

                                <th class="px-4 py-3 text-right whitespace-nowrap">
                                    <button wire:click="sortBy('freight_total')" class="font-semibold hover:underline">
                                        Kravas pārvadājums (Σ)
                                    </button>
                                </th>

                                <th class="px-4 py-3 text-right whitespace-nowrap">
                                    <button wire:click="sortBy('expenses_total')" class="font-semibold hover:underline">
                                        Izdevumi (Σ)
                                    </button>
                                </th>

                                <th class="px-4 py-3 text-right whitespace-nowrap">
                                    <button wire:click="sortBy('profit')" class="font-semibold hover:underline">
                                        Peļņa / Zaudējumi
                                    </button>
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @forelse($rows as $trip)
                                @php
                                    $freight  = (float)($trip->freight_total ?? 0);
                                    $expenses = (float)($trip->expenses_total ?? 0);
                                    $profit   = (float)($trip->profit ?? ($freight - $expenses));
                                    $cur      = $trip->currency ?? '';
                                @endphp

                                @php
                                    $isThirdParty = $trip->carrierCompany?->is_third_party ?? false;
                                @endphp
                                <tr class="hover:bg-gray-50 {{ $isThirdParty ? 'bg-amber-50/70' : '' }}">
                                    <td class="px-4 py-3 whitespace-nowrap font-semibold text-gray-900">
                                        {{ $trip->id }}
                                        @if($isThirdParty)
                                            <span class="ml-1 px-1.5 py-0.5 rounded bg-amber-200 text-amber-900 text-[10px] font-semibold">3rd party</span>
                                        @endif
                                        <div class="text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($trip->start_date)->format('d.m.Y') }}
                                            →
                                            {{ \Carbon\Carbon::parse($trip->end_date)->format('d.m.Y') }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap text-gray-900">
                                        @if($isThirdParty)
                                            <div class="font-semibold">
                                                {{ $trip->carrierCompany?->name ?? '—' }}
                                            </div>
                                            <div class="text-xs text-gray-600">
                                                {{ $trip->truck?->plate ?? '—' }}
                                                @if($trip->trailer?->plate)
                                                    / {{ $trip->trailer->plate }}
                                                @endif
                                            </div>
                                        @else
                                            <div class="font-semibold">
                                                {{ $trip->truck?->plate ?? '—' }}
                                                <span class="text-gray-500 font-normal">
                                                    {{ $trip->truck?->brand ?? '' }} {{ $trip->truck?->model ?? '' }}
                                                </span>
                                            </div>
                                            <div class="text-xs text-gray-600">
                                                👤 {{ $trip->driver?->full_name ?? '—' }}
                                                @if($trip->driver?->pers_code)
                                                    <span class="text-gray-400">• {{ $trip->driver?->pers_code }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap text-gray-900">
                                        @if($trip->started_at)
                                            <div class="font-semibold">
                                                {{ $trip->started_at->format('d.m.Y H:i') }}
                                            </div>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                        @if(!is_null($trip->departure_odometer))
                                            <div class="text-xs text-gray-600">
                                                {{ number_format((float)$trip->departure_odometer, 1, '.', ' ') }} km
                                            </div>
                                        @elseif(!is_null($trip->odo_start_km))
                                            <div class="text-xs text-gray-600">
                                                {{ number_format((float)$trip->odo_start_km, 1, '.', ' ') }} km
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap text-gray-900">
                                        @if($trip->ended_at)
                                            <div class="font-semibold">
                                                {{ $trip->ended_at->format('d.m.Y H:i') }}
                                            </div>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                        @if(!is_null($trip->return_odometer))
                                            <div class="text-xs text-gray-600">
                                                {{ number_format((float)$trip->return_odometer, 1, '.', ' ') }} km
                                            </div>
                                        @elseif(!is_null($trip->odo_end_km))
                                            <div class="text-xs text-gray-600">
                                                {{ number_format((float)$trip->odo_end_km, 1, '.', ' ') }} km
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap text-right font-semibold text-gray-900">
                                        {{ number_format($freight, 2, '.', ' ') }}
                                        <span class="text-xs text-gray-500">{{ $cur }}</span>
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap text-right font-semibold text-gray-900">
                                        {{ number_format($expenses, 2, '.', ' ') }}
                                        <span class="text-xs text-gray-500">{{ $cur }}</span>
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap text-right font-semibold {{ $profit < 0 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ number_format($profit, 2, '.', ' ') }}
                                        <span class="text-xs text-gray-500">{{ $cur }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                        Nav datu
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3">
                    {{ $rows->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- =========================
         MOBILE: BOTTOM BAR + SHEET + SCROLL TOP
    ========================== --}}
    <div
        class="md:hidden"
        x-data="{ open:false, showTop:false }"
        x-init="window.addEventListener('scroll', () => showTop = window.scrollY > 300)"
        x-on:open-stats-filters.window="open = true"
        x-on:keydown.escape.window="open = false"
    >
        {{-- Bottom action bar --}}
        <div class="fixed bottom-0 left-0 right-0 z-40">
            <div class="max-w-7xl mx-auto px-4 pb-3">
                <div class="bg-white/95 border border-gray-200 shadow-lg rounded-2xl backdrop-blur px-3 py-3 flex items-center gap-2">
                    <button
                        x-on:click="open = true"
                        class="flex-1 px-3 py-3 rounded-xl bg-blue-600 text-white font-semibold active:scale-[0.99]
                               flex items-center justify-center gap-2"
                    >
                        <span>🔎 Filtri</span>
                        @if($this->activeFiltersCount > 0)
                            <span class="text-xs px-2 py-1 rounded-full bg-white/20 border border-white/20">
                                • {{ $this->activeFiltersCount }}
                            </span>
                        @endif
                    </button>

                    <button
                        wire:click="resetFilters"
                        class="px-4 py-3 rounded-xl bg-gray-100 border border-gray-200 text-gray-800 font-semibold"
                        title="Notīrīt"
                    >
                        ♻️
                    </button>
                </div>
            </div>
        </div>

        {{-- Scroll to top --}}
        <div class="fixed right-4 bottom-24 z-40">
            <button
                x-show="showTop"
                x-transition.opacity
                x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                class="w-12 h-12 rounded-2xl bg-white/95 border border-gray-200 shadow-lg
                       flex items-center justify-center text-lg active:scale-[0.98]"
                aria-label="Uz augšu"
                title="Uz augšu"
            >
                ↑
            </button>
        </div>

        {{-- Bottom sheet backdrop --}}
        <div
            x-show="open"
            x-transition.opacity
            class="fixed inset-0 z-50 bg-black/40"
            x-on:click="open = false"
        ></div>

        {{-- Bottom sheet --}}
        <div
            x-show="open"
            x-transition
            class="fixed inset-x-0 bottom-0 z-50"
        >
            <div class="max-w-7xl mx-auto px-4 pb-4">
                <div class="bg-white rounded-t-3xl shadow-2xl border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 flex items-center justify-between">
                        <div class="font-semibold text-gray-900">Filtri</div>
                        <button
                            class="px-3 py-2 rounded-xl bg-gray-100 border border-gray-200"
                            x-on:click="open = false"
                        >
                            ✕
                        </button>
                    </div>

                    <div class="px-4 pb-4 space-y-3">
                        <input
                            type="text"
                            wire:model.live.debounce.400ms="search"
                            placeholder="Meklēt: reisa ID, vadītājs, numurzīme..."
                            class="w-full px-3 py-3 rounded-xl border border-gray-300 bg-white text-gray-900
                                   focus:outline-none focus:ring-2 focus:ring-blue-200"
                        />

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <div class="text-xs text-gray-500 mb-1">Datums no</div>
                                <input
                                    type="date"
                                    wire:model.live="dateFrom"
                                    class="w-full px-3 py-3 rounded-xl border border-gray-300 bg-white text-gray-900
                                           focus:outline-none focus:ring-2 focus:ring-blue-200"
                                />
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 mb-1">Datums līdz</div>
                                <input
                                    type="date"
                                    wire:model.live="dateTo"
                                    class="w-full px-3 py-3 rounded-xl border border-gray-300 bg-white text-gray-900
                                           focus:outline-none focus:ring-2 focus:ring-blue-200"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-4 gap-2">
                            <button wire:click="quickRange(7)"  class="px-3 py-3 rounded-xl bg-gray-100 border border-gray-200">7d</button>
                            <button wire:click="quickRange(30)" class="px-3 py-3 rounded-xl bg-gray-100 border border-gray-200">30d</button>
                            <button wire:click="quickRange(90)" class="px-3 py-3 rounded-xl bg-gray-100 border border-gray-200">90d</button>
                            <button wire:click="clearDates"     class="px-3 py-3 rounded-xl bg-gray-100 border border-gray-200">∞</button>
                        </div>

                        <div class="flex gap-2 pt-1">
                            <button
                                wire:click="resetFilters"
                                class="flex-1 px-3 py-3 rounded-xl bg-gray-100 border border-gray-200 font-semibold"
                            >
                                Notīrīt
                            </button>

                            <button
                                class="flex-1 px-3 py-3 rounded-xl bg-blue-600 text-white font-semibold"
                                x-on:click="open = false"
                            >
                                Piemērot
                            </button>
                        </div>

                        <div class="text-xs text-gray-500 text-center pt-1">
                            Rindu skaits: {{ $rows->total() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ styles внутри root --}}
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    {{-- Chart.js: график по периодам --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
    (function() {
        window.statsChartInstance = null;
        function renderStatsChart() {
            if (window.statsChartInstance) { window.statsChartInstance.destroy(); window.statsChartInstance = null; }
            var dataEl = document.getElementById('stats-chart-data');
            var canvas = document.getElementById('stats-chart-canvas');
            if (!dataEl || !canvas || typeof Chart === 'undefined') return;
            var data = JSON.parse(dataEl.textContent);
            if (!data.labels || data.labels.length === 0) return;
            var ctx = canvas.getContext('2d');
            window.statsChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [
                        { label: data.label_freight || 'Frakts (EUR)', data: data.freight, backgroundColor: 'rgba(59, 130, 246, 0.7)', borderColor: 'rgb(59, 130, 246)', borderWidth: 1 },
                        { label: data.label_profit || 'Peļņa (EUR)', data: data.profit, backgroundColor: 'rgba(34, 197, 94, 0.7)', borderColor: 'rgb(34, 197, 94)', borderWidth: 1 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'top' } },
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: function(v) { return Number(v).toLocaleString('lv'); } } }
                    }
                }
            });
        }
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', renderStatsChart);
        else renderStatsChart();
        document.addEventListener('livewire:navigated', renderStatsChart);
        if (window.Livewire) Livewire.hook('morph.updated', function() { renderStatsChart(); });
    })();
    </script>
</div>
