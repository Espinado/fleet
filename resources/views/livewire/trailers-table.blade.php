<div class="p-4 sm:p-6 max-w-7xl mx-auto">

    {{-- 🔝 Augšējā josla (adaptīva) --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-4">

        {{-- 🔍 Meklēšana --}}
        <div class="flex items-center gap-2 w-full md:w-auto">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('app.trailers.search') }}"
                class="flex-1 border rounded-lg px-3 py-2 text-sm shadow-sm focus:ring focus:ring-blue-100"
            />
            @if ($search)
                <button wire:click="$set('search','')" class="px-2 py-1 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-600 text-sm">
                    ✖
                </button>
            @endif
        </div>

            {{-- ➕ Pievienot + Kārtot (mobilais) + Rindas (dators) --}}
        <div class="flex items-center justify-end gap-3 w-full md:w-auto">

            {{-- ➕ Poga Pievienot --}}
            <a href="{{ route('trailers.create') }}"
               class="inline-flex items-center gap-1 bg-green-600 text-white text-sm font-medium px-3 py-1.5 rounded-lg shadow hover:bg-green-700 transition">
                ➕ {{ __('app.trailers.add') }}
            </a>

            {{-- 🔽 Poga kārtošanai (tikai mobilajiem) --}}
            <div x-data="{ open: false }" class="relative block md:hidden">
                <button @click="open = !open"
                        class="px-3 py-2 text-sm border rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 flex items-center gap-1">
                    ⬇️ {{ __('app.trailers.sort') }}
                    @if ($sortDirection === 'asc')
                        <span class="text-xs text-gray-500">▲</span>
                    @else
                        <span class="text-xs text-gray-500">▼</span>
                    @endif
                </button>

                {{-- Kārtošanas izvēlne --}}
                <div x-show="open" @click.away="open = false"
                     class="absolute left-0 mt-1 w-48 bg-white border rounded-lg shadow-lg z-50 text-sm overflow-hidden">
                    @foreach ([
                        'brand' => __('app.trailers.col_brand'),
                        'plate' => __('app.trailers.col_plate'),
                        'inspection_expired' => __('app.trailers.col_inspection'),
                        'insurance_expired' => __('app.trailers.col_inssurance'),
                        'status' => __('app.trailers.col_status'),
                        'is_active' => __('app.trailers.col_active'),
                        'company' => __('app.trailers.col_company'),
                    ] as $field => $label)
                        <button wire:click="sortBy('{{ $field }}')" @click="open = false"
                                class="block w-full text-left px-3 py-2 hover:bg-gray-100 {{ $sortField === $field ? 'bg-blue-50 text-blue-600 font-medium' : '' }}">
                            {{ $label }}
                            @if ($sortField === $field)
                                @if ($sortDirection === 'asc')
                                    ▲
                                @else
                                    ▼
                                @endif
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- 📄 Rindu skaits (tikai datoriem) --}}
            <div class="hidden md:flex items-center gap-2">
                <label for="perPage" class="text-sm text-gray-600">{{ __('app.trailers.rows') }}</label>
                <select id="perPage" wire:model.live="perPage"
                        class="border rounded-lg px-2 py-1 text-sm shadow-sm focus:ring focus:ring-blue-100">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
    </div>

    {{-- 💻 Tabulas versija --}}
    <div class="hidden md:block bg-white rounded-lg shadow">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('brand')">
                        {{ __('app.trailers.col_brand') }}
                        @if ($sortField === 'brand')
                            {!! $sortDirection === 'asc' ? '▲' : '▼' !!}
                        @endif
                    </th>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('plate')">
                        {{ __('app.trailers.col_plate') }}
                        @if ($sortField === 'plate')
                            {!! $sortDirection === 'asc' ? '▲' : '▼' !!}
                        @endif
                    </th>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('inspection_expired')">
                        {{ __('app.trailers.col_inspection') }}
                        @if ($sortField === 'inspection_expired')
                            {!! $sortDirection === 'asc' ? '▲' : '▼' !!}
                        @endif
                    </th>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('insurance_expired')">
                        {{ __('app.trailers.col_inssurance') }}
                        @if ($sortField === 'insurance_expired')
                            {!! $sortDirection === 'asc' ? '▲' : '▼' !!}
                        @endif
                    </th>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('status')">
                        {{ __('app.trailers.col_status') }}
                        @if ($sortField === 'status')
                            {!! $sortDirection === 'asc' ? '▲' : '▼' !!}
                        @endif
                    </th>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('is_active')">
                        {{ __('app.trailers.col_active') }}
                        @if ($sortField === 'is_active')
                            {!! $sortDirection === 'asc' ? '▲' : '▼' !!}
                        @endif
                    </th>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('company')">
                        {{ __('app.trailers.col_company') }}
                        @if ($sortField === 'company')
                            {!! $sortDirection === 'asc' ? '▲' : '▼' !!}
                        @endif
                    </th>
                    <th class="p-3 text-left">{{ __('app.trailers.col_action') }}</th>
                </tr>
            </thead>
            <tbody wire:loading.class="opacity-50">
                @forelse($items as $trailer)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $trailer->brand }} {{ $trailer->model }}</td>
                        <td class="px-4 py-2">{{ $trailer->plate }}</td>
                        <td class="px-4 py-2">
                            {{ $trailer->inspection_expired ? $trailer->inspection_expired->format('d-m-Y') : '—' }}
                        </td>
                        <td class="px-4 py-2">
                            {{ $trailer->insurance_expired ? $trailer->insurance_expired->format('d-m-Y') : '—' }}
                        </td>
                        <td class="px-4 py-2">
                            @if ($trailer->status == 1)
                                ✅ {{ __('app.trailers.status_active') }}
                            @else
                                ❌ {{ __('app.trailers.status_inactive') }}
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            {{ $trailer->is_active ? '🟢 ' . __('app.trailers.active_yes') : '⚪ ' . __('app.trailers.active_no') }}
                        </td>
                        <td class="px-4 py-2">{{ $trailer->company_name }}</td>
                        <td class="p-3 border text-center">
                            <a href="{{ route('trailers.show', $trailer->id) }}" class="text-blue-600">👁️</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-gray-500">{{ __('app.trailers.no_results') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 📱 Mobilā / PWA versija --}}
    <div class="block md:hidden mt-3 space-y-3">
        @forelse($items as $trailer)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col gap-2">
                <div class="flex justify-between items-start">
                    <h3 class="text-lg font-semibold text-gray-800">
                        {{ $trailer->brand }} {{ $trailer->model }}
                    </h3>
                    <a href="{{ route('trailers.show', $trailer->id) }}"
                       class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1">
                        👁️
                    </a>
                </div>

                <div class="text-sm text-gray-700 grid grid-cols-2 gap-y-1">
                    <div><b>{{ __('app.trailers.col_plate') }}:</b> {{ $trailer->plate }}</div>
                    <div>
                        <b>{{ __('app.trailers.col_status') }}:</b>
                        @if ($trailer->status == 1)
                            <span class="text-green-600 font-medium">{{ __('app.trailers.status_active') }}</span>
                        @else
                            <span class="text-red-500 font-medium">{{ __('app.trailers.status_inactive') }}</span>
                        @endif
                    </div>
                    <div><b>{{ __('app.trailers.col_inspection') }}:</b> {{ $trailer->inspection_expired ? $trailer->inspection_expired->format('d-m-Y') : '—' }}</div>
                    <div><b>{{ __('app.trailers.col_inssurance') }}:</b> {{ $trailer->insurance_expired ? $trailer->insurance_expired->format('d-m-Y') : '—' }}</div>
                    <div><b>{{ __('app.trailers.col_active') }}:</b> {{ $trailer->is_active ? '🟢 ' . __('app.trailers.active_yes') : '⚪ ' . __('app.trailers.active_no') }}</div>
                    <div class="col-span-2"><b>{{ __('app.trailers.col_company') }}:</b> {{ $trailer->company_name ?? '—' }}</div>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 py-10">
                🚛 {{ __('app.trailers.no_trailers') }}
            </div>
        @endforelse
    </div>

    {{-- 🔄 Lappošana + ielādes indikators --}}
    <div class="mt-6 flex justify-center">
        <div wire:loading.delay>
            <span class="text-gray-500 text-sm animate-pulse">{{ __('app.trailers.loading') }}</span>
        </div>
    </div>

    <div class="mt-2">
        {{ $items->links() }}
    </div>
</div>
