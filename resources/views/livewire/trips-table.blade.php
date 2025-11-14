<div class="p-4 sm:p-6 max-w-7xl mx-auto">

    {{-- 🔝 Уведомления --}}
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

        {{-- 🔍 Верхняя панель --}}
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-4">

            {{-- Search --}}
            <div class="flex items-center gap-2 w-full md:w-auto">
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="🔍 Search trips..."
                       class="flex-1 border rounded-lg px-3 py-2 text-sm shadow-sm focus:ring focus:ring-blue-100" />
                @if ($search)
                    <button wire:click="$set('search','')"
                            class="px-2 py-1 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-600 text-sm">
                        ✖
                    </button>
                @endif
            </div>

            {{-- Right panel --}}
            <div class="flex items-center justify-end gap-3 w-full md:w-auto">

                {{-- Add Trip --}}
                <a href="{{ route('trips.create') }}"
                   class="inline-flex items-center gap-1 bg-green-600 text-white text-sm font-medium px-3 py-1.5 rounded-lg shadow hover:bg-green-700 transition">
                    ➕ Add Trip
                </a>

                {{-- Mobile sort button --}}
                <div x-data="{ open: false }" class="relative block md:hidden">
                    <button @click="open = !open"
                            class="px-3 py-2 text-sm border rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 flex items-center gap-1">
                        ⬇️ Sort
                    </button>

                    {{-- Sort menu (mobile) --}}
                    <div x-show="open" @click.away="open = false"
                         class="absolute left-0 mt-1 w-48 bg-white border rounded-lg shadow-lg z-50 text-sm overflow-hidden">

                        @foreach([
                            'start'        => 'Start Date',
                            'expeditor'    => 'Expeditor',
                            'driver'       => 'Driver',
                            'route'        => 'Route',
                            'total_weight' => 'Weight',
                            'total_price'  => 'Price',
                            'status'       => 'Status',
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

                {{-- Desktop Rows --}}
                <div class="hidden md:flex items-center gap-2">
                    <label class="text-sm text-gray-600">Rows:</label>
                    <select wire:model.live="perPage"
                            class="border rounded-lg px-2 py-1 text-sm shadow-sm focus:ring focus:ring-blue-100">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                    </select>
                </div>

                {{-- Status filter --}}
                <div class="hidden md:flex items-center gap-2">
                    <label class="text-sm text-gray-600">Status:</label>
                    <select wire:model.live="status"
                            class="border rounded-lg px-2 py-1 text-sm shadow-sm focus:ring focus:ring-blue-100 w-36">
                        <option value="">All</option>
                        <option value="planned">Planned</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

            </div>
        </div>

        {{-- 💻 DESKTOP TABLE --}}
        <div class="hidden md:block bg-white rounded-lg shadow">
            <table class="w-full border-collapse">
                <thead class="bg-gray-100 text-gray-700 border-b text-sm">
                    <tr>
                        <th class="px-3 py-2">Start</th>
                        <th class="px-3 py-2">Expeditor</th>
                        <th class="px-3 py-2">Driver/Truck/Trailer</th>
                        <th class="px-3 py-2">Route</th>
                        <th class="px-3 py-2">Clients</th>
                        <th class="px-3 py-2 text-right">Weight</th>
                        <th class="px-3 py-2 text-right">Price</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody wire:loading.class="opacity-50">
                @forelse($trips as $t)

                    @php
                        $startDate = $t->cargos->min('loading_date');
                        $endDate = $t->cargos->max('unloading_date');

                        $countryIds = collect($t->cargos)
                            ->flatMap(fn($c) => [$c->loading_country_id, $c->unloading_country_id])
                            ->filter()->unique();

                        $route = $countryIds
                            ->map(fn($id) => config("countries.$id.iso"))
                            ->filter()->unique()
                            ->implode(' → ');

                        $shippers = collect($t->cargos)
                            ->pluck('shipper.company_name')
                            ->filter()->unique();

                        $consignees = collect($t->cargos)
                            ->pluck('consignee.company_name')
                            ->filter()->unique();

                        $allClients = collect();
                        foreach ($shippers as $s) $allClients->push(['type'=>'shipper','name'=>$s]);
                        foreach ($consignees as $c)
                            if (!$allClients->contains(fn($x)=>$x['name']===$c))
                                $allClients->push(['type'=>'consignee','name'=>$c]);

                        $totalWeight = $t->cargos->sum('cargo_weight');
                        $totalPrice = $t->cargos->sum('price');
                    @endphp

                    <tr class="border-b hover:bg-gray-50">

                        {{-- Start --}}
                        <td class="px-3 py-2 text-sm whitespace-nowrap">
                            {{ optional($startDate)->format('d.m.Y') ?? '—' }}
                            <div class="text-xs text-gray-500">
                                → {{ optional($endDate)->format('d.m.Y') ?? '—' }}
                            </div>
                        </td>

                        {{-- Expeditor --}}
                        <td class="px-3 py-2 text-sm font-medium">
                            {{ $t->expeditor_name ?? '—' }}
                        </td>

                        {{-- Driver/Truck --}}
                        <td class="px-3 py-2 text-sm">
                            {{ $t->driver?->first_name }} {{ $t->driver?->last_name }}
                            <div class="text-xs text-gray-600">{{ $t->truck?->plate ?? '—' }}</div>
                            <div class="text-xs text-gray-600">{{ $t->trailer?->plate ?? '—' }}</div>
                        </td>

                        {{-- Route --}}
                        <td class="px-3 py-2 text-sm font-medium text-gray-700">
                            {{ $route ?: '—' }}
                        </td>

                        {{-- Clients --}}
                        <td class="px-3 py-2 text-sm leading-tight">
                            @forelse($allClients as $client)
                                <div class="flex items-center gap-1">
                                    @if ($client['type'] === 'shipper')
                                        <span class="text-blue-500 text-xs">🔵</span>
                                    @else
                                        <span class="text-green-500 text-xs">🟢</span>
                                    @endif
                                    <span>{{ $client['name'] }}</span>
                                </div>
                            @empty
                                <span class="text-gray-400">—</span>
                            @endforelse
                        </td>

                        {{-- Weight --}}
                        <td class="px-3 py-2 text-sm text-right">
                            {{ number_format($totalWeight, 0, '.', ' ') }} kg
                        </td>

                        {{-- Price --}}
                        <td class="px-3 py-2 text-sm text-right">
                            €{{ number_format($totalPrice, 2, '.', ' ') }}
                        </td>

                        {{-- Status --}}
                        <td class="px-3 py-2 text-sm">
                            <span class="px-2 py-1 rounded text-xs {{ $t->status->color() }}">
                                {{ $t->status->label() }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="px-3 py-2 text-right text-sm">
                            <a href="{{ route('trips.show', $t->id) }}" class="text-blue-600 hover:underline">
                                👁️
                            </a>
                        </td>

                    </tr>

                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-gray-500">No trips found</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- 📱 MOBILE CARDS --}}
        <div class="block md:hidden mt-3 space-y-3">

            @forelse($trips as $t)

                @php
                    $startDate = $t->cargos->min('loading_date');
                    $endDate   = $t->cargos->max('unloading_date');

                    $totalWeight = $t->cargos->sum('cargo_weight');
                    $totalPrice  = $t->cargos->sum('price');

                    $countryIds = collect($t->cargos)
                        ->flatMap(fn($c)=>[$c->loading_country_id,$c->unloading_country_id])
                        ->filter()->unique();

                    $route = $countryIds
                        ->map(fn($id)=>config("countries.$id.iso"))
                        ->filter()->unique()
                        ->implode(' → ');
                @endphp

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col gap-2">

                    <div class="flex justify-between items-start">
                        <h3 class="text-lg font-semibold text-gray-800">
                            {{ $t->expeditor_name ?? '—' }}
                        </h3>

                        <a href="{{ route('trips.show', $t->id) }}"
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            👁️
                        </a>
                    </div>

                    <div class="text-sm text-gray-700">
                        <b>Date:</b> {{ optional($startDate)->format('d.m.Y') ?? '—' }}
                        → {{ optional($endDate)->format('d.m.Y') ?? '—' }}
                    </div>

                    <div class="text-sm text-gray-700">
                        <b>Driver:</b> {{ $t->driver?->first_name }} {{ $t->driver?->last_name }}<br>
                        <b>Truck:</b> {{ $t->truck?->plate ?? '—' }}<br>
                        <b>Trailer:</b> {{ $t->trailer?->plate ?? '—' }}
                    </div>

                    <div class="text-sm text-gray-700">
                        <b>Route:</b> {{ $route ?: '—' }}
                    </div>

                    <div class="text-sm text-gray-700 flex justify-between">
                        <span><b>Weight:</b> {{ number_format($totalWeight, 0, '.', ' ') }} kg</span>
                        <span><b>Price:</b> €{{ number_format($totalPrice, 2, '.', ' ') }}</span>
                    </div>

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
