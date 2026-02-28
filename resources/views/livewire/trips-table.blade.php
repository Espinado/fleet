{{-- resources/views/livewire/trips-table.blade.php --}}

<div class="p-4 sm:p-6 max-w-7xl mx-auto">

    {{-- =========================================================
        NOTICES
    ========================================================== --}}
    @if (session('success'))
        <div class="mb-4 p-4 rounded bg-green-100 border border-green-400 text-green-800">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 p-4 rounded bg-red-100 border border-red-400 text-red-800">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-4">

        {{-- =========================================================
            TOP BAR (Search / Filters)
        ========================================================== --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">

            {{-- Search --}}
            <div class="flex items-center gap-2 w-full md:w-auto">
                <input
                    type="text"
                    placeholder="🔍 Search trips..."
                    wire:model.live.debounce.300ms="search"
                    class="w-full border rounded-lg px-3 py-2 text-sm shadow-sm focus:ring focus:ring-blue-100"
                >

                @if ($search)
                    <button
                        wire:click="$set('search','')"
                        class="px-2 py-1 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-600 text-sm"
                        title="Clear search"
                    >
                        ✖
                    </button>
                @endif
            </div>

            <div class="flex items-center justify-end gap-3 w-full md:w-auto">

                <a
                    href="{{ route('trips.create') }}"
                    class="inline-flex items-center gap-1 bg-green-600 text-white text-sm font-medium px-3 py-1.5 rounded-lg shadow hover:bg-green-700"
                >
                    ➕ Add Trip
                </a>

                {{-- Rows --}}
                <div class="hidden md:flex items-center gap-2">
                    <label class="text-sm text-gray-600">Rows:</label>
                    <select wire:model.live="perPage" class="border rounded-lg px-2 py-1 text-sm shadow-sm">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                    </select>
                </div>

                {{-- Status --}}
                <div class="hidden md:flex items-center gap-2">
                    <label class="text-sm text-gray-600">Status:</label>
                    <select wire:model.live="status" class="border rounded-lg px-2 py-1 text-sm shadow-sm w-36">
                        <option value="">All</option>
                        <option value="planned">Planned</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

            </div>
        </div>


        {{-- =========================================================
            DESKTOP TABLE (md+)
        ========================================================== --}}
        <div class="hidden md:block bg-white rounded-lg shadow">
            <table class="w-full border-collapse">
                <thead class="bg-gray-100 text-gray-700 border-b text-sm">
                    <tr>
                        <th class="px-3 py-2 cursor-pointer select-none" wire:click="sortBy('start')">
                            Start/Stop
                            @if($sortField === 'start') {{ $sortDirection === 'asc' ? '▲' : '▼' }} @endif
                        </th>

                        <th class="px-3 py-2 cursor-pointer select-none" wire:click="sortBy('expeditor')">
                            Expeditor / Carrier
                            @if($sortField === 'expeditor') {{ $sortDirection === 'asc' ? '▲' : '▼' }} @endif
                        </th>

                        <th class="px-3 py-2 cursor-pointer select-none" wire:click="sortBy('driver')">
                            Transport
                            @if($sortField === 'driver') {{ $sortDirection === 'asc' ? '▲' : '▼' }} @endif
                        </th>

                        <th class="px-3 py-2 cursor-pointer select-none" wire:click="sortBy('route')">
                            Route
                            @if($sortField === 'route') {{ $sortDirection === 'asc' ? '▲' : '▼' }} @endif
                        </th>

                        <th class="px-3 py-2">
                            Clients
                        </th>

                        <th class="px-3 py-2 cursor-pointer select-none" wire:click="sortBy('status')">
                            Status
                            @if($sortField === 'status') {{ $sortDirection === 'asc' ? '▲' : '▼' }} @endif
                        </th>

                        <th class="px-3 py-2 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody wire:loading.class="opacity-50">
                @forelse($trips as $t)
                    @php
                        $firstLoading   = $t->steps->where('type','loading')->sortBy('date')->first();
                        $lastUnloading  = $t->steps->where('type','unloading')->sortByDesc('date')->first();

                        $startDate = $firstLoading?->date;
                        $endDate   = $lastUnloading?->date;

                        $route = $t->steps
                            ->pluck('country_id')
                            ->filter()
                            ->unique()
                            ->map(fn($id) => config("countries.$id.iso"))
                            ->filter()
                            ->implode(' → ');

                        // clients prepared in Livewire (from cargos)
                        $clientsAll   = collect($t->clients_list ?? []);
                        $clientsCount = $clientsAll->count();

                        $isOwn = $t->scheme_key === 'own';
                    @endphp

                    <tr class="border-b hover:bg-gray-50">

                        {{-- Start/Stop --}}
                        <td class="px-3 py-2 text-sm whitespace-nowrap">
                            <div>{{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d.m.Y') : '—' }}</div>
                            <div class="text-xs text-gray-500">
                                → {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d.m.Y') : '—' }}
                            </div>
                        </td>

                        {{-- Expeditor / Carrier --}}
                        <td class="px-3 py-2 text-sm">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 rounded text-[11px] font-semibold {{ $t->scheme_badge_class }}">
                                    {{ $t->scheme_label }}
                                </span>

                                <span class="font-medium text-gray-900">
                                    {{ $t->expeditor_name }}
                                </span>
                            </div>

                            <div class="text-xs text-gray-600 mt-1">
                                🚚 {{ $t->carrierCompany?->name ?? '—' }}
                                @if($t->carrierCompany?->is_third_party)
                                    <span class="ml-1 px-1.5 py-0.5 rounded bg-red-50 text-red-700 text-[10px] font-semibold">3rd</span>
                                @endif
                            </div>
                        </td>

                        {{-- Transport --}}
                        <td class="px-3 py-2 text-sm">
                            @if($isOwn)
                                <div class="font-medium text-gray-900">
                                    {{ $t->driver?->full_name ?? '—' }}
                                </div>
                            @else
                                <div class="text-xs text-gray-500 font-medium mb-1">External</div>
                            @endif

                            <div class="text-xs {{ $isOwn ? 'text-gray-600' : 'text-gray-700' }}">
                                🚛 {{ $t->truck?->plate ?? '—' }}
                            </div>
                            <div class="text-xs {{ $isOwn ? 'text-gray-600' : 'text-gray-700' }}">
                                🧷 {{ $t->trailer?->plate ?? '—' }}
                            </div>
                        </td>

                        {{-- Route --}}
                        <td class="px-3 py-2 text-sm">
                            @if($route)
                                <span title="{{ $route }}">{{ $route }}</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>

                        {{-- Clients (ULTRA-COMPACT: N clients + Details) --}}
                        <td class="px-3 py-2 text-sm">
                            @if($clientsCount > 0)
                                <div x-data="{ open:false }" class="min-w-[140px]">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-gray-900">
                                            {{ $clientsCount }} clients
                                        </span>
                                        <button
                                            type="button"
                                            class="text-xs text-blue-600 hover:underline"
                                            @click="open = !open"
                                        >
                                            <span x-text="open ? 'Hide' : 'Details'"></span>
                                        </button>
                                    </div>

                                    <div x-show="open" x-transition.opacity.duration.150ms class="mt-2 space-y-1">
                                        @foreach($clientsAll as $c)
                                            <div class="text-xs text-gray-700 truncate">🔹 {{ $c }}</div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="px-3 py-2 text-sm">
                            <span class="px-2 py-1 rounded text-xs {{ $t->status->color() }}">
                                {{ $t->status->label() }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="px-3 py-2 text-right">
                            <a href="{{ route('trips.show', $t->id) }}" class="text-blue-600 hover:underline" title="Open">
                                👁️
                            </a>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-gray-500">No trips found</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>


        {{-- =========================================================
            MOBILE CARDS (sm)
        ========================================================== --}}
        <div class="block md:hidden mt-3 space-y-3">
            @forelse($trips as $t)
                @php
                    $firstLoading   = $t->steps->where('type','loading')->sortBy('date')->first();
                    $lastUnloading  = $t->steps->where('type','unloading')->sortByDesc('date')->first();

                    $startDate = $firstLoading?->date;
                    $endDate   = $lastUnloading?->date;

                    $route = $t->steps
                        ->pluck('country_id')
                        ->filter()
                        ->unique()
                        ->map(fn($id) => config("countries.$id.iso"))
                        ->filter()
                        ->implode(' → ');

                    $clientsAll   = collect($t->clients_list ?? []);
                    $clientsCount = $clientsAll->count();

                    $isOwn = $t->scheme_key === 'own';
                @endphp

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col gap-2">

                    {{-- Header --}}
                    <div class="flex justify-between items-start gap-3">
                        <div class="flex flex-col gap-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2 py-1 rounded text-[11px] font-semibold {{ $t->scheme_badge_class }}">
                                    {{ $t->scheme_label }}
                                </span>

                                <h3 class="text-lg font-semibold text-gray-800 truncate">
                                    {{ $t->expeditor_name }}
                                </h3>
                            </div>

                            <div class="text-xs text-gray-600 truncate">
                                🚚 {{ $t->carrierCompany?->name ?? '—' }}
                                @if($t->carrierCompany?->is_third_party)
                                    <span class="ml-1 px-1.5 py-0.5 rounded bg-red-50 text-red-700 text-[10px] font-semibold">3rd</span>
                                @endif
                            </div>
                        </div>

                        <a href="{{ route('trips.show', $t->id) }}"
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium shrink-0"
                           title="Open">
                            👁️
                        </a>
                    </div>

                    {{-- Date --}}
                    <div class="text-sm text-gray-700">
                        <b>Date:</b>
                        {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d.m.Y') : '—' }}
                        →
                        {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d.m.Y') : '—' }}
                    </div>

                    {{-- Route --}}
                    <div class="text-sm text-gray-700">
                        <b>Route:</b> {{ $route ?: '—' }}
                    </div>

                    {{-- Transport --}}
                    <div class="text-sm text-gray-700">
                        <b>Transport:</b>
                        <div class="mt-1 space-y-0.5">
                            @if($isOwn)
                                <div class="text-xs text-gray-600">👤 {{ $t->driver?->full_name ?? '—' }}</div>
                            @else
                                <div class="text-xs text-gray-500 font-medium">External</div>
                            @endif

                            <div class="text-xs text-gray-600">🚛 {{ $t->truck?->plate ?? '—' }}</div>
                            <div class="text-xs text-gray-600">🧷 {{ $t->trailer?->plate ?? '—' }}</div>
                        </div>
                    </div>

                    {{-- Clients (ULTRA-COMPACT: N clients + Details) --}}
                    <div class="text-sm text-gray-700" x-data="{ open:false }">
                        <div class="flex items-center justify-between">
                            <b>Clients:</b>

                            @if($clientsCount > 0)
                                <button type="button" class="text-xs text-blue-600 hover:underline" @click="open = !open">
                                    <span x-text="open ? 'Hide' : 'Details'"></span>
                                </button>
                            @endif
                        </div>

                        @if($clientsCount > 0)
                            <div class="mt-1">
                                <span class="font-medium text-gray-900">{{ $clientsCount }} clients</span>
                            </div>

                            <div x-show="open" x-transition.opacity.duration.150ms class="mt-2 space-y-1">
                                @foreach($clientsAll as $c)
                                    <div class="text-sm truncate">🔹 {{ $c }}</div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-gray-400">—</div>
                        @endif
                    </div>

                    {{-- Status --}}
                    <div class="text-sm">
                        <span class="px-2 py-1 rounded text-xs {{ $t->status->color() }}">
                            {{ $t->status->label() }}
                        </span>
                    </div>

                </div>

            @empty
                <div class="text-center text-gray-500 py-10">
                    🚚 No trips found
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-4">{{ $trips->links() }}</div>

    </div>
</div>
