<div class="min-h-screen bg-gray-100 pb-24">

    {{-- HEADER --}}
    <div class="sticky top-0 z-20 bg-white/90 border-b border-gray-200 backdrop-blur">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
            <h1 class="text-lg sm:text-2xl font-semibold text-gray-800 truncate">
                📊 Stats
            </h1>
        </div>

        {{-- FILTERS --}}
        <div class="max-w-7xl mx-auto px-4 pb-3 grid grid-cols-1 sm:grid-cols-4 gap-2">
            <input
                type="text"
                wire:model.live.debounce.400ms="search"
                placeholder="Поиск: trip id, водитель, plate..."
                class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white text-gray-900"
            />

            <input
                type="date"
                wire:model.live="dateFrom"
                class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white text-gray-900"
            />

            <input
                type="date"
                wire:model.live="dateTo"
                class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white text-gray-900"
            />

            <div class="text-sm text-gray-600 flex items-center justify-start sm:justify-end">
                <span class="px-3 py-2 rounded-xl bg-gray-200/60">
                    Rows: {{ $rows->total() }}
                </span>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 pt-4">
        <div class="bg-white rounded-2xl shadow border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left whitespace-nowrap">
                                <button wire:click="sortBy('id')" class="font-semibold hover:underline">
                                    # Trip
                                </button>
                            </th>

                            <th class="px-4 py-3 text-left whitespace-nowrap">
                                Truck / Driver
                            </th>

                            <th class="px-4 py-3 text-left whitespace-nowrap">
                                <button wire:click="sortBy('departure_at')" class="font-semibold hover:underline">
                                    Выезд из гаража
                                </button>
                            </th>

                            <th class="px-4 py-3 text-left whitespace-nowrap">
                                <button wire:click="sortBy('return_at')" class="font-semibold hover:underline">
                                    Возврат в гараж
                                </button>
                            </th>

                            <th class="px-4 py-3 text-right whitespace-nowrap">
                                <button wire:click="sortBy('freight_total')" class="font-semibold hover:underline">
                                    Фрахт (Σ)
                                </button>
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        @forelse($rows as $trip)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap font-semibold text-gray-900">
                                    {{ $trip->id }}
                                    <div class="text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($trip->start_date)->format('d.m.Y') }}
                                        →
                                        {{ \Carbon\Carbon::parse($trip->end_date)->format('d.m.Y') }}
                                    </div>
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap text-gray-900">
                                    <div class="font-semibold">
                                        {{ $trip->truck?->plate ?? '—' }}
                                        <span class="text-gray-500 font-normal">
                                            {{ $trip->truck?->brand ?? '' }} {{ $trip->truck?->model ?? '' }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-600">
                                        👤 {{ $trip->driver?->full_name ?? '—' }}
                                        @if($trip->driver?->pers_code)
                                            <span class="text-gray-400">• {{ $trip->driver->pers_code }}</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap text-gray-900">
                                    @if($trip->departure_at)
                                        <div class="font-semibold">
                                            {{ \Carbon\Carbon::parse($trip->departure_at)->format('d.m.Y H:i') }}
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            {{ number_format((float)$trip->departure_odometer, 1, '.', ' ') }} km
                                        </div>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap text-gray-900">
                                    @if($trip->return_at)
                                        <div class="font-semibold">
                                            {{ \Carbon\Carbon::parse($trip->return_at)->format('d.m.Y H:i') }}
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            {{ number_format((float)$trip->return_odometer, 1, '.', ' ') }} km
                                        </div>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap text-right font-semibold text-gray-900">
                                    {{ number_format((float)($trip->freight_total ?? 0), 2, '.', ' ') }}
                                    <span class="text-xs text-gray-500">
                                        {{ $trip->currency ?? '' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                    Нет данных
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
