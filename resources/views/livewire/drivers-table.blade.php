<div class="p-4 sm:p-6 space-y-4">

    {{-- 🔍 Панель управления --}}
    <div class="bg-white shadow rounded-xl p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        {{-- Поиск --}}
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search driver..."
                class="w-full sm:w-72 border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
            />
            @if($search)
                <button wire:click="$set('search','')"
                        class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600">✖</button>
            @endif
        </div>

        {{-- Кол-во строк, сортировка, кнопка --}}
        <div class="flex flex-wrap items-center justify-between sm:justify-end gap-3 w-full sm:w-auto">
            {{-- Rows --}}
            <div class="flex items-center gap-2 text-sm">
                <label class="text-gray-600">Rows:</label>
                <select wire:model.live="perPage"
                        class="border rounded-lg px-2 py-1 w-20 sm:w-24 text-center bg-white focus:ring-1 focus:ring-blue-400 focus:outline-none">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            {{-- Sort --}}
            <div class="flex items-center gap-2 text-sm">
                <label class="text-gray-600">Sort by:</label>
                <select wire:model.live="sortField"
                        wire:change="sortBy($event.target.value)"
                        class="border rounded-lg px-2 py-1 w-40 bg-white focus:ring-1 focus:ring-blue-400 focus:outline-none">
                    <option value="first_name">Name</option>
                    <option value="pers_code">Personal Code</option>
                    <option value="company">Company</option>
                    <option value="status">Status</option>
                    <option value="is_active">Active</option>
                </select>
            </div>

            <a href="{{ route('drivers.create') }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                ➕ Add Driver
            </a>
        </div>
    </div>

    {{-- 🖥️ Таблица --}}
    <div class="hidden md:block bg-white shadow rounded-xl overflow-x-auto">
        <table class="w-full border-collapse text-sm">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('first_name')">Driver</th>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('pers_code')">Personal Code</th>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('company')">Company</th>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('status')">Status</th>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('is_active')">Active</th>
                    <th class="px-4 py-2 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $driver)
                    <tr class="border-t hover:bg-gray-50 transition">
                        <td class="px-4 py-2">{{ $driver->first_name }} {{ $driver->last_name }}</td>
                        <td class="px-4 py-2">{{ $driver->pers_code }}</td>
                        <td class="px-4 py-2">{{ $driver->company_name }}</td>
                        <td class="px-4 py-2">{{ $driver->status_label }}</td>
                        <td class="px-4 py-2">{{ $driver->is_active ? '✅' : '❌' }}</td>
                        <td class="px-4 py-2 text-center">
                            <a href="/drivers/{{ $driver->id }}" class="text-blue-600 hover:text-blue-800">👁️</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-4 text-gray-500">No results</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 📱 Мобильные карточки --}}
    <div class="grid md:hidden gap-3">
        @forelse($items as $driver)
            <div class="bg-white rounded-xl shadow-sm border p-4 flex justify-between items-start">
                <div>
                    <p class="text-sm font-semibold text-gray-800">{{ $driver->first_name }} {{ $driver->last_name }}</p>
                    <p class="text-xs text-gray-500 mb-1">{{ $driver->pers_code }}</p>
                    <p class="text-xs text-gray-600"><b>Company:</b> {{ $driver->company_name }}</p>
                    <p class="text-xs text-gray-600"><b>Status:</b> {{ $driver->status_label }}</p>
                    <p class="text-xs text-gray-600"><b>Active:</b> {{ $driver->is_active ? '✅ Yes' : '❌ No' }}</p>
                </div>
                <a href="/drivers/{{ $driver->id }}" class="text-blue-600 text-lg">👁️</a>
            </div>
        @empty
            <p class="text-center text-gray-500 py-4">No results</p>
        @endforelse
    </div>

    {{-- 📄 Пагинация --}}
    <div class="flex items-center justify-between flex-wrap gap-2 mt-3 text-sm text-gray-600">
        <div>
            Showing {{ $items->firstItem() ?? 0 }}–{{ $items->lastItem() ?? 0 }} of {{ $items->total() }}
        </div>
        <div>
            {{ $items->links() }}
        </div>
    </div>

</div>
