<div class="p-4 sm:p-6 max-w-7xl mx-auto">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-4">
        <div class="flex items-center gap-2 w-full md:w-auto">
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="{{ __('app.carriers.search') }}"
                   class="flex-1 border rounded-lg px-3 py-2 text-sm shadow-sm focus:ring focus:ring-amber-100" />
            @if($search)
                <button wire:click="$set('search','')" class="px-2 py-1 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-600 text-sm">✖</button>
            @endif
        </div>
        <div class="flex items-center justify-end gap-3 w-full md:w-auto">
            <a href="{{ route('carriers.create') }}"
               class="inline-flex items-center gap-1 bg-amber-600 text-white text-sm font-medium px-3 py-1.5 rounded-lg shadow hover:bg-amber-700 transition">
                ➕ {{ __('app.carriers.add') }}
            </a>
            <select wire:model.live="perPage" class="border rounded-lg px-2 py-1 text-sm">
                <option value="10">10</option>
                <option value="15">15</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left">
                    <tr>
                        <th class="px-4 py-2 font-medium text-gray-700 cursor-pointer" wire:click="sortBy('name')">{{ __('app.carriers.name') }}</th>
                        <th class="px-4 py-2 font-medium text-gray-700 cursor-pointer hidden sm:table-cell" wire:click="sortBy('country')">{{ __('app.carriers.country') }}</th>
                        <th class="px-4 py-2 font-medium text-gray-700 hidden md:table-cell">{{ __('app.carriers.contact') }}</th>
                        {{-- Рейтинг пока скрыт --}}
                        {{-- <th class="px-4 py-2 font-medium text-gray-700 cursor-pointer" wire:click="sortBy('rating')">{{ __('app.carriers.rating') }}</th> --}}
                        <th class="px-4 py-2 font-medium text-gray-700 cursor-pointer" wire:click="sortBy('trips_count')">{{ __('app.carriers.trips_count') }}</th>
                        <th class="px-4 py-2 w-24"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($carriers as $c)
                        <tr class="border-t border-gray-100 hover:bg-gray-50/50">
                            <td class="px-4 py-2 font-medium">
                                <a href="{{ route('carriers.show', $c) }}" wire:navigate class="text-amber-700 hover:underline">{{ $c->name }}</a>
                            </td>
                            <td class="px-4 py-2 text-gray-600 hidden sm:table-cell">{{ $c->country ?? '—' }}</td>
                            <td class="px-4 py-2 text-gray-600 hidden md:table-cell">
                                @if($c->contact_person){{ $c->contact_person }}<br>@endif
                                @if($c->phone)<small>{{ $c->phone }}</small>@endif
                                @if($c->email)<br><small>{{ $c->email }}</small>@endif
                                @if(!$c->contact_person && !$c->phone && !$c->email)—@endif
                            </td>
                            {{-- Рейтинг пока скрыт --}}
                            {{-- <td class="px-4 py-2">
                                @if($c->rating)
                                    <span class="text-amber-600 font-medium">{{ $c->rating }}/5</span>
                                @else
                                    —
                                @endif
                            </td> --}}
                            <td class="px-4 py-2">{{ $c->trips_count }}</td>
                            <td class="px-4 py-2">
                                <a href="{{ route('carriers.edit', $c) }}" wire:navigate class="text-xs text-blue-600 hover:underline">{{ __('app.carriers.edit') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">{{ __('app.carriers.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-2 border-t border-gray-100">
            {{ $carriers->links() }}
        </div>
    </div>
</div>
