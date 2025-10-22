<div class="p-6">
    {{-- ✅ Уведомление об успехе --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-4">
        {{-- 🔍 Панель поиска, выбора строк и кнопка добавления --}}
        <div class="flex items-center justify-between mb-4 gap-4 flex-wrap">
            {{-- Поиск --}}
            <div class="flex items-center gap-2">
                <input
                    type="text"
                    {{-- Если Livewire v3 --}}
                    wire:model.live.debounce.300ms="search"
                    {{-- Если Livewire v2, замени на: wire:model.debounce.300ms="search" --}}
                    placeholder="Search clients..."
                    class="border rounded px-3 py-2"
                />
                <button wire:click="$set('search','')" class="px-2 py-1 rounded bg-gray-200 hover:bg-gray-300">
                    ✖
                </button>
            </div>

            {{-- Количество строк --}}
            <div class="flex items-center gap-2">
                <label class="text-sm">Rows:</label>
                <select
                    wire:model.live="perPage"
                    class="border rounded px-2 py-1 w-24"
                >
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            {{-- Кнопка добавления нового клиента --}}
            <a href="{{ route('clients.create') }}"
               class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                ➕ Add New Client
            </a>
        </div>

        {{-- 🧾 Таблица клиентов --}}
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('company_name')">
                        Company
                    </th>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('email')">
                        Email
                    </th>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('reg_nr')">
                        Reg Nr
                    </th>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('phone')">
                        Phone
                    </th>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('representative')">
                        Representative
                    </th>
                    <th class="px-4 py-2 text-center">Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($clients as $client)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-2 font-semibold text-gray-900">{{ $client->company_name }}</td>
                        <td class="px-4 py-2">{{ $client->email ?? '-' }}</td>
                         <td class="px-4 py-2">{{ $client->reg_nr ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $client->phone ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $client->representative ?? '-' }}</td>
                        <td class="px-4 py-2 text-center">
                        <a href="{{ route('clients.show', $client->id) }}" class="text-blue-600 hover:underline">👁️</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-gray-500">No clients found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- 📄 Пагинация --}}
        <div class="mt-4">
            {{ $clients->links() }}
        </div>
    </div>
</div>
