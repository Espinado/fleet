<div class="p-6">
    <div class="bg-white shadow rounded-lg p-4">
        {{-- Панель поиска и выбора количества строк --}}
        <div class="flex items-center justify-between mb-4 gap-4">
        <div class="flex items-center gap-2">
            {{-- Если у вас Livewire v3, используйте: wire:model.live.debounce.300ms --}}
            {{-- Если у вас Livewire v2, используйте: wire:model.debounce.300ms --}}
            <input
                type="text"
                {{-- ВАЖНО: замените на нужный синтаксис в зависимости от версии Livewire --}}
                wire:model.live.debounce.300ms="search"
                {{-- wire:model.debounce.300ms="search" --}}
                placeholder="Поиск по тягачу"
                class="border rounded px-3 py-2"
            />
            <button wire:click="$set('search','')" class="px-2 py-1 rounded bg-gray-200">✖</button>
        </div>

        <div>
            <label class="text-sm mr-2">Rows:</label>
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
                    {{--  <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('last_name')">Last Name</th>  --}}
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('plate')">plate</th>
                      <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('inspection_expired')">Inspection till</th>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('insurance_expired')">Insurance till</th>

                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('status')">Status</th>
                    <th class="px-4 py-2 cursor-pointer" wire:click="sortBy('is_active')">Active</th>
                      <th class="p-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $truck)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $truck->brand }} {{ $truck->model }}</td>
                        {{--  <td class="px-4 py-2">{{ $driver->last_name }}</td>  --}}
                        <td class="px-4 py-2">{{ $truck->plate }}</td>
                         <td class="px-4 py-2">{{ $truck->inspection_expired }}</td>
                        <td class="px-4 py-2">{{ $truck->insurance_expired }}</td>
                      
                        <td class="px-4 py-2">
                            {{ $truck->status == 1 ? '✅ Active' : '❌ Inactive' }}
                        </td>
                        <td class="px-4 py-2">
                            {{ $truck->is_active ? '✅Yes' : '❌No' }}
                        </td>
                          <td class="p-3 border text-center">
                            <a href="/trucks/{{ $truck->id }}" class="text-blue-600">👁️</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="text-center py-4 text-gray-500">No results</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Пагинация --}}
        <div class="mt-4">
            {{ $items->links() }}
        </div>
    </div>
</div>
