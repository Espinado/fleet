<div class="min-h-screen bg-gray-100 pb-24">
    <div class="max-w-4xl mx-auto px-4 py-6">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-800">
                {{ __('app.maintenance_record.journal_title') }}
            </h1>
            <a href="{{ route('maintenance.records.create') }}" wire:navigate
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium min-h-[44px] inline-flex items-center">
                {{ __('app.maintenance_record.add_record') }}
            </a>
        </div>

        {{-- Блок с суммой по выборке --}}
        <div class="rounded-xl bg-white border border-gray-200 shadow-sm px-4 py-3 mb-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ __('app.maintenance_record.total_cost_filtered') }}</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">€{{ number_format($totalCost, 2, '.', ' ') }}</p>
        </div>

        {{-- Поиск, период, сортировка --}}
        <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-4 mb-4 space-y-3">
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('app.maintenance_record.search_placeholder') }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 min-h-[44px]">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-0.5">{{ __('app.maintenance_record.date_from') }}</label>
                    <input type="date" wire:model.live="dateFrom"
                           class="w-full rounded-lg border-gray-300 text-sm min-h-[44px]">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-0.5">{{ __('app.maintenance_record.date_to') }}</label>
                    <input type="date" wire:model.live="dateTo"
                           class="w-full rounded-lg border-gray-300 text-sm min-h-[44px]">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-0.5">{{ __('app.maintenance.sort') }}</label>
                    <select wire:model.live="sortField" class="w-full rounded-lg border-gray-300 text-sm min-h-[44px]">
                        <option value="performed_at">{{ __('app.maintenance_record.sort_by_date') }}</option>
                        <option value="vehicle_name">{{ __('app.maintenance_record.sort_by_vehicle') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-0.5">{{ __('app.maintenance.sort') }} ↑/↓</label>
                    <button type="button" wire:click="$set('sortDir', '{{ $sortDir === 'asc' ? 'desc' : 'asc' }}')"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 hover:bg-gray-100 text-sm min-h-[44px] px-3">
                        {{ $sortDir === 'asc' ? '↑ ' . __('app.maintenance.sort_asc') : '↓ ' . __('app.maintenance.sort_desc') }}
                    </button>
                </div>
            </div>
        </div>

        @if(session('message'))
            <p class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-4 py-2">
                {{ session('message') }}
            </p>
        @endif

        @if($records->isEmpty())
            <div class="rounded-xl bg-white border border-gray-200 p-8 text-center text-gray-500">
                <p class="text-base">{{ __('app.maintenance_record.no_records') }}</p>
                <a href="{{ route('maintenance.records.create') }}" wire:navigate class="mt-4 inline-block text-blue-600 hover:underline">
                    {{ __('app.maintenance_record.add_record') }} →
                </a>
            </div>
        @else
            <section class="rounded-xl bg-white border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[500px]">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium text-gray-700">{{ __('app.maintenance_record.vehicle') }}</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-700">{{ __('app.maintenance_record.performed_at') }}</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-700">{{ __('app.maintenance_record.odometer_km') }}</th>
                                <th class="px-4 py-2 text-right font-medium text-gray-700">{{ __('app.maintenance_record.cost') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records as $record)
                                <tr class="border-t border-gray-100 hover:bg-gray-50/50">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('maintenance.records.show', $record) }}" wire:navigate class="flex items-center gap-1.5 text-gray-900 font-medium hover:text-blue-600">
                                            <span>{{ $record->vehicle_name }}</span>
                                            <span class="text-xs {{ $record->vehicle_type === 'truck' ? 'text-blue-600' : 'text-amber-600' }}">{{ $record->vehicle_type === 'truck' ? '🚛' : '🚚' }}</span>
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 tabular-nums">{{ $record->performed_at->format('d.m.Y') }}</td>
                                    <td class="px-4 py-3 text-gray-600 tabular-nums">
                                        @if($record->odometer_km !== null)
                                            {{ number_format($record->odometer_km, 0, '.', ' ') }} km
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right tabular-nums font-medium">
                                        @if($record->cost !== null)
                                            €{{ number_format((float) $record->cost, 2, '.', ' ') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $records->links() }}
                </div>
            </section>
        @endif
    </div>
</div>
