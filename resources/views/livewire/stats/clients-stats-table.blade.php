<div class="min-h-screen bg-gray-100 dark:bg-gray-900 pb-28">
    <div class="max-w-6xl mx-auto px-4 py-6 space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">🏢 {{ __('app.stats.clients.title') }}</h1>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.stats.clients.subtitle') }}</div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">{{ __('app.stats.clients.search') }}</label>
                    <div class="relative">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="{{ __('app.stats.clients.search_placeholder') }}"
                            class="w-full px-3 py-2.5 pr-9 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-blue-500"
                        >
                        @if(strlen($search) > 0)
                            <button type="button"
                                    wire:click="clearSearch"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 p-1 rounded-full text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 dark:hover:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                                    title="{{ __('app.stats.clients.search_clear') }}"
                                    aria-label="{{ __('app.stats.clients.search_clear') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        @endif
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">{{ __('app.stats.date_from') }}</label>
                    <input type="date" wire:model.live="dateFrom"
                           class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">{{ __('app.stats.date_to') }}</label>
                    <input type="date" wire:model.live="dateTo"
                           class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm">
                </div>
            </div>
            <div class="mt-3 flex flex-wrap gap-2">
                <button wire:click="quickRange(7)" type="button"
                        class="px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600">
                    7 {{ __('app.stats.clients.days') }}
                </button>
                <button wire:click="quickRange(30)" type="button"
                        class="px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600">
                    30 {{ __('app.stats.clients.days') }}
                </button>
                <button wire:click="quickRange(90)" type="button"
                        class="px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600">
                    90 {{ __('app.stats.clients.days') }}
                </button>
                <button wire:click="clearDates" type="button"
                        class="px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600"
                        title="{{ __('app.stats.clients.all_time') }}">
                    ∞
                </button>
            </div>
        </div>

        {{-- Summary --}}
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('app.stats.clients.total_trips') }}</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ number_format($summary['total_trips'], 0, ',', ' ') }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('app.stats.clients.total_freight') }}</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ number_format($summary['total_freight'], 2, ',', ' ') }} EUR</div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <button type="button" wire:click="sortBy('client_name')" class="font-semibold text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400">
                                    {{ __('app.stats.clients.col_client') }}
                                    @if($sortField === 'client_name') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-right">
                                <button type="button" wire:click="sortBy('cargos_count')" class="font-semibold text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400">
                                    {{ __('app.stats.clients.col_cargos') }}
                                    @if($sortField === 'cargos_count') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-right">
                                <button type="button" wire:click="sortBy('trips_count')" class="font-semibold text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400">
                                    {{ __('app.stats.clients.col_trips') }}
                                    @if($sortField === 'trips_count') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-right">
                                <button type="button" wire:click="sortBy('freight_total')" class="font-semibold text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400">
                                    {{ __('app.stats.clients.col_freight') }}
                                    @if($sortField === 'freight_total') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($rows as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                <td class="px-4 py-3">
                                    @if($row->client_id)
                                        <a href="{{ route('clients.show', $row->client_id) }}" wire:navigate
                                           class="font-medium text-blue-600 dark:text-blue-400 hover:underline truncate max-w-xs block">
                                            {{ $row->client_name }}
                                        </a>
                                    @else
                                        <span class="text-gray-500 dark:text-gray-400">{{ $row->client_name }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ number_format($row->cargos_count, 0, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ number_format($row->trips_count, 0, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right tabular-nums font-medium">{{ number_format((float)$row->freight_total, 2, ',', ' ') }} EUR</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    {{ __('app.stats.clients.no_data') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
