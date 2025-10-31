<div class="space-y-4">

    {{-- 🔍 Панель управления --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-3 rounded-xl shadow">
        {{-- Поиск --}}
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search name / document / type..."
                class="w-full sm:w-72 border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
            />
            @if($search)
                <button wire:click="$set('search','')" class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600">✖</button>
            @endif
        </div>

        {{-- ⚙️ Rows + Sort --}}
        <div class="flex items-center gap-4 text-sm flex-wrap sm:justify-end">
            {{-- Rows --}}
            <div class="flex items-center gap-2">
                <label class="text-gray-600 whitespace-nowrap">Rows:</label>
                <select wire:model.live="perPage"
                        class="border rounded-lg px-2 py-1 w-20 text-center bg-white focus:ring-1 focus:ring-blue-400 focus:outline-none">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            {{-- Sort --}}
            <div class="flex items-center gap-2">
                <label class="text-gray-600 whitespace-nowrap">Sort:</label>
                <select wire:model.live="sortField"
                        wire:change="sortBy($event.target.value)"
                        class="border rounded-lg px-2 py-1 w-36 bg-white focus:ring-1 focus:ring-blue-400 focus:outline-none">
                    <option value="expiry_date">Expiry date</option>
                    <option value="name">Name</option>
                    <option value="type">Type</option>
                    <option value="company">Company</option>
                </select>
            </div>
        </div>
    </div>

    {{-- 🖥️ Таблица --}}
    <div class="hidden md:block bg-white shadow rounded-xl overflow-x-auto">
        <table class="w-full border-collapse text-sm">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    @php
                        $cols = ['type'=>'Type','name'=>'Name','document'=>'Document','expiry_date'=>'Expiry date','company'=>'Company','status'=>'Status','is_active'=>'Active'];
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
                        $bg = $days < 0 ? 'bg-purple-100' : ($days <= 10 ? 'bg-red-100' : ($days <= 20 ? 'bg-orange-100' : ($days <= 30 ? 'bg-yellow-100' : '')));
                    @endphp
                    <tr class="{{ $bg }} hover:bg-gray-50 transition">
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
                    <tr><td colspan="8" class="text-center py-4 text-gray-500">Nothing found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 📱 Карточки --}}
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
