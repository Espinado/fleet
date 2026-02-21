{{-- resources/views/livewire/driver-app/driver-trip-expenses.blade.php --}}
<div x-data="{ open: false, openList: false }" class="space-y-4">

    {{-- Заголовок --}}
    <button
        type="button"
        @click="open = !open"
        class="w-full bg-yellow-100 px-4 py-3 rounded-xl flex items-center justify-between font-semibold"
    >
        💶 Pievienot izdevumu
        <span x-text="open ? '▲' : '▼'" class="text-xs"></span>
    </button>

    {{-- Форма --}}
    <div x-show="open" x-collapse x-cloak class="bg-white rounded-xl p-4 space-y-4 shadow">

        @if (session('success'))
            <div class="bg-green-50 border border-green-300 text-green-700 px-3 py-2 rounded-lg text-xs">
                {{ session('success') }}
            </div>
        @endif

        <form wire:submit.prevent="saveExpense" class="space-y-3">

            {{-- Категория --}}
            <div>
                <label class="text-xs font-semibold">Kategorija</label>
                <select
                    wire:model.live="category"
                    class="w-full border-gray-300 rounded-lg text-sm p-2 bg-white"
                >
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Описание --}}
            <div>
                <label class="text-xs font-semibold">Apraksts</label>
                <input
                    type="text"
                    wire:model.live="description"
                    class="w-full border-gray-300 rounded-lg text-sm p-2"
                >
            </div>

            {{-- Сумма --}}
            <div>
                <label class="text-xs font-semibold">Summa (€)</label>
                <input
                    type="number"
                    step="0.01"
                    wire:model.live="amount"
                    class="w-full border-gray-300 rounded-lg text-sm p-2"
                >
            </div>

            {{-- ✅ Mapon odometer (только Fuel) --}}
            <div x-show="$wire.category === 'fuel'" x-cloak class="space-y-2">
                @error('mapon')
                    <div class="bg-red-50 border border-red-300 text-red-700 px-3 py-2 rounded-lg text-xs">
                        {{ $message }}
                    </div>
                @enderror

                <button
                    type="button"
                    wire:click="fetchOdometerFromMapon"
                    wire:target="fetchOdometerFromMapon"
                    wire:loading.attr="disabled"
                    class="w-full bg-gray-900 hover:bg-black text-white py-2 rounded-lg font-semibold text-sm
                           disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="fetchOdometerFromMapon">📡 Взять одометр из Mapon</span>
                    <span wire:loading wire:target="fetchOdometerFromMapon">⏳ Получаем одометр…</span>
                </button>

                @if($maponOdometerKm !== null)
                    <div class="p-3 rounded-xl bg-gray-50 border text-sm">
                        <div class="font-semibold">
                            Odometrs: {{ number_format($maponOdometerKm, 1) }} km
                        </div>
                        <div class="text-xs text-gray-600">
                            Source: {{ $maponOdometerSource ?? '—' }}
                            @if($maponAt) • Mapon at: {{ $maponAt }} @endif
                            @if($maponIsStale && $maponStaleMinutes) • ⚠️ stale {{ $maponStaleMinutes }} min @endif
                        </div>
                    </div>
                @endif
            </div>

            {{-- Дата --}}
            <div>
                <label class="text-xs font-semibold">Datums</label>
                <input
                    type="date"
                    wire:model.live="expense_date"
                    class="w-full border-gray-300 rounded-lg text-sm p-2"
                >
            </div>

            {{-- Файл --}}
            <div>
                <label class="text-xs font-semibold">Fails (nav obligāti)</label>
                <input
                    type="file"
                    wire:model="file"
                    accept="image/*,application/pdf"
                    capture="environment"
                    class="text-sm"
                >
                <div wire:loading wire:target="file" class="text-xs text-gray-500 mt-1">
                    ⏳ Augšupielāde...
                </div>
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="saveExpense,file"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg font-semibold text-sm
                       disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span wire:loading.remove wire:target="saveExpense,file">💾 Saglabāt</span>
                <span wire:loading wire:target="saveExpense,file">⏳ Saglabā...</span>
            </button>

        </form>
    </div>

    {{-- Кнопка показать список --}}
    <button
        type="button"
        @click="openList = !openList"
        class="w-full bg-gray-100 px-4 py-3 rounded-xl flex items-center justify-between font-semibold"
    >
        📁 Izdevumu saraksts ({{ $expenses->count() }})
        <span x-text="openList ? '▲' : '▼'" class="text-xs"></span>
    </button>

    {{-- Список --}}
    <div x-show="openList" x-collapse x-cloak class="bg-white rounded-xl p-4 shadow">
        @forelse($expenses as $exp)

            @php
                $url = $exp->file_url;
                $ext = $url ? strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION)) : null;
                $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp']);
                $isPdf = $ext === 'pdf';
            @endphp

            <div class="flex items-start justify-between gap-3 py-3 border-b last:border-b-0">

                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-sm truncate">
                        {{ $exp->category->label() }} — €{{ number_format($exp->amount, 2) }}
                    </div>

                    <div class="text-xs text-gray-500 mt-1">
                        {{ $exp->expense_date?->format('d.m.Y') ?? '—' }}
                        @if($exp->description)
                            • {{ $exp->description }}
                        @endif
                    </div>

                    {{-- Вариант A: odometer_km НЕ в trip_expenses — поэтому этот блок убран --}}
                </div>

                <div class="w-14 h-14 flex items-center justify-center bg-gray-100 rounded-lg overflow-hidden shrink-0">
                    @if ($isPdf)
                        <a href="{{ $url }}" target="_blank" class="text-red-600 font-bold text-sm">PDF</a>
                    @elseif ($isImage)
                        <a href="{{ $url }}" target="_blank" class="block">
                            <img src="{{ $url }}" class="w-14 h-14 object-cover" alt="Expense file">
                        </a>
                    @else
                        <span class="text-gray-400 text-xs">Nav faila</span>
                    @endif
                </div>

            </div>

        @empty
            <div class="text-sm text-gray-500">
                Nav izdevumu
            </div>
        @endforelse

        @if($expenses->count())
            <div class="font-semibold text-right mt-3">
                Kopā: €{{ number_format($total, 2) }}
            </div>
        @endif
    </div>

</div>
