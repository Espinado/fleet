<div class="p-4 sm:p-6 max-w-7xl mx-auto">

    {{-- 🔝 Augšējā josla --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-4">

        {{-- 🔍 Meklēšana --}}
        <div class="flex items-center gap-2 w-full md:w-auto">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('app.clients.search') }}"
                class="flex-1 border rounded-lg px-3 py-2 text-sm shadow-sm focus:ring focus:ring-blue-100"
            />
            @if ($search)
                <button wire:click="$set('search','')" class="px-2 py-1 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-600 text-sm">
                    ✖
                </button>
            @endif
        </div>

        {{-- ➕ Pievienot + Kārtot + Rindas --}}
        <div class="flex items-center justify-end gap-3 w-full md:w-auto">

            {{-- ➕ Pievienot --}}
            <a href="{{ route('clients.create') }}"
               class="inline-flex items-center gap-1 bg-green-600 text-white text-sm font-medium px-3 py-1.5 rounded-lg shadow hover:bg-green-700 transition">
                ➕ {{ __('app.clients.add') }}
            </a>

            {{-- 🔽 Kārtot (mobilais) --}}
            <div x-data="{ open: false }" class="relative block md:hidden">
                <button @click="open = !open"
                        class="px-3 py-2 text-sm border rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 flex items-center gap-1">
                    ⬇️ {{ __('app.clients.sort') }}
                    @if ($sortDirection === 'asc')
                        <span class="text-xs text-gray-500">▲</span>
                    @else
                        <span class="text-xs text-gray-500">▼</span>
                    @endif
                </button>

                <div x-show="open" @click.away="open = false"
                     class="absolute left-0 mt-1 w-48 bg-white border rounded-lg shadow-lg z-50 text-sm overflow-hidden">

                    @foreach([
                        'company_name' => __('app.clients.col_company'),
                        'email'        => __('app.clients.col_email'),
                        'reg_nr'       => __('app.clients.col_reg_nr'),
                        'phone'        => __('app.clients.col_phone'),
                        'representative' => __('app.clients.col_representative'),
                    ] as $field => $label)

                        <button wire:click="sortBy('{{ $field }}')" @click="open = false"
                                class="block w-full text-left px-3 py-2 hover:bg-gray-100
                                {{ $sortField === $field ? 'bg-blue-50 text-blue-600 font-medium' : '' }}">
                            {{ $label }}
                            @if ($sortField === $field)
                                {!! $sortDirection === 'asc' ? '▲' : '▼' !!}
                            @endif
                        </button>

                    @endforeach
                </div>
            </div>

            {{-- 📄 Rindas --}}
            <div class="hidden md:flex items-center justify-center gap-2 bg-white px-3 py-1.5 rounded-lg border shadow-sm">
                <label class="text-sm text-gray-600">{{ __('app.clients.rows') }}</label>
                <select wire:model.live="perPage"
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
    <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-100 text-left text-sm text-gray-700 select-none">

                    @foreach([
                        'company_name'  => __('app.clients.col_company'),
                        'email'         => __('app.clients.col_email'),
                        'reg_nr'        => __('app.clients.col_reg_nr'),
                        'phone'         => __('app.clients.col_phone'),
                        'representative'=> __('app.clients.col_representative'),
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

                    <th class="p-3 text-left">{{ __('app.clients.col_action') }}</th>
                </tr>
            </thead>

            <tbody wire:loading.class="opacity-50">
                @forelse($clients as $client)
                    <tr class="border-t hover:bg-gray-50 transition">
                        <td class="px-4 py-2 font-medium">{{ $client->company_name }}</td>
                        <td class="px-4 py-2">{{ $client->email ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $client->reg_nr ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $client->phone ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $client->representative ?? '—' }}</td>
                        <td class="p-3 text-center">
                            <a href="{{ route('clients.show', $client->id) }}"
                               class="text-blue-600 hover:text-blue-800">👁️</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-gray-500">{{ __('app.clients.no_results') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 📱 Mobilā / PWA versija --}}
    <div class="block md:hidden mt-3 space-y-3">
        @forelse($clients as $client)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex justify-between items-start">

                <div class="flex flex-col gap-1">
                    <p class="text-sm font-semibold text-gray-800">
                        {{ $client->company_name }}
                    </p>

                    <p class="text-xs text-gray-600"><b>{{ __('app.clients.col_email') }}:</b> {{ $client->email ?? '—' }}</p>
                    <p class="text-xs text-gray-600"><b>{{ __('app.clients.col_reg_nr') }}:</b> {{ $client->reg_nr ?? '—' }}</p>
                    <p class="text-xs text-gray-600"><b>{{ __('app.clients.col_phone') }}:</b> {{ $client->phone ?? '—' }}</p>
                    <p class="text-xs text-gray-600"><b>{{ __('app.clients.col_representative') }}:</b> {{ $client->representative ?? '—' }}</p>
                </div>

                <a href="{{ route('clients.show', $client->id) }}" class="text-blue-600 text-lg">👁️</a>
            </div>
        @empty
            <div class="text-center text-gray-500 py-10">
                👥 {{ __('app.clients.no_clients') }}
            </div>
        @endforelse
    </div>


    {{-- 🔄 Ielāde + lappošana --}}
    <div class="mt-6 flex justify-center">
        <div wire:loading.delay>
            <span class="text-gray-500 text-sm animate-pulse">{{ __('app.clients.loading') }}</span>
        </div>
    </div>

    <div class="mt-2">
        {{ $clients->links() }}
    </div>
</div>
