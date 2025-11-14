<div class="p-4 sm:p-6 max-w-7xl mx-auto">

    {{-- 🔝 Верхняя панель --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-4">

        {{-- 🔍 Поиск --}}
        <div class="flex items-center gap-2 w-full md:w-auto">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="🔍 Search clients..."
                class="flex-1 border rounded-lg px-3 py-2 text-sm shadow-sm focus:ring focus:ring-blue-100"
            />
            @if ($search)
                <button wire:click="$set('search','')" class="px-2 py-1 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-600 text-sm">
                    ✖
                </button>
            @endif
        </div>

        {{-- 🔽 Sort (mobile) + Rows (desktop) + Add --}}
        <div class="flex items-center justify-end gap-3 w-full md:w-auto">

            {{-- ➕ Add --}}
            <a href="{{ route('clients.create') }}"
               class="inline-flex items-center gap-1 bg-green-600 text-white text-sm font-medium px-3 py-1.5 rounded-lg shadow hover:bg-green-700 transition">
                ➕ Add Client
            </a>

            {{-- 🔽 Sort mobile --}}
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

                <div x-show="open" @click.away="open = false"
                     class="absolute left-0 mt-1 w-48 bg-white border rounded-lg shadow-lg z-50 text-sm overflow-hidden">

                    @foreach([
                        'company_name'  => 'Company',
                        'email'         => 'Email',
                        'reg_nr'        => 'Reg Nr',
                        'phone'         => 'Phone',
                        'representative'=> 'Representative',
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

            {{-- Rows desktop --}}
            <div class="hidden md:flex items-center gap-2">
                <label class="text-sm text-gray-600">Rows:</label>
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

    {{-- 💻 DESKTOP TABLE --}}
    <div class="hidden md:block bg-white rounded-lg shadow">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-100 text-left text-sm">
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('company_name')">
                        Company
                        @if ($sortField === 'company_name') {!! $sortDirection === 'asc' ? '▲' : '▼' !!} @endif
                    </th>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('email')">
                        Email
                        @if ($sortField === 'email') {!! $sortDirection === 'asc' ? '▲' : '▼' !!} @endif
                    </th>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('reg_nr')">
                        Reg Nr
                        @if ($sortField === 'reg_nr') {!! $sortDirection === 'asc' ? '▲' : '▼' !!} @endif
                    </th>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('phone')">
                        Phone
                        @if ($sortField === 'phone') {!! $sortDirection === 'asc' ? '▲' : '▼' !!} @endif
                    </th>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('representative')">
                        Representative
                        @if ($sortField === 'representative') {!! $sortDirection === 'asc' ? '▲' : '▼' !!} @endif
                    </th>
                    <th class="px-4 py-2 text-center">Action</th>
                </tr>
            </thead>

            <tbody wire:loading.class="opacity-50">
                @forelse($clients as $client)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-2 font-semibold text-gray-900">{{ $client->company_name }}</td>
                        <td class="px-4 py-2">{{ $client->email ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $client->reg_nr ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $client->phone ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $client->representative ?? '—' }}</td>
                        <td class="px-4 py-2 text-center">
                            <a href="{{ route('clients.show', $client->id) }}" class="text-blue-600">👁️</a>
                        </td>
                    </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-gray-500">No clients found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 📱 MOBILE VERSION --}}
    <div class="block md:hidden mt-3 space-y-3">
        @forelse($clients as $client)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col gap-2">

                <div class="flex justify-between items-start">
                    <h3 class="text-lg font-semibold text-gray-800">
                        {{ $client->company_name }}
                    </h3>

                    <a href="{{ route('clients.show', $client->id) }}"
                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        👁️
                    </a>
                </div>

                <div class="text-sm text-gray-700 grid grid-cols-2 gap-y-1">
                    <div><b>Email:</b> {{ $client->email ?? '—' }}</div>
                    <div><b>Reg Nr:</b> {{ $client->reg_nr ?? '—' }}</div>
                    <div><b>Phone:</b> {{ $client->phone ?? '—' }}</div>
                    <div><b>Representative:</b> {{ $client->representative ?? '—' }}</div>
                </div>

            </div>
        @empty
            <div class="text-center text-gray-500 py-10">
                👥 No clients found
            </div>
        @endforelse
    </div>

    {{-- 🔄 Loading & Pagination --}}
    <div class="mt-6 flex justify-center">
        <div wire:loading.delay>
            <span class="text-gray-500 text-sm animate-pulse">Loading...</span>
        </div>
    </div>

    <div class="mt-2">
        {{ $clients->links() }}
    </div>
</div>
