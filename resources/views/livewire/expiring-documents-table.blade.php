<div>
    <div class="flex items-center justify-between mb-4 gap-4">
        <div class="flex items-center gap-2">
            {{-- Если у вас Livewire v3, используйте: wire:model.live.debounce.300ms --}}
            {{-- Если у вас Livewire v2, используйте: wire:model.debounce.300ms --}}
            <input
                type="text"
                {{-- ВАЖНО: замените на нужный синтаксис в зависимости от версии Livewire --}}
                wire:model.live.debounce.300ms="search"
                {{-- wire:model.debounce.300ms="search" --}}
                placeholder="Поиск по имени / документу / типу..."
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

    <div class="overflow-x-auto bg-white shadow rounded">
        <table class="w-full border-collapse">
            <thead class="bg-gray-100">
                <tr>
                    @php
                        $cols = ['type'=>'Type','name'=>'Name','document'=>'Document','expiry_date'=>'Expiry date','status'=>'Status','is_active'=>'Active'];
                    @endphp

                    @foreach($cols as $field => $label)
                        <th class="p-3 text-left cursor-pointer" wire:click="sortBy('{{ $field }}')">
                            {{ $label }}
                            @if($sortField === $field)
                                <span class="ml-1 text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </th>
                    @endforeach

                    <th class="p-3 text-left">Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($items as $item)
                    @php
                        $days = $item->days_left;
                        $rowClass = '';
                        if ($days < 0) $rowClass = 'bg-purple-200';
                        elseif ($days <= 10) $rowClass = 'bg-red-200';
                        elseif ($days <= 20) $rowClass = 'bg-orange-200';
                        elseif ($days <= 30) $rowClass = 'bg-yellow-200';
                    @endphp

                    <tr class="{{ $rowClass }}">
                        <td class="p-3 border">{{ $item->type }}</td>
                        <td class="p-3 border">{{ $item->name }}</td>
                        <td class="p-3 border">{{ $item->document }}</td>
                        <td class="p-3 border">
                            {{ $item->expiry_date->format('d-m-Y') }}
                            @if($item->days_left < 0)
                                (expired {{ abs($item->days_left) }} days ago)
                            @elseif($item->days_left === 0)
                                (expires today)
                            @else
                                (in {{ $item->days_left }} days)
                            @endif
                        </td>
                        <td class="p-3 border">{{ $item->status }}</td>
                        <td class="p-3 border">{{ $item->is_active ? 'Yes' : 'No' }}</td>
                        <td class="p-3 border text-center">
                            <a href="/{{ strtolower($item->type) }}s/{{ $item->id }}" class="text-blue-600">👁️</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($cols)+1 }}" class="p-4 text-center text-gray-500">Ничего не найдено</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex items-center justify-between">
        <div class="text-sm text-gray-600">
            Показано {{ $items->firstItem() ?? 0 }}–{{ $items->lastItem() ?? 0 }} из {{ $items->total() }}
        </div>

        <div>
            {{ $items->links() }}
        </div>
    </div>
</div>
