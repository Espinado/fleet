<div class="p-4 sm:p-6 max-w-7xl mx-auto">

    {{-- 🔝 Notices --}}
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

        {{-- 🔍 TOP BAR --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">

            {{-- Search --}}
            <div class="flex items-center gap-2 w-full md:w-auto">
                <input type="text"
                       placeholder="🔍 Search trips..."
                       wire:model.live.debounce.300ms="search"
                       class="w-full border rounded-lg px-3 py-2 text-sm shadow-sm focus:ring focus:ring-blue-100">

                @if ($search)
                    <button wire:click="$set('search','')"
                            class="px-2 py-1 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-600 text-sm">
                        ✖
                    </button>
                @endif
            </div>

            <div class="flex items-center justify-end gap-3 w-full md:w-auto">

                <a href="{{ route('trips.create') }}"
                   class="inline-flex items-center gap-1 bg-green-600 text-white text-sm font-medium px-3 py-1.5 rounded-lg shadow hover:bg-green-700">
                    ➕ Add Trip
                </a>

                {{-- Rows --}}
                <div class="hidden md:flex items-center gap-2">
                    <label class="text-sm text-gray-600">Rows:</label>
                    <select wire:model.live="perPage"
                            class="border rounded-lg px-2 py-1 text-sm shadow-sm">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                    </select>
                </div>

                {{-- Status --}}
                <div class="hidden md:flex items-center gap-2">
                    <label class="text-sm text-gray-600">Status:</label>
                    <select wire:model.live="status"
                            class="border rounded-lg px-2 py-1 text-sm shadow-sm w-36">
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
                        <th class="px-3 py-2 cursor-pointer" wire:click="sortBy('start')">
                            Start/Stop
                            @if($sortField==='start') {{ $sortDirection==='asc'?'▲':'▼' }} @endif
                        </th>
                        <th class="px-3 py-2">Expeditor</th>
                        <th class="px-3 py-2">Driver/Truck/Trailer</th>
                        <th class="px-3 py-2 cursor-pointer" wire:click="sortBy('route')">
                            Route
                        </th>
                        <th class="px-3 py-2">Clients</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody wire:loading.class="opacity-50">

                @forelse($trips as $t)
                    @php
                        $firstLoading = $t->steps->where('type','loading')->sortBy('date')->first();
                        $lastUnloading = $t->steps->where('type','unloading')->sortByDesc('date')->first();

                        $startDate = $firstLoading?->date;
                        $endDate   = $lastUnloading?->date;

                        $route = $t->steps
                            ->pluck('country_id')
                            ->filter()->unique()
                            ->map(fn($id)=>config("countries.$id.iso"))
                            ->implode(' → ');

                        $clients = $t->steps
                            ->pluck('client.company_name')
                            ->filter()->unique();
                    @endphp

                    <tr class="border-b hover:bg-gray-50">

                        <td class="px-3 py-2 text-sm whitespace-nowrap">
                            <div>{{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d.m.Y H:i') : '—' }}</div>
                            <div class="text-xs text-gray-500">
                                → {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d.m.Y H:i') : '—' }}
                            </div>
                        </td>

                        <td class="px-3 py-2 text-sm font-medium">
                            {{ $t->expeditor_name }}
                        </td>

                        <td class="px-3 py-2 text-sm">
                            {{ $t->driver?->full_name }}
                            <div class="text-xs text-gray-600">{{ $t->truck?->plate }}</div>
                            <div class="text-xs text-gray-600">{{ $t->trailer?->plate }}</div>
                        </td>

                        <td class="px-3 py-2 text-sm">
                            {{ $route ?: '—' }}
                        </td>

                        <td class="px-3 py-2 text-sm">
                            @forelse($clients as $c)
                                <div>🔹 {{ $c }}</div>
                            @empty
                                <span class="text-gray-400">—</span>
                            @endforelse
                        </td>

                        <td class="px-3 py-2 text-sm">
                            <span class="px-2 py-1 rounded text-xs {{ $t->status->color() }}">
                                {{ $t->status->label() }}
                            </span>
                        </td>

                        <td class="px-3 py-2 text-right">
                            <a href="{{ route('trips.show', $t->id) }}" class="text-blue-600 hover:underline">
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

        {{-- 📱 MOBILE VERSION --}}
        <div class="block md:hidden mt-3 space-y-3">

            @forelse($trips as $t)
                @php
                    $firstLoading = $t->steps->where('type','loading')->sortBy('date')->first();
                    $lastUnloading = $t->steps->where('type','unloading')->sortByDesc('date')->first();

                    $startDate = $firstLoading?->date;
                    $endDate   = $lastUnloading?->date;

                    $route = $t->steps->pluck('country_id')
                        ->filter()->unique()
                        ->map(fn($id)=>config("countries.$id.iso"))
                        ->implode(' → ');

                    $clients = $t->steps
                        ->pluck('client.company_name')
                        ->filter()->unique();
                @endphp

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col gap-2">

                    <div class="flex justify-between items-start">
                        <h3 class="text-lg font-semibold text-gray-800">
                            {{ $t->expeditor_name }}
                        </h3>

                        <a href="{{ route('trips.show', $t->id) }}"
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            👁️
                        </a>
                    </div>

                    <div class="text-sm text-gray-700">
                        <b>Date:</b>
                        {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d.m.Y') : '—' }}
                        →
                        {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d.m.Y') : '—' }}
                    </div>

                    <div class="text-sm text-gray-700">
                        <b>Route:</b> {{ $route ?: '—' }}
                    </div>

                    <div class="text-sm text-gray-700">
                        <b>Clients:</b><br>
                        @forelse($clients as $c)
                            <div>🔹 {{ $c }}</div>
                        @empty
                            <span class="text-gray-400">—</span>
                        @endforelse
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
