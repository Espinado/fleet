<div class="p-4 sm:p-6 max-w-7xl mx-auto">

    {{-- 🔝 Верхняя панель (адаптивная) --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-4">

        {{-- 🔍 Поиск --}}
        <div class="flex items-center gap-2 w-full md:w-auto">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="🔍 Search truck..."
                class="flex-1 border rounded-lg px-3 py-2 text-sm shadow-sm focus:ring focus:ring-blue-100"
            />
            @if ($search)
                <button wire:click="$set('search','')" class="px-2 py-1 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-600 text-sm">
                    ✖
                </button>
            @endif
        </div>

        {{-- ➕ Add + Sort (mobile) + Rows (desktop) --}}
        <div class="flex items-center justify-end gap-3 w-full md:w-auto">

            {{-- ➕ Кнопка Add --}}
            <a href="{{ route('trucks.create') }}"
               class="inline-flex items-center gap-1 bg-green-600 text-white text-sm font-medium px-3 py-1.5 rounded-lg shadow hover:bg-green-700 transition">
                ➕ Add Truck
            </a>

            {{-- 🔽 Кнопка сортировки (только для мобильных) --}}
            <div x-data="{ open: false }" class="relative block md:hidden">
                <button @click="open = !open"
                        class="px-3 py-2 text-sm border rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 flex items-center gap-1">
                    ⬇️ Sort
                    @if ($sortDirection === 'asc')
                        <span class="text-xs text-gray-500">▲</span>
                    @else
                        <span class="text-xs text-gray-500">▼</span>
                    @endif
                </button>

                {{-- Меню сортировки --}}
                <div x-show="open" @click.away="open = false"
                     class="absolute left-0 mt-1 w-48 bg-white border rounded-lg shadow-lg z-50 text-sm overflow-hidden">
                    @foreach ([
                        'brand' => 'Brand',
                        'plate' => 'Plate',
                        'inspection_expired' => 'Inspection',
                        'insurance_expired' => 'Insurance',
                        'status' => 'Status',
                        'company' => 'Company',
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

            {{-- 📄 Кол-во строк (только для настольных) --}}
            <div class="hidden md:flex items-center gap-2">
                <label for="perPage" class="text-sm text-gray-600">Rows:</label>
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

    {{-- 💻 TABLE VERSION --}}
    <div class="hidden md:block bg-white rounded-lg shadow">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('brand')">
                        Brand/Model
                        @if ($sortField === 'brand')
                            {!! $sortDirection === 'asc' ? '▲' : '▼' !!}
                        @endif
                    </th>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('plate')">
                        Plate
                        @if ($sortField === 'plate')
                            {!! $sortDirection === 'asc' ? '▲' : '▼' !!}
                        @endif
                    </th>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('inspection_expired')">
                        Inspection till
                        @if ($sortField === 'inspection_expired')
                            {!! $sortDirection === 'asc' ? '▲' : '▼' !!}
                        @endif
                    </th>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('insurance_expired')">
                        Insurance till
                        @if ($sortField === 'insurance_expired')
                            {!! $sortDirection === 'asc' ? '▲' : '▼' !!}
                        @endif
                    </th>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('status')">
                        Status
                        @if ($sortField === 'status')
                            {!! $sortDirection === 'asc' ? '▲' : '▼' !!}
                        @endif
                    </th>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('company')">
                        Company
                        @if ($sortField === 'company')
                            {!! $sortDirection === 'asc' ? '▲' : '▼' !!}
                        @endif
                    </th>
                    <th class="p-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody wire:loading.class="opacity-50">
                @forelse($items as $truck)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $truck->brand }} {{ $truck->model }}</td>
                        <td class="px-4 py-2">{{ $truck->plate }}</td>
                        <td class="px-4 py-2">{{ $truck->inspection_expired }}</td>
                        <td class="px-4 py-2">{{ $truck->insurance_expired }}</td>
                        <td class="px-4 py-2">{{ $truck->status == 1 ? '✅ Active' : '❌ Inactive' }}</td>
                        <td class="px-4 py-2">{{ $truck->company_name }}</td>
                        <td class="p-3 border text-center">
                            <a href="{{ route('trucks.show', $truck->id) }}" class="text-blue-600">👁️</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-gray-500">No results</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 📱 MOBILE / PWA VERSION --}}
    <div class="block md:hidden mt-3 space-y-3">
        @forelse($items as $truck)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col gap-2">
                <div class="flex justify-between items-start">
                    <h3 class="text-lg font-semibold text-gray-800">
                        {{ $truck->brand }} {{ $truck->model }}
                    </h3>
                    <a href="{{ route('trucks.show', $truck->id) }}"
                       class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1">
                        👁️
                    </a>
                </div>

                <div class="text-sm text-gray-700 grid grid-cols-2 gap-y-1">
                    <div><b>Plate:</b> {{ $truck->plate }}</div>
                    <div>
                        <b>Status:</b>
                        @if ($truck->status == 1)
                            <span class="text-green-600 font-medium">Active</span>
                        @else
                            <span class="text-red-500 font-medium">Inactive</span>
                        @endif
                    </div>
                    <div><b>Inspection:</b> {{ $truck->inspection_expired ?? '—' }}</div>
                    <div><b>Insurance:</b> {{ $truck->insurance_expired ?? '—' }}</div>
                    <div class="col-span-2"><b>Company:</b> {{ $truck->company_name ?? '—' }}</div>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 py-10">
                🚚 No trucks found
            </div>
        @endforelse
    </div>

    {{-- 🔄 Пагинация + индикатор загрузки --}}
    <div class="mt-6 flex justify-center">
        <div wire:loading.delay>
            <span class="text-gray-500 text-sm animate-pulse">Loading...</span>
        </div>
    </div>

    <div class="mt-2">
        {{ $items->links() }}
    </div>
</div>
