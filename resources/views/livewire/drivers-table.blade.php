<div class="p-4 sm:p-6 max-w-7xl mx-auto">

    {{-- 🔝 Верхняя панель --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-4">

        {{-- 🔍 Поиск --}}
        <div class="flex items-center gap-2 w-full md:w-auto">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="🔍 Search driver..."
                class="flex-1 border rounded-lg px-3 py-2 text-sm shadow-sm focus:ring focus:ring-blue-100"
            />
            @if ($search)
                <button wire:click="$set('search','')" class="px-2 py-1 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-600 text-sm">
                    ✖
                </button>
            @endif
        </div>

        {{-- ➕ Add + Sort + Rows --}}
        <div class="flex items-center justify-end gap-3 w-full md:w-auto">

            {{-- ➕ Add --}}
            <a href="{{ route('drivers.create') }}"
               class="inline-flex items-center gap-1 bg-green-600 text-white text-sm font-medium px-3 py-1.5 rounded-lg shadow hover:bg-green-700 transition">
                ➕ Add Driver
            </a>

            {{-- 🔽 Sort (mobile) --}}
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

                <div x-show="open" @click.away="open = false"
                     class="absolute left-0 mt-1 w-48 bg-white border rounded-lg shadow-lg z-50 text-sm overflow-hidden">
                    @foreach ([
                        'first_name' => 'Name',
                        'pers_code' => 'Personal Code',
                        'phone' => 'Phone',
                        'company' => 'Company',
                        'status' => 'Status'
                    ] as $field => $label)
                        <button wire:click="sortBy('{{ $field }}')" @click="open = false"
                                class="block w-full text-left px-3 py-2 hover:bg-gray-100 {{ $sortField === $field ? 'bg-blue-50 text-blue-600 font-medium' : '' }}">
                            {{ $label }}
                            @if ($sortField === $field)
                                {!! $sortDirection === 'asc' ? '▲' : '▼' !!}
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- 📄 Rows --}}
            <div class="hidden md:flex items-center justify-center gap-2 bg-white px-3 py-1.5 rounded-lg border shadow-sm">
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
    <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-100 text-left text-sm text-gray-700 select-none">
                    @foreach ([
                        'first_name' => 'Driver',
                        'pers_code' => 'Personal Code',
                        'phone' => 'Phone',
                        'status' => 'Status',
                        'company' => 'Company'
                    ] as $field => $label)
                        <th class="px-4 py-2 cursor-pointer whitespace-nowrap" wire:click="sortBy('{{ $field }}')">
                            <div class="flex items-center gap-1">
                                <span>{{ $label }}</span>
                                @if ($sortField === $field)
                                    @if ($sortDirection === 'asc')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    @endif
                                @endif
                            </div>
                        </th>
                    @endforeach
                    <th class="p-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody wire:loading.class="opacity-50">
                @forelse($items as $driver)
                    @php
                        $statusValue = is_int($driver->status) ? $driver->status : $driver->status?->value;
                        $company = config("companies.$driver->company.name") ?? '—';
                    @endphp
                    <tr class="border-t hover:bg-gray-50 transition">
                        <td class="px-4 py-2">
                            <div class="flex items-center gap-2">
                                @if($driver->photo_url)
                                    <img src="{{ $driver->photo_url }}" alt="Photo"
                                         class="w-8 h-8 rounded-full object-cover border border-{{ $driver->status_color }}-400">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">👤</div>
                                @endif
                                <span>{{ $driver->first_name }} {{ $driver->last_name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-2">{{ $driver->pers_code }}</td>
                        <td class="px-4 py-2">{{ $driver->phone ?? '—' }}</td>
                        <td class="px-4 py-2">
                            <span class="px-2 py-1 rounded text-xs font-medium
                                bg-{{ $driver->status_color }}-100 text-{{ $driver->status_color }}-700">
                                {{ $driver->status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-2">{{ $company }}</td>
                        <td class="p-3 text-center">
                            <a href="{{ route('drivers.show', $driver->id) }}"
                               class="text-blue-600 hover:text-blue-800">👁️</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-gray-500">No results</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 📱 MOBILE / PWA VERSION --}}
    <div class="block md:hidden mt-3 space-y-3">
        @forelse($items as $driver)
            @php
                $company = config("companies.$driver->company.name") ?? '—';
            @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex justify-between items-start">
                <div class="flex gap-3">
                    @if($driver->photo_url)
                        <img src="{{ $driver->photo_url }}" alt="Photo"
                             class="w-12 h-12 rounded-full object-cover border border-{{ $driver->status_color }}-400">
                    @else
                        <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-lg">👤</div>
                    @endif

                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ $driver->first_name }} {{ $driver->last_name }}</p>
                        <p class="text-xs text-gray-500 mb-1">{{ $driver->pers_code }}</p>
                        <p class="text-xs text-gray-600"><b>Phone:</b> {{ $driver->phone ?? '—' }}</p>
                        <p class="text-xs text-gray-600"><b>Company:</b> {{ $company }}</p>
                        <p class="text-xs">
                            <b>Status:</b>
                            <span class="text-{{ $driver->status_color }}-600 font-medium">
                                {{ $driver->status_label }}
                            </span>
                        </p>
                    </div>
                </div>
                <a href="{{ route('drivers.show', $driver->id) }}" class="text-blue-600 text-lg">👁️</a>
            </div>
        @empty
            <div class="text-center text-gray-500 py-10">
                👨‍✈️ No drivers found
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
