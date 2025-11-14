<div class="p-4 sm:p-6 max-w-7xl mx-auto">

    {{-- 🔝 Верхняя панель (адаптивная) --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-4">

        {{-- 🔍 Поиск --}}
        <div class="flex items-center gap-2 w-full md:w-auto">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="🔍 Search document..."
                class="flex-1 border rounded-lg px-3 py-2 text-sm shadow-sm focus:ring focus:ring-blue-100"
            />
            @if ($search)
                <button wire:click="$set('search','')" class="px-2 py-1 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-600 text-sm">
                    ✖
                </button>
            @endif
        </div>

        {{-- 🔽 Sort (mobile) + Rows (desktop) --}}
        <div class="flex items-center justify-end gap-3 w-full md:w-auto">

            {{-- 🔽 Кнопка сортировки (только мобильные) --}}
            <div x-data="{ open: false }" class="relative block md:hidden">
                <button @click="open = !open"
                        class="px-3 py-2 text-sm border rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 flex items-center gap-1">
                    ⬇️ Sort
                    @if ($sortDirection === 'asc')
                        <span class="text-xs">▲</span>
                    @else
                        <span class="text-xs">▼</span>
                    @endif
                </button>

                {{-- Меню сортировки мобильное --}}
                <div x-show="open" @click.away="open = false"
                     class="absolute left-0 mt-1 w-48 bg-white border rounded-lg shadow-lg z-50 text-sm overflow-hidden">

                    @foreach ([
                        'type'        => 'Type',
                        'name'        => 'Name',
                        'document'    => 'Document',
                        'expiry_date' => 'Expiry date',
                        'company'     => 'Company',
                        'status'      => 'Status',
                        'is_active'   => 'Active',
                    ] as $field => $label)

                        <button wire:click="sortBy('{{ $field }}')" @click="open = false"
                                class="block w-full text-left px-3 py-2 hover:bg-gray-100
                                {{ $sortField === $field ? 'bg-blue-50 text-blue-600 font-medium' : '' }}">
                            {{ $label }}
                            @if ($sortField === $field)
                                {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                            @endif
                        </button>

                    @endforeach
                </div>
            </div>

            {{-- 📄 Кол-во строк — только для desktop --}}
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
            <tr class="bg-gray-100 text-left text-sm">
                @php
                    $cols = [
                        'type'        => 'Type',
                        'name'        => 'Name',
                        'document'    => 'Document',
                        'expiry_date' => 'Expiry date',
                        'company'     => 'Company',
                        'status'      => 'Status',
                        'is_active'   => 'Active',
                    ];
                @endphp

                @foreach($cols as $field => $label)
                    <th class="px-4 py-2 cursor-pointer"
                        wire:click="sortBy('{{ $field }}')">
                        {{ $label }}
                        @if ($sortField === $field)
                            {!! $sortDirection === 'asc' ? '▲' : '▼' !!}
                        @endif
                    </th>
                @endforeach

                <th class="p-3 text-left">Action</th>
            </tr>
            </thead>

            <tbody wire:loading.class="opacity-50">
            @forelse($items as $item)
                @php
                    $days = $item->days_left;
                    $bg =
                        $days < 0 ? 'bg-rose-100' :
                        ($days <= 10 ? 'bg-red-200' :
                        ($days <= 20 ? 'bg-orange-200' :
                        ($days <= 30 ? 'bg-yellow-100' : 'bg-white')));
                @endphp

                <tr class="border-t hover:bg-gray-50 {{ $bg }}">
                    <td class="px-4 py-2">{{ $item->type }}</td>
                    <td class="px-4 py-2">{{ $item->name }}</td>
                    <td class="px-4 py-2">{{ $item->document }}</td>

                    <td class="px-4 py-2">
                        {{ $item->expiry_date->format('d.m.Y') }}
                        <span class="text-xs text-gray-600 ml-1">
                            @if($days < 0)
                                (expired {{ abs($days) }}d)
                            @elseif($days == 0)
                                (today)
                            @else
                                (in {{ $days }}d)
                            @endif
                        </span>
                    </td>

                    <td class="px-4 py-2">{{ $item->company }}</td>
                    <td class="px-4 py-2">{{ $item->status }}</td>
                    <td class="px-4 py-2">{{ $item->is_active ? '✅' : '❌' }}</td>

                    <td class="p-3 text-center">
                        <a href="/{{ strtolower($item->type) }}s/{{ $item->id }}"
                           class="text-blue-600">👁️</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-gray-500">No results</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- 📱 MOBILE / PWA VERSION --}}
    <div class="block md:hidden mt-3 space-y-3">
        @forelse($items as $item)
            @php
                $days = $item->days_left;
                $bg =
                    $days < 0 ? 'bg-rose-100' :
                    ($days <= 10 ? 'bg-red-200' :
                    ($days <= 20 ? 'bg-orange-200' :
                    ($days <= 30 ? 'bg-yellow-100' : 'bg-white')));
            @endphp

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col gap-2 {{ $bg }}">
                <div class="flex justify-between items-start">
                    <h3 class="text-lg font-semibold text-gray-800">
                        {{ $item->name }}
                    </h3>

                    <a href="/{{ strtolower($item->type) }}s/{{ $item->id }}"
                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        👁️
                    </a>
                </div>

                <div class="text-sm text-gray-700 grid grid-cols-2 gap-y-1">
                    <div><b>Type:</b> {{ $item->type }}</div>
                    <div><b>Company:</b> {{ $item->company }}</div>

                    <div><b>Document:</b> {{ $item->document }}</div>

                    <div>
                        <b>Expiry:</b>
                        {{ $item->expiry_date->format('d.m.Y') }}
                        ({{ $days < 0 ? "expired $days d" : "in $days d" }})
                    </div>

                    <div>
                        <b>Status:</b>
                        {{ $item->status }}
                    </div>

                    <div>
                        <b>Active:</b>
                        {{ $item->is_active ? '✅' : '❌' }}
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 py-10">
                📄 No documents found
            </div>
        @endforelse
    </div>

    {{-- 🔄 Loading + Pagination --}}
    <div class="mt-6 flex justify-center">
        <div wire:loading.delay>
            <span class="text-gray-500 text-sm animate-pulse">Loading...</span>
        </div>
    </div>

    <div class="mt-2">
        {{ $items->links() }}
    </div>

</div>
