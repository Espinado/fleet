<div class="p-6">
    {{-- ✅ Уведомления --}}
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
        {{-- 🔍 Панель фильтров --}}
        <div class="flex items-center justify-between mb-4 gap-4 flex-wrap">
            <div class="flex items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Search: client, expeditor, route, cargo"
                    class="border rounded px-3 py-2" />
                <button wire:click="$set('search','')" class="px-2 py-1 rounded bg-gray-200">✖</button>
            </div>

            <div class="flex items-center gap-3">
                <label class="text-sm">Status:</label>
                <select wire:model.live="status" class="border rounded px-2 py-1 w-36">
                    <option value="">All</option>
                    <option value="planned">Planned</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>

                <label class="text-sm">Rows:</label>
                <select wire:model.live="perPage" class="border rounded px-2 py-1 w-24">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="20">20</option>
                </select>

                <a href="{{ route('trips.create') }}"
                   class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                    + Add Trip
                </a>
            </div>
        </div>

        {{-- 📋 Таблица рейсов --}}
        <table class="w-full border-collapse text-left">
            <thead class="bg-gray-100 text-gray-700 border-b">
                <tr>
                    <th class="px-3 py-2">Start</th>
                    <th class="px-3 py-2">Expeditor</th>
                    <th class="px-3 py-2">Driver/Truck/Trailer</th>
                    <th class="px-3 py-2">Route</th>
                    <th class="px-3 py-2">Clients</th>
                    <th class="px-3 py-2 text-right">Total Weight</th>
                    <th class="px-3 py-2 text-right">Total Price</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($trips as $t)
                    @php
                        // === Даты начала и конца рейса ===
                        $startDate = $t->cargos->min('loading_date');
                        $endDate = $t->cargos->max('unloading_date');

                        // === Маршрут стран ===
                        $countryIds = collect($t->cargos)
                            ->flatMap(fn($c) => [$c->loading_country_id, $c->unloading_country_id])
                            ->filter()
                            ->unique()
                            ->values();

                        $route = $countryIds
                            ->map(fn($id) => config("countries.$id.iso") ?? null)
                            ->filter()
                            ->unique()
                            ->implode(' → ');

                        // === Города для tooltip ===
                        $cityNames = collect($t->cargos)
                            ->flatMap(fn($c) => [
                                getCityById($c->loading_city_id),
                                getCityById($c->unloading_city_id),
                            ])
                            ->filter()
                            ->unique()
                            ->implode(' → ');

                        // === Клиенты (Shippers и Consignees) ===
                        $shippers = collect($t->cargos)
                            ->pluck('shipper.company_name')
                            ->filter()
                            ->unique()
                            ->values();

                        $consignees = collect($t->cargos)
                            ->pluck('consignee.company_name')
                            ->filter()
                            ->unique()
                            ->values();

                        $allClients = collect();

                        foreach ($shippers as $s) {
                            $allClients->push(['type' => 'shipper', 'name' => $s]);
                        }

                        foreach ($consignees as $c) {
                            // если клиент уже есть как shipper — не добавляем
                            if (!$allClients->contains(fn($x) => $x['name'] === $c)) {
                                $allClients->push(['type' => 'consignee', 'name' => $c]);
                            }
                        }

                        // === Общий вес и сумма ===
                        $totalWeight = $t->cargos->sum('cargo_weight');
                        $totalPrice = $t->cargos->sum('price');
                    @endphp

                    <tr class="border-b hover:bg-gray-50">
                        {{-- 📅 Даты --}}
                        <td class="px-3 py-2 text-sm whitespace-nowrap">
                            {{ optional($startDate)->format('d.m.Y') ?? '—' }}
                            <div class="text-xs text-gray-500">
                                → {{ optional($endDate)->format('d.m.Y') ?? '—' }}
                            </div>
                        </td>

                        {{-- 🧾 Экспедитор --}}
                        <td class="px-3 py-2 text-sm font-medium">
                            {{ $t->expeditor_name ?? '—' }}
                        </td>

                        {{-- 🚛 Водитель и Тягач --}}
                        <td class="px-3 py-2 text-sm">
                            {{ ($t->driver->first_name ?? '') . ' ' . ($t->driver->last_name ?? '') }}
                            <div class="text-xs text-gray-600">
                                {{ $t->truck->plate ?? '—' }}
                            </div>
                            <div class="text-xs text-gray-600">
                                {{ $t->trailer->plate ?? '—' }}
                            </div>
                        </td>

                        {{-- 🌍 Маршрут --}}
                        <td class="px-3 py-2 text-sm font-medium text-gray-700 relative group">
                            <span class="cursor-help">{{ $route ?: '—' }}</span>

                            @if ($cityNames)
                                <!-- <div class="absolute bottom-full left-0 mb-1 hidden group-hover:block 
                                            bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap 
                                            shadow-lg z-50 opacity-0 group-hover:opacity-100 
                                            transition-opacity duration-200">
                                    {{ $cityNames }}
                                </div> -->
                            @endif
                        </td>

                        {{-- 👥 Клиенты --}}
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

                        {{-- ⚖️ Вес --}}
                        <td class="px-3 py-2 text-sm text-right">
                            {{ number_format($totalWeight, 0, '.', ' ') }} kg
                        </td>

                        {{-- 💶 Цена --}}
                        <td class="px-3 py-2 text-sm text-right">
                            €{{ number_format($totalPrice, 2, '.', ' ') }}
                        </td>

                        {{-- 🏷️ Статус --}}
                        <td class="px-3 py-2 text-sm">
                            <span class="px-2 py-1 rounded text-xs {{ $t->status->color() }}">
                                {{ $t->status->label() }}
                            </span>
                        </td>

                        {{-- ⚙️ Действия --}}
                        <td class="px-3 py-2 text-right text-sm">
                            <a href="{{ route('trips.show', $t->id) }}"
                               class="text-blue-600 hover:underline">👁️</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-gray-500 py-4">No trips found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- 📄 Пагинация --}}
        <div class="mt-4">
            {{ $trips->links() }}
        </div>
    </div>
</div>
