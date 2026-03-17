<div class="min-h-screen bg-gray-100 pb-24">
    {{-- Header + period --}}
    <div class="sticky top-0 z-30 bg-white/95 border-b border-gray-200 backdrop-blur">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <h1 class="text-lg sm:text-2xl font-semibold text-gray-800 truncate">
                {{ __('app.owner_dashboard.title') }}
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ __('app.owner_dashboard.subtitle') }}
            </p>
        </div>
        <div class="max-w-7xl mx-auto px-4 pb-3 flex flex-wrap items-center gap-2">
            <span class="text-sm text-gray-600">{{ __('app.owner_dashboard.period') }}:</span>
            <button type="button" wire:click="setPeriodAll"
                    @class([
                        'px-3 py-1.5 rounded-xl text-sm font-medium transition',
                        !$dateFrom && !$dateTo
                            ? 'bg-amber-100 text-amber-800 ring-1 ring-amber-300'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                    ])>
                {{ __('app.owner_dashboard.period_all') }}
            </button>
            <button type="button" wire:click="setPeriodMonth"
                    @class([
                        'px-3 py-1.5 rounded-xl text-sm font-medium transition',
                        $dateFrom && $dateTo && $dateFrom === \Carbon\Carbon::now()->startOfMonth()->toDateString() && $dateTo === \Carbon\Carbon::now()->endOfMonth()->toDateString()
                            ? 'bg-amber-100 text-amber-800 ring-1 ring-amber-300'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                    ])>
                {{ __('app.owner_dashboard.period_month') }}
            </button>
            <button type="button" wire:click="setPeriodYear"
                    @class([
                        'px-3 py-1.5 rounded-xl text-sm font-medium transition',
                        $dateFrom && $dateTo && $dateFrom === \Carbon\Carbon::now()->startOfYear()->toDateString() && $dateTo === \Carbon\Carbon::now()->endOfYear()->toDateString()
                            ? 'bg-amber-100 text-amber-800 ring-1 ring-amber-300'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                    ])>
                {{ __('app.owner_dashboard.period_year') }}
            </button>
            @if($dateFrom || $dateTo)
                <span class="text-xs text-gray-500 ml-1">
                    {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d.m.Y') : '…' }}
                    → {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d.m.Y') : '…' }}
                </span>
            @endif
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-4 space-y-6">
        {{-- KPI: Revenue, Expenses, Profit, Trips --}}
        <section class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="rounded-xl bg-white border border-gray-200 p-4 shadow-sm">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ __('app.owner_dashboard.revenue') }}</p>
                <p class="mt-1 text-xl font-bold text-gray-900">
                    €{{ number_format($kpi->revenue, 2, '.', ' ') }}
                </p>
            </div>
            <div class="rounded-xl bg-white border border-gray-200 p-4 shadow-sm">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ __('app.owner_dashboard.expenses') }}</p>
                <p class="mt-1 text-xl font-bold text-gray-900">
                    €{{ number_format($kpi->expenses, 2, '.', ' ') }}
                </p>
                @if(isset($kpi->maintenance_costs) && $kpi->maintenance_costs > 0)
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('app.owner_dashboard.incl_maintenance') }} €{{ number_format($kpi->maintenance_costs, 2, '.', ' ') }}</p>
                @endif
            </div>
            <div class="rounded-xl bg-white border border-gray-200 p-4 shadow-sm">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ __('app.owner_dashboard.profit') }}</p>
                <p class="mt-1 text-xl font-bold {{ $kpi->profit >= 0 ? 'text-green-700' : 'text-red-700' }}">
                    €{{ number_format($kpi->profit, 2, '.', ' ') }}
                </p>
            </div>
            <div class="rounded-xl bg-white border border-gray-200 p-4 shadow-sm">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ __('app.owner_dashboard.trips_count') }}</p>
                <p class="mt-1 text-xl font-bold text-gray-900">{{ number_format($kpi->trips_count, 0, '.', ' ') }}</p>
            </div>
        </section>

        {{-- Receivables + Downtime + Expiring --}}
        <section class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
            <div class="rounded-xl bg-white border border-gray-200 p-4 shadow-sm">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ __('app.inv.receivables_title') }}</p>
                <p class="mt-1 text-lg font-bold text-gray-900">
                    €{{ number_format($receivables->total_receivables, 2, '.', ' ') }}
                </p>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ $receivables->invoices_with_balance_count }} {{ __('app.owner_dashboard.invoices_with_balance') }}
                    @if($receivables->overdue_count > 0)
                        · <span class="text-amber-600 font-medium">{{ $receivables->overdue_count }} {{ __('app.owner_dashboard.overdue') }}</span>
                    @endif
                </p>
                <a href="{{ route('invoices.index') }}" wire:navigate class="mt-2 inline-block text-sm text-blue-600 hover:underline">
                    {{ __('app.owner_dashboard.view_invoices') }} →
                </a>
            </div>
            <div class="rounded-xl bg-white border border-gray-200 p-4 shadow-sm">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ __('app.owner_dashboard.downtime') }}</p>
                <p class="mt-1 text-lg font-bold text-gray-900">
                    €{{ number_format($downtime->total_amount, 2, '.', ' ') }}
                </p>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ $downtime->cargos_count }} {{ __('app.owner_dashboard.downtime_cargos') }}
                </p>
                <a href="{{ route('stats.downtime') }}" wire:navigate class="mt-2 inline-block text-sm text-blue-600 hover:underline">
                    {{ __('app.nav.stats_downtime') }} →
                </a>
            </div>
            <div class="rounded-xl bg-white border border-gray-200 p-4 shadow-sm">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ __('app.owner_dashboard.expiring_docs') }}</p>
                <p class="mt-1 text-lg font-bold {{ $expiringCount > 0 ? 'text-amber-700' : 'text-gray-900' }}">
                    {{ $expiringCount }}
                </p>
                <p class="text-sm text-gray-500 mt-0.5">{{ __('app.owner_dashboard.expiring_next_30') }}</p>
                <a href="{{ route('dashboard') }}" wire:navigate class="mt-2 inline-block text-sm text-blue-600 hover:underline">
                    {{ __('app.nav.dashboard') }} →
                </a>
            </div>
        </section>

        {{-- Истекающие документы (короткий блок) --}}
        <section class="rounded-xl bg-white border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-base font-semibold text-gray-800">{{ __('app.maintenance.expiring_documents') }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('app.maintenance.expiring_documents_hint') }}</p>
                </div>
                <a href="{{ route('maintenance.index') }}" wire:navigate class="text-sm text-blue-600 hover:underline min-h-[44px] inline-flex items-center touch-manipulation shrink-0">
                    {{ __('app.owner_dashboard.view_all') }} →
                </a>
            </div>
            <div class="p-3">
                <input type="search" wire:model.live.debounce.300ms="expiringSearch"
                       placeholder="{{ __('app.maintenance.search_placeholder') }}"
                       class="w-full rounded-lg border-gray-300 text-sm min-h-[44px] mb-2">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xs text-gray-500">{{ __('app.maintenance.sort') }}:</span>
                    <select wire:model.live="expiringSort" class="rounded-lg border-gray-300 text-sm py-1.5 pr-6">
                        <option value="expires_at">{{ __('app.maintenance.sort_by_date') }}</option>
                        <option value="entity_name">{{ __('app.maintenance.sort_by_name') }}</option>
                    </select>
                    <button type="button" wire:click="$set('expiringDir', '{{ $expiringDir === 'asc' ? 'desc' : 'asc' }}')"
                            class="p-1.5 rounded border border-gray-300 bg-gray-50 hover:bg-gray-100 text-sm">
                        {{ $expiringDir === 'asc' ? '↑' : '↓' }}
                    </button>
                </div>
                @if($expiringDocuments->isEmpty())
                    <p class="py-4 text-center text-gray-500 text-sm">{{ __('app.maintenance.no_expiring_docs') }}</p>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach($expiringDocuments as $item)
                            <li class="py-1.5 first:pt-0">
                                <a href="{{ $item->entity_type === 'truck' ? route('trucks.show', $item->entity_id) : ($item->entity_type === 'trailer' ? route('trailers.show', $item->entity_id) : route('drivers.show', $item->entity_id)) }}" wire:navigate
                                   class="grid grid-cols-[1fr_auto] gap-2 items-center rounded-lg px-2 py-1.5 hover:bg-gray-50 active:bg-gray-100 touch-manipulation min-h-[44px] text-left">
                                    <span class="font-medium text-gray-900 truncate min-w-0">{{ $item->entity_name }}</span>
                                    <span class="text-sm text-gray-500 tabular-nums whitespace-nowrap">{{ __('app.' . $item->doc_label_key) }} · {{ $item->expires_at->format('d.m.Y') }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="flex flex-wrap items-center justify-between gap-2 pt-2 mt-2 border-t border-gray-100">
                        <span class="text-xs text-gray-500">{{ $expiringDocuments->firstItem() }}–{{ $expiringDocuments->lastItem() }} / {{ $expiringDocuments->total() }}</span>
                        <div class="flex gap-1">
                            @if($expiringDocuments->currentPage() > 1)
                                <button type="button" wire:click="setExpiringPage({{ $expiringDocuments->currentPage() - 1 }})" class="px-2 py-1.5 rounded bg-gray-100 hover:bg-gray-200 text-xs min-h-[44px] touch-manipulation">{{ __('app.pagination.previous') }}</button>
                            @endif
                            @if($expiringDocuments->currentPage() < $expiringDocuments->lastPage())
                                <button type="button" wire:click="setExpiringPage({{ $expiringDocuments->currentPage() + 1 }})" class="px-2 py-1.5 rounded bg-gray-100 hover:bg-gray-200 text-xs min-h-[44px] touch-manipulation">{{ __('app.pagination.next') }}</button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </section>

        {{-- Предстоящее ТО (короткий блок) --}}
        <section class="rounded-xl bg-white border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-base font-semibold text-gray-800">{{ __('app.owner_dashboard.upcoming_maintenance') }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('app.owner_dashboard.upcoming_maintenance_hint') }}</p>
                </div>
                <a href="{{ route('maintenance.index') }}" wire:navigate class="text-sm text-blue-600 hover:underline min-h-[44px] inline-flex items-center touch-manipulation shrink-0">
                    {{ __('app.owner_dashboard.view_all') }} →
                </a>
            </div>
            <div class="p-3">
                <input type="search" wire:model.live.debounce.300ms="upcomingSearch"
                       placeholder="{{ __('app.maintenance.search_placeholder') }}"
                       class="w-full rounded-lg border-gray-300 text-sm min-h-[44px] mb-2">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xs text-gray-500">{{ __('app.maintenance.sort') }}:</span>
                    <select wire:model.live="upcomingSort" class="rounded-lg border-gray-300 text-sm py-1.5 pr-6">
                        <option value="expires_at">{{ __('app.maintenance.sort_by_date') }}</option>
                        <option value="name">{{ __('app.maintenance.sort_by_name') }}</option>
                    </select>
                    <button type="button" wire:click="$set('upcomingDir', '{{ $upcomingDir === 'asc' ? 'desc' : 'asc' }}')"
                            class="p-1.5 rounded border border-gray-300 bg-gray-50 hover:bg-gray-100 text-sm">
                        {{ $upcomingDir === 'asc' ? '↑' : '↓' }}
                    </button>
                </div>
                @if($upcomingMaintenance->isEmpty())
                    <p class="py-4 text-center text-gray-500 text-sm">{{ __('app.maintenance.no_upcoming') }}</p>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach($upcomingMaintenance as $item)
                            <li class="py-1.5 first:pt-0">
                                <a href="{{ $item->type === 'truck' ? route('trucks.show', $item->id) : route('trailers.show', $item->id) }}" wire:navigate
                                   class="grid grid-cols-[1fr_auto] gap-2 items-center rounded-lg px-2 py-1.5 hover:bg-gray-50 active:bg-gray-100 touch-manipulation min-h-[44px] text-left">
                                    <span class="font-medium text-gray-900 truncate min-w-0">{{ $item->name }}</span>
                                    <span class="text-sm text-gray-500 flex flex-wrap gap-1.5 tabular-nums">
                                        @if($item->due_by_km && $item->next_service_km)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-blue-100 text-blue-800">{{ __('app.maintenance.due_by_km') }}: {{ number_format($item->next_service_km, 0, '.', ' ') }} km</span>
                                        @endif
                                        @if($item->due_by_date && $item->next_service_date)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-100 text-amber-800">{{ __('app.maintenance.due_by_date') }}: {{ \Carbon\Carbon::parse($item->next_service_date)->format('d.m.Y') }}</span>
                                        @endif
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="flex flex-wrap items-center justify-between gap-2 pt-2 mt-2 border-t border-gray-100">
                        <span class="text-xs text-gray-500">{{ $upcomingMaintenance->firstItem() }}–{{ $upcomingMaintenance->lastItem() }} / {{ $upcomingMaintenance->total() }}</span>
                        <div class="flex gap-1">
                            @if($upcomingMaintenance->currentPage() > 1)
                                <button type="button" wire:click="setUpcomingPage({{ $upcomingMaintenance->currentPage() - 1 }})" class="px-2 py-1.5 rounded bg-gray-100 hover:bg-gray-200 text-xs min-h-[44px] touch-manipulation">{{ __('app.pagination.previous') }}</button>
                            @endif
                            @if($upcomingMaintenance->currentPage() < $upcomingMaintenance->lastPage())
                                <button type="button" wire:click="setUpcomingPage({{ $upcomingMaintenance->currentPage() + 1 }})" class="px-2 py-1.5 rounded bg-gray-100 hover:bg-gray-200 text-xs min-h-[44px] touch-manipulation">{{ __('app.pagination.next') }}</button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </section>

        {{-- Top clients (tabs: by revenue / by trips) --}}
        <section class="rounded-xl bg-white border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-base font-semibold text-gray-800">{{ __('app.owner_dashboard.top_clients') }}</h2>
                <div class="flex rounded-lg bg-gray-100 p-0.5" role="tablist">
                    <button type="button" wire:click="$set('topClientsMode', 'revenue')"
                            @class([
                                'px-3 py-1.5 rounded-md text-sm font-medium transition',
                                $topClientsMode === 'revenue' ? 'bg-white text-gray-900 shadow' : 'text-gray-600 hover:text-gray-900'
                            ])>
                        {{ __('app.owner_dashboard.by_revenue') }}
                    </button>
                    <button type="button" wire:click="$set('topClientsMode', 'trips')"
                            @class([
                                'px-3 py-1.5 rounded-md text-sm font-medium transition',
                                $topClientsMode === 'trips' ? 'bg-white text-gray-900 shadow' : 'text-gray-600 hover:text-gray-900'
                            ])>
                        {{ __('app.owner_dashboard.by_trips') }}
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left">
                        <tr>
                            <th class="px-4 py-2 font-medium text-gray-700">{{ __('app.owner_dashboard.client') }}</th>
                            <th class="px-4 py-2 font-medium text-gray-700 text-right">{{ __('app.owner_dashboard.revenue') }}</th>
                            <th class="px-4 py-2 font-medium text-gray-700 text-right">{{ __('app.owner_dashboard.trips') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topClients as $row)
                            <tr class="border-t border-gray-100 hover:bg-gray-50/50">
                                <td class="px-4 py-2 font-medium text-gray-900">{{ $row->client_name }}</td>
                                <td class="px-4 py-2 text-right text-gray-700">€{{ number_format($row->total_revenue ?? 0, 2, '.', ' ') }}</td>
                                <td class="px-4 py-2 text-right text-gray-700">{{ number_format($row->trips_count ?? 0, 0, '.', ' ') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">{{ __('app.owner_dashboard.no_data') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-2 border-t border-gray-100 text-right">
                <a href="{{ route('stats.clients') }}" wire:navigate class="text-sm text-blue-600 hover:underline">{{ __('app.owner_dashboard.all_clients') }} →</a>
            </div>
        </section>

        {{-- Top trucks (tabs: by revenue / by trips) --}}
        <section class="rounded-xl bg-white border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-base font-semibold text-gray-800">{{ __('app.owner_dashboard.top_trucks') }}</h2>
                <div class="flex rounded-lg bg-gray-100 p-0.5" role="tablist">
                    <button type="button" wire:click="$set('topTrucksMode', 'revenue')"
                            @class([
                                'px-3 py-1.5 rounded-md text-sm font-medium transition',
                                $topTrucksMode === 'revenue' ? 'bg-white text-gray-900 shadow' : 'text-gray-600 hover:text-gray-900'
                            ])>
                        {{ __('app.owner_dashboard.by_revenue') }}
                    </button>
                    <button type="button" wire:click="$set('topTrucksMode', 'trips')"
                            @class([
                                'px-3 py-1.5 rounded-md text-sm font-medium transition',
                                $topTrucksMode === 'trips' ? 'bg-white text-gray-900 shadow' : 'text-gray-600 hover:text-gray-900'
                            ])>
                        {{ __('app.owner_dashboard.by_trips') }}
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left">
                        <tr>
                            <th class="px-4 py-2 font-medium text-gray-700">{{ __('app.owner_dashboard.truck') }}</th>
                            <th class="px-4 py-2 font-medium text-gray-700 text-right">{{ __('app.owner_dashboard.revenue') }}</th>
                            <th class="px-4 py-2 font-medium text-gray-700 text-right">{{ __('app.owner_dashboard.trips') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topTrucks as $row)
                            <tr class="border-t border-gray-100 hover:bg-gray-50/50">
                                <td class="px-4 py-2 font-medium text-gray-900">{{ $row->truck_name }}</td>
                                <td class="px-4 py-2 text-right text-gray-700">€{{ number_format($row->total_revenue ?? 0, 2, '.', ' ') }}</td>
                                <td class="px-4 py-2 text-right text-gray-700">{{ number_format($row->trips_count ?? 0, 0, '.', ' ') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">{{ __('app.owner_dashboard.no_data') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-2 border-t border-gray-100 text-right">
                <a href="{{ route('trucks.index') }}" wire:navigate class="text-sm text-blue-600 hover:underline">{{ __('app.owner_dashboard.all_trucks') }} →</a>
            </div>
        </section>
    </div>
</div>
