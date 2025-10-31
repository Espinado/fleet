<div class="space-y-4">

    {{-- 🔍 Поиск и количество строк --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-3 rounded-xl shadow">
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search name / document / type..."
                class="w-full sm:w-72 border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm"
            />
            @if($search)
                <button wire:click="$set('search','')" class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600">✖</button>
            @endif
        </div>

        <div class="flex items-center gap-2 text-sm">
            <label for="perPage" class="text-gray-600">Rows:</label>
            <select id="perPage" wire:model.live="perPage" class="border rounded-lg px-2 py-1 focus:ring-1 focus:ring-blue-400 focus:outline-none">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>

    {{-- 🧾 Таблица для десктопа --}}
    <div class="hidden md:block bg-white shadow rounded-xl overflow-x-auto">
        <table class="w-full border-collapse text-sm">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    @php
                        $cols = [
                            'type' => 'Type',
                            'name' => 'Name',
                            'document' => 'Document',
                            'expiry_date' => 'Expiry date',
                            'company' => 'Company',
                            'status' => 'Status',
                            'is_active' => 'Active'
                        ];
                    @endphp
                    @foreach($cols as $field => $label)
                        <th class="p-3 text-left cursor-pointer select-none" wire:click="sortBy('{{ $field }}')">
                            {{ $label }}
                            @if($sortField === $field)
                                <span class="ml-1 text-xs text-gray-500">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
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
                        if ($days < 0) $rowClass = 'bg-purple-100';
                        elseif ($days <= 10) $rowClass = 'bg-red-100';
                        elseif ($days <= 20) $rowClass = 'bg-orange-100';
                        elseif ($days <= 30) $rowClass = 'bg-yellow-100';
                    @endphp

                    <tr class="{{ $rowClass }} hover:bg-gray-50 transition">
                        <td class="p-3 border">{{ $item->type }}</td>
                        <td class="p-3 border">{{ $item->name }}</td>
                        <td class="p-3 border">{{ $item->document }}</td>
                        <td class="p-3 border">
                            {{ $item->expiry_date->format('d.m.Y') }}
                            <span class="text-xs text-gray-600 ml-1">
                                @if($days < 0)
                                    (expired {{ abs($days) }}d)
                                @elseif($days === 0)
                                    (today)
                                @else
                                    (in {{ $days }}d)
                                @endif
                            </span>
                        </td>
                        <td class="p-3 border">{{ $item->company }}</td>
                        <td class="p-3 border">{{ $item->status }}</td>
                        <td class="p-3 border">{{ $item->is_active ? '✅' : '❌' }}</td>
                        <td class="p-3 border text-center">
                            <a href="/{{ strtolower($item->type) }}s/{{ $item->id }}" class="text-blue-600 hover:text-blue-800">👁️</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($cols)+1 }}" class="p-4 text-center text-gray-500">Nothing found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 📱 Мобильные карточки --}}
    <div class="grid md:hidden gap-3">
        @forelse($items as $item)
            @php
                $days = $item->days_left;
                $bg = $days < 0 ? 'bg-purple-100' : ($days <= 10 ? 'bg-red-100' : ($days <= 20 ? 'bg-orange-100' : ($days <= 30 ? 'bg-yellow-100' : 'bg-white')));
            @endphp

            <div class="rounded-xl shadow-sm border p-4 {{ $bg }}">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs uppercase text-gray-500">{{ $item->type }}</p>
                        <p class="font-semibold text-gray-800 leading-tight">{{ $item->name }}</p>
                        <p class="text-sm text-gray-600 mt-1">
                            <b>{{ $item->document }}</b> — {{ $item->expiry_date->format('d.m.Y') }}
                        </p>
                    </div>
                    <a href="/{{ strtolower($item->type) }}s/{{ $item->id }}" class="text-blue-600 text-lg">👁️</a>
                </div>
                <div class="mt-2 flex justify-between text-xs text-gray-600">
                    <span>{{ $item->company }}</span>
                    <span>
                        @if($days < 0)
                            expired {{ abs($days) }}d
                        @elseif($days === 0)
                            today
                        @else
                            in {{ $days }}d
                        @endif
                    </span>
                </div>
            </div>
        @empty
            <p class="text-center text-gray-500 py-4">Nothing found</p>
        @endforelse
    </div>

    {{-- 📄 Пагинация --}}
    <div class="flex items-center justify-between flex-wrap gap-2 mt-3">
        <div class="text-sm text-gray-600">
            Showing {{ $items->firstItem() ?? 0 }}–{{ $items->lastItem() ?? 0 }} of {{ $items->total() }}
        </div>
        <div class="text-sm">
            {{ $items->links() }}
        </div>
    </div>

</div>
