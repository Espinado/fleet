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
        {{-- 🔍 Панель фильтров и кнопок --}}
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
                    <option value="50">50</option>
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
                    <th wire:click="sortBy('start_date')" class="px-3 py-2 cursor-pointer">Start</th>
                    <th class="px-3 py-2">Expeditor</th>
                    <th class="px-3 py-2">Clients / Cargo</th>
                    <th class="px-3 py-2">Driver</th>
                    <th class="px-3 py-2">Truck</th>
                    <th class="px-3 py-2">Trailer</th>
                    <th class="px-3 py-2">Route</th>
                    <th wire:click="sortBy('status')" class="px-3 py-2 cursor-pointer">Status</th>
                    <th class="px-3 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($trips as $t)
                    <tr class="border-b hover:bg-gray-50 align-top">
                        {{-- 📅 Дата --}}
                        <td class="px-3 py-2 text-sm whitespace-nowrap">
                            {{ $t->start_date?->format('d.m.Y') ?? '—' }}
                        </td>

                        {{-- 🧾 Экспедитор --}}
                        <td class="px-3 py-2 text-sm">
                            {{ $t->expeditor_name ?? '—' }}
                        </td>

                        {{-- 👥 Клиенты и грузы --}}
                        <td class="px-3 py-2 text-sm">
                            @forelse ($t->cargos as $cargo)
                                <div class="border-b last:border-0 pb-1 mb-1">
                                    <div>
                                        <b>From:</b> {{ $cargo->shipper->company_name ?? '-' }}
                                        → <b>To:</b> {{ $cargo->consignee->company_name ?? '-' }}
                                    </div>
                                    <div class="text-xs text-gray-600">
                                        {{ $cargo->cargo_description ?? '' }}
                                        @if($cargo->price)
                                            — 💶 {{ number_format($cargo->price, 2) }} {{ $cargo->currency }}
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <span class="text-gray-400">No cargo</span>
                            @endforelse
                        </td>

                        {{-- 🚚 Водитель --}}
                        <td class="px-3 py-2 text-sm">
                            {{ ($t->driver->first_name ?? '') . ' ' . ($t->driver->last_name ?? '') }}
                        </td>

                        {{-- 🚛 Грузовик --}}
                        <td class="px-3 py-2 text-sm">{{ $t->truck->plate ?? '—' }}</td>

                        {{-- 🚛 Прицеп --}}
                        <td class="px-3 py-2 text-sm">{{ $t->trailer->plate ?? '—' }}</td>

                        {{-- 🗺️ Маршрут (по первому и последнему грузу) --}}
                        <td class="px-3 py-2 text-sm">
                            @if($t->cargos->isNotEmpty())
                                {{ config('countries.' . $t->cargos->first()->loading_country_id)['iso'] ?? '—' }}
                                →
                                {{ config('countries.' . $t->cargos->last()->unloading_country_id)['iso'] ?? '—' }}
                            @else
                                —
                            @endif
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
