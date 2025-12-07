<div x-data="{ open: false, openList: false }" class="space-y-4">

    {{-- Заголовок --}}
    <button @click="open = !open"
            class="w-full bg-yellow-100 px-4 py-3 rounded-xl flex items-center justify-between font-semibold">
        💶 Pievienot izdevumu
        <span x-text="open ? '▲' : '▼'" class="text-xs"></span>
    </button>

    {{-- Форма --}}
    <div x-show="open" x-collapse class="bg-white rounded-xl p-4 space-y-4 shadow">

        @if (session('success'))
            <div class="bg-green-50 border border-green-300 text-green-700 px-3 py-2 rounded-lg text-xs">
                {{ session('success') }}
            </div>
        @endif

        <form wire:submit.prevent="saveExpense" class="space-y-3">

            {{-- Категория --}}
            <div>
                <label class="text-xs font-semibold">Kategorija</label>
                <select wire:model="category"
                        class="w-full border-gray-300 rounded-lg text-sm p-2 bg-white">
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Описание --}}
            <div>
                <label class="text-xs font-semibold">Apraksts</label>
                <input type="text"
                       wire:model="description"
                       class="w-full border-gray-300 rounded-lg text-sm p-2">
            </div>

            {{-- Сумма --}}
            <div>
                <label class="text-xs font-semibold">Summa (€)</label>
                <input type="number" step="0.01"
                       wire:model="amount"
                       class="w-full border-gray-300 rounded-lg text-sm p-2">
            </div>

            {{-- Дата --}}
            <div>
                <label class="text-xs font-semibold">Datums</label>
                <input type="date"
                       wire:model="expense_date"
                       class="w-full border-gray-300 rounded-lg text-sm p-2">
            </div>

            {{-- Файл --}}
            <div>
                <label class="text-xs font-semibold">Fails (nav obligāti)</label>
                <input type="file"
                       wire:model="file"
                       accept="image/*,application/pdf"
                       capture="environment"
                       class="text-sm">
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg font-semibold text-sm">
                💾 Saglabāt
            </button>

        </form>
    </div>


    {{-- Кнопка показать список --}}
    <button @click="openList = !openList"
            class="w-full bg-gray-100 px-4 py-3 rounded-xl flex items-center justify-between font-semibold">
        📁 Izdevumu saraksts ({{ $expenses->count() }})
        <span x-text="openList ? '▲' : '▼'" class="text-xs"></span>
    </button>

    {{-- Таблица --}}
    <div x-show="openList" x-collapse class="bg-white rounded-xl p-4 shadow">

        @foreach($expenses as $exp)

            @php
                $url = $exp->file_url;
                $ext = $url ? strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION)) : null;
                $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp']);
                $isPdf = $ext === 'pdf';
            @endphp

            <div class="flex items-center justify-between py-2 border-b">
                <div class="flex-1">
                    <div class="font-semibold text-sm">
                        {{ $exp->category->label() }} — €{{ number_format($exp->amount, 2) }}
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ $exp->expense_date->format('d.m.Y') }} • {{ $exp->description ?: '—' }}
                    </div>
                </div>

                <div class="w-14 h-14 flex items-center justify-center bg-gray-100 rounded-lg overflow-hidden">

                    @if ($isPdf)
                        <a href="{{ $url }}" target="_blank" class="text-red-600 font-bold">PDF</a>

                    @elseif ($isImage)
                        <a href="{{ $url }}" target="_blank">
                            <img src="{{ $url }}" class="w-14 h-14 object-cover">
                        </a>

                    @else
                        <span class="text-gray-400 text-xs">Nav faila</span>
                    @endif

                </div>

                
            </div>

        @endforeach

        @if($expenses->count())
            <div class="font-semibold text-right mt-3">
                Kopā: €{{ number_format($total, 2) }}
            </div>
        @endif
    </div>

</div>
