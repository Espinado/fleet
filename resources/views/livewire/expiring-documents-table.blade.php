<div class="p-4 sm:p-6 max-w-7xl mx-auto">

    {{-- 🔝 Верхняя панель (адаптивная) --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-4">

        {{-- 🔍 Meklēšana --}}
        <div class="flex items-center gap-2 w-full md:w-auto">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="🔍 {{ __('app.exp.search') }}"
                class="flex-1 border rounded-lg px-3 py-2 text-sm shadow-sm focus:ring focus:ring-blue-100"
            />
            @if ($search)
                <button wire:click="$set('search','')" class="px-2 py-1 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-600 text-sm">
                    ✖
                </button>
            @endif
        </div>

        {{-- 🔽 Kārtošana (mobilā) + Rindu skaits (desktop) --}}
        <div class="flex items-center justify-end gap-3 w-full md:w-auto">

            {{-- 🔽 Kārtošanas poga (tikai mobilajām ierīcēm) --}}
            <div x-data="{ open: false }" class="relative block md:hidden">
                <button @click="open = !open"
                        class="px-3 py-2 text-sm border rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 flex items-center gap-1">
                    ⬇️ {{ __('app.exp.sort') }}
                    @if ($sortDirection === 'asc')
                        <span class="text-xs">▲</span>
                    @else
                        <span class="text-xs">▼</span>
                    @endif
                </button>

                {{-- Mobilā kārtošanas izvēlne --}}
                <div x-show="open" @click.away="open = false"
                     class="absolute left-0 mt-1 w-48 bg-white border rounded-lg shadow-lg z-50 text-sm overflow-hidden">

                    @foreach ([
                        'type'        => 'Tips',
                        'name'        => 'Nosaukums',
                        'document'    => 'Dokuments',
                        'expiry_date' => 'Derīgs līdz',
                        'company'     => 'Kompānija',
                        'status'      => 'Statuss',
                        'is_active'   => 'Aktīvs',
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

            {{-- 📄 Rindu skaits — tikai desktop --}}
            <div class="hidden md:flex items-center gap-2">
                <label for="perPage" class="text-sm text-gray-600">{{ __('app.exp.rows') }}:</label>
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

    {{-- 💻 TABULAS VERSIJA --}}
    <div class="hidden md:block bg-white rounded-lg shadow">
        <table class="w-full border-collapse">
            <thead>
            <tr class="bg-gray-100 text-left text-sm">
                @php
                    $cols = [
                        'type'        => __('app.exp.col_type'),
                        'name'        => __('app.exp.col_name'),
                        'document'    => __('app.exp.col_document'),
                        'expiry_date' => __('app.exp.col_expiry'),
                        'company'     => __('app.exp.col_company'),
                        'status'      => __('app.exp.col_status'),
                        'is_active'   => __('app.exp.col_active'),
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

                <th class="p-3 text-left">{{ __('app.exp.col_action') }}</th>
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
                                ({{ __('app.exp.expired_before', ['days' => abs($days)]) }})
                            @elseif($days == 0)
                                ({{ __('app.exp.expired_today') }})
                            @else
                                ({{ __('app.exp.expires_in', ['days' => $days]) }})
                            @endif
                        </span>
                    </td>

                    <td class="px-4 py-2">{{ $item->company }}</td>
                    <td class="px-4 py-2">{{ $item->status }}</td>
                    <td class="px-4 py-2">{{ $item->is_active ? '✅' : '❌' }}</td>

                    <td class="p-3 text-center">
                        <a href="/{{ strtolower($item->type) }}s/{{ $item->id }}"
                           class="text-blue-600" title="Atvērt">👁️</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-gray-500">{{ __('app.exp.no_data') }}</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- 📱 MOBILĀ / PWA VERSIJA --}}
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
                    <div><b>{{ __('app.exp.col_type') }}:</b> {{ $item->type }}</div>
                    <div><b>{{ __('app.exp.col_company') }}:</b> {{ $item->company }}</div>

                    <div><b>{{ __('app.exp.col_document') }}:</b> {{ $item->document }}</div>

                    <div>
                        <b>{{ __('app.exp.col_expiry') }}:</b>
                        {{ $item->expiry_date->format('d.m.Y') }}
                        ({{ $days < 0
                            ? __('app.exp.expired_before', ['days' => abs($days)])
                            : __('app.exp.expires_in', ['days' => $days]) }})
                    </div>

                    <div>
                        <b>{{ __('app.exp.col_status') }}:</b>
                        {{ $item->status }}
                    </div>

                    <div>
                        <b>{{ __('app.exp.col_active') }}:</b>
                        {{ $item->is_active ? '✅' : '❌' }}
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 py-10">
                📄 {{ __('app.exp.no_docs') }}
            </div>
        @endforelse
    </div>

    {{-- 🔄 Loading + Pagination --}}
    <div class="mt-6 flex justify-center">
        <div wire:loading.delay>
            <span class="text-gray-500 text-sm animate-pulse">{{ __('app.exp.loading') }}</span>
        </div>
    </div>

    <div class="mt-2">
        {{ $items->links() }}
    </div>

</div>
