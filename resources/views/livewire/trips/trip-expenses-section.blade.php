<div class="bg-white dark:bg-gray-900 shadow rounded-2xl p-4 sm:p-6 space-y-6 transition-colors">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <h2 class="text-lg sm:text-xl font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
            💶 Izdevumi par reisu
        </h2>
        <span class="text-sm text-gray-500 dark:text-gray-400">ID: {{ $trip->id }}</span>
    </div>

    {{-- SUCCESS --}}
    @if (session('success'))
        <div class="bg-green-50 dark:bg-green-900/30 border border-green-300 text-green-800 dark:text-green-200 px-4 py-2 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif


    {{-- ============================================================ --}}
    {{-- Форма добавления расхода --}}
    {{-- ============================================================ --}}
    <form wire:submit.prevent="saveExpense"
          enctype="multipart/form-data"
          class="space-y-4">

        <div class="flex justify-end">
            <button type="submit"
                    class="flex-shrink-0 bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] text-white font-medium rounded-xl
                           px-6 py-2 transition">
                Pievienot
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">

            {{-- Category --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategorija</label>
                <select wire:model="category"
                        class="w-full border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 rounded-xl">
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('category') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Apraksts</label>
                <input type="text"
                       wire:model="description"
                       class="w-full border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 rounded-xl">
                @error('description') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Amount --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Summa (€)</label>
                <input type="number" wire:model="amount" step="0.01"
                       class="w-full border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 rounded-xl">
                @error('amount') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Date --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Datums</label>
                <input type="date" wire:model="expense_date"
                       class="w-full border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 rounded-xl">
                @error('expense_date') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- File --}}
            <div class="flex flex-col w-full">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Fails (nav obligāti)
                </label>
                <input type="file"
                       wire:model="expenseFile"
                       accept="image/*,application/pdf"
                       class="text-sm file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0
                              file:bg-indigo-50 dark:file:bg-indigo-900/40 file:text-indigo-700 dark:file:text-indigo-300">
                @error('expenseFile') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

        </div>
    </form>



    {{-- ============================================================ --}}
    {{-- Фильтры таблицы --}}
    {{-- ============================================================ --}}
    <div class="flex flex-wrap justify-between items-center gap-3">

        {{-- Search --}}
        <div class="relative w-64">
    <input type="text"
           wire:model.live="search"
           placeholder="🔍 Meklēt aprakstā, kategorijā..."
           class="w-full border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-xl px-3 py-2 pr-9 text-sm">

    {{-- Крестик для сброса --}}
    @if($search)
        <button type="button"
                wire:click="$set('search', '')"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            ✖
        </button>
    @endif
</div>

        {{-- Per page --}}
        <select wire:model="perPage"
                class="border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-xl text-sm px-3 py-2">
            <option value="10">10 rindas</option>
            <option value="25">25 rindas</option>
            <option value="50">50 rindas</option>
        </select>
    </div>



    {{-- ============================================================ --}}
    {{-- Таблица расходов --}}
    {{-- ============================================================ --}}
   <div class="overflow-x-auto -mx-2 sm:mx-0">
    <table class="min-w-full border border-gray-200 dark:border-gray-700 text-sm">

        <thead class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 uppercase text-xs">
            <tr>
                {{-- SORTABLE HEADERS --}}
                <th class="px-3 py-2 cursor-pointer" wire:click="sortBy('expense_date')">
                    Datums
                    @if($sortField === 'expense_date')
                        {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                    @endif
                </th>

                <th class="px-3 py-2 cursor-pointer" wire:click="sortBy('category')">
                    Kategorija
                    @if($sortField === 'category')
                        {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                    @endif
                </th>

                {{-- вместо Apraksts: литры/одометр/описание --}}
                <th class="px-3 py-2 cursor-pointer" wire:click="sortBy('description')">
                    Detalizēti
                    @if($sortField === 'description')
                        {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                    @endif
                </th>

                <th class="px-3 py-2 text-right cursor-pointer" wire:click="sortBy('amount')">
                    Summa (€)
                    @if($sortField === 'amount')
                        {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                    @endif
                </th>

                <th class="px-3 py-2">Fails</th>
                <th class="px-3 py-2 text-right">Darbības</th>
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">

            @forelse($this->filteredExpenses as $exp)

                @php
                    // file preview
                    $url = $exp->file_url;
                    $ext = $url ? strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION)) : null;
                    $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp'], true);
                    $isPdf = $ext === 'pdf';

                    // category safe value (enum/string)
                    $categoryValue = $exp->category instanceof \BackedEnum
                        ? $exp->category->value
                        : (string) $exp->category;

                    $categoryLabel = method_exists($exp->category, 'label')
                        ? $exp->category->label()
                        : $categoryValue;

                    // flags
                    $isFuelOrAdblue = in_array($categoryValue, ['fuel', 'adblue'], true);

                    // data
                    $liters = $exp->liters ?? null;
                    $odoKm  = $exp->odometer_km ?? null;
                    $odoSrc = $exp->odometer_source ?? null;

                    // colors by category
                    $catBadge = match ($categoryValue) {
                        'fuel'         => 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-900/20 dark:text-amber-200 dark:border-amber-800',
                        'adblue'       => 'bg-sky-50 text-sky-800 border-sky-200 dark:bg-sky-900/20 dark:text-sky-200 dark:border-sky-800',
                        'washer_fluid' => 'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-900/20 dark:text-blue-200 dark:border-blue-800',
                        'parking'      => 'bg-purple-50 text-purple-800 border-purple-200 dark:bg-purple-900/20 dark:text-purple-200 dark:border-purple-800',
                        'toll'         => 'bg-indigo-50 text-indigo-800 border-indigo-200 dark:bg-indigo-900/20 dark:text-indigo-200 dark:border-indigo-800',
                        'hotel'        => 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-200 dark:border-emerald-800',
                        'food'         => 'bg-lime-50 text-lime-800 border-lime-200 dark:bg-lime-900/20 dark:text-lime-200 dark:border-lime-800',
                        default        => 'bg-gray-50 text-gray-800 border-gray-200 dark:bg-gray-800/40 dark:text-gray-200 dark:border-gray-700',
                    };

                    $litersColor = match ($categoryValue) {
                        'fuel'         => 'text-amber-700 dark:text-amber-200',
                        'adblue'       => 'text-sky-700 dark:text-sky-200',
                        'washer_fluid' => 'text-blue-700 dark:text-blue-200',
                        default        => 'text-gray-700 dark:text-gray-200',
                    };

                    $odoColor = match ($categoryValue) {
                        'fuel'   => 'text-amber-700 dark:text-amber-200',
                        'adblue' => 'text-sky-700 dark:text-sky-200',
                        default  => 'text-gray-700 dark:text-gray-200',
                    };
                @endphp

                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/70 transition">

                    <td class="px-3 py-2 whitespace-nowrap">
                        {{ $exp->expense_date?->format('d.m.Y') ?? '—' }}
                    </td>

                    <td class="px-3 py-2">
                        <span class="inline-flex items-center px-2 py-1 rounded-lg border text-xs font-semibold {{ $catBadge }}">
                            {{ $categoryLabel }}
                        </span>
                    </td>

                    {{-- литры + одометр (только fuel/adblue) + fallback description --}}
                    <td class="px-3 py-2">
                        <div class="space-y-1">

                            @if($liters !== null)
                                <div class="text-xs font-semibold {{ $litersColor }}">
                                    🧴 {{ number_format((float)$liters, 2, ',', ' ') }} L
                                </div>
                            @endif

                            @if($isFuelOrAdblue && $odoKm !== null)
                                <div class="text-xs font-semibold {{ $odoColor }}">
                                    ⛽ {{ number_format((float)$odoKm, 1, ',', ' ') }} km
                                    @if($odoSrc)
                                        <span class="text-[11px] text-gray-500 dark:text-gray-400 font-normal">
                                            ({{ $odoSrc }})
                                        </span>
                                    @endif
                                </div>
                            @endif

                            @if($liters === null && (!$isFuelOrAdblue || $odoKm === null))
                                <div class="text-gray-600 dark:text-gray-300">
                                    {{ $exp->description ?: '—' }}
                                </div>
                            @endif

                        </div>
                    </td>

                    <td class="px-3 py-2 text-right whitespace-nowrap">
                        €{{ number_format((float)$exp->amount, 2, ',', ' ') }}
                    </td>

                    {{-- FILE PREVIEW --}}
                    <td class="px-3 py-2">
                        @if($url)
                            @if($isPdf)
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="text-red-600 dark:text-red-400 font-semibold">PDF</a>
                            @elseif($isImage)
                                <a href="{{ $url }}" target="_blank" rel="noopener">
                                    <img src="{{ $url }}" class="w-12 h-12 rounded-lg object-cover border dark:border-gray-700" alt="Expense file">
                                </a>
                            @else
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="text-indigo-600 dark:text-indigo-300 underline">Atvērt</a>
                            @endif
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>

                    <td class="px-3 py-2 text-right whitespace-nowrap">
                        <button
                            type="button"
                            wire:click="delete({{ $exp->id }})"
                            class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm font-semibold"
                        >
                            Dzēst
                        </button>
                    </td>

                </tr>

            @empty
                <tr>
                    <td colspan="6"
                        class="px-3 py-3 text-center text-gray-500 dark:text-gray-400">
                        Nav izdevumu
                    </td>
                </tr>
            @endforelse

        </tbody>

    </table>
</div>


    {{-- PAGINATION --}}
    <div class="mt-4">
        {{ $this->filteredExpenses->links() }}
    </div>
    {{-- TOTALS --}}
<div class="mt-4 text-right text-sm font-semibold text-gray-700 dark:text-gray-200">

    @if($this->isFiltered)
        {{-- Когда поиск/фильтр активны --}}
        <div>
            Kopā (pēc filtrēšanas):
            €{{ number_format($this->filteredTotal, 2, ',', ' ') }}
        </div>
        <div class="text-gray-500 text-xs">
            Pilnā summa: €{{ number_format($this->totalAll, 2, ',', ' ') }}
        </div>
    @else
        {{-- Когда фильтров нет --}}
        <div>
            Kopējā summa:
            €{{ number_format($this->totalAll, 2, ',', ' ') }}
        </div>
    @endif

</div>


</div>
