<div class="p-6 bg-white rounded-lg shadow">

    {{-- Верхняя панель: Add New и поиск --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-4 gap-4">
        <div>
            <a href="{{ route('trailers.create') }}"
               class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                ➕ Add New Trailer
            </a>
        </div>

        <div class="flex items-center gap-2">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Поиск по прицепу"
                class="border rounded px-3 py-2"
            />
            <button wire:click="$set('search','')" class="px-2 py-1 rounded bg-gray-200">✖</button>

            <label class="text-sm ml-4 mr-2">Rows:</label>
            <select wire:model.live="perPage" class="border rounded px-2 py-1">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>

    {{-- Таблица --}}
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-gray-100 text-left">
                <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('brand')">Brand/Model</th>
                <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('plate')">Plate</th>
                <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('inspection_expired')">Inspection till</th>
                <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('insurance_expired')">Insurance till</th>
                <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('status')">Status</th>
                <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('is_active')">Active</th>
                <th class="p-3 text-left">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $trailer)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $trailer->brand }} {{ $trailer->model }}</td>
                    <td class="px-4 py-2">{{ $trailer->plate }}</td>
                    <td class="px-4 py-2">{{ $trailer->inspection_expired }}</td>
                    <td class="px-4 py-2">{{ $trailer->insurance_expired }}</td>
                    <td class="px-4 py-2">{{ $trailer->status == 1 ? '✅ Active' : '❌ Inactive' }}</td>
                 <td class="px-4 py-2">{{ $trailer->company_name }}</td>
                    <td class="p-3 border text-center">
                        <a href="{{ route('trailers.show', $trailer->id) }}" class="text-blue-600">👁️</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-gray-500">No results</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Пагинация --}}
    <div class="mt-4">
        {{ $items->links() }}
    </div>
</div>
