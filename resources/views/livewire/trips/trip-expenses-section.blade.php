{{-- resources/views/livewire/driver-app/driver-trip-expenses.blade.php --}}
<div class="bg-white dark:bg-gray-900 shadow rounded-2xl p-4 sm:p-6 space-y-6 transition-colors
            lg:pb-6 pb-24">

    {{-- HEADER --}}
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="text-lg sm:text-xl font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                💶 Izdevumi par reisu
            </h2>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                ID: {{ $trip->id }}
            </div>
        </div>

        {{-- Quick action (mobile) --}}
        <button type="button"
                class="lg:hidden inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-medium
                       bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100 active:scale-[0.98]"
                x-data
                @click="window.scrollTo({ top: 0, behavior: 'smooth' });">
            ➕
        </button>
    </div>

    {{-- SUCCESS --}}
    @if (session('success'))
        <div class="bg-green-50 dark:bg-green-900/30 border border-green-300 dark:border-green-800
                    text-green-800 dark:text-green-200 px-4 py-2 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- ERROR (optional) --}}
    @if (session('error'))
        <div class="bg-red-50 dark:bg-red-900/30 border border-red-300 dark:border-red-800
                    text-red-800 dark:text-red-200 px-4 py-2 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    @include('components.upload-loading-overlay', ['targets' => 'expenseFile,saveExpense'])

    {{-- ============================================================ --}}
    {{-- FORM --}}
    {{-- ============================================================ --}}
    <form wire:submit.prevent="saveExpense"
          enctype="multipart/form-data"
          class="space-y-4"
          x-data="{ fileUploading: false, cancelTimeout: null }"
          x-on:livewire-upload-start="fileUploading = true; if(cancelTimeout) { clearTimeout(cancelTimeout); cancelTimeout = null }"
          x-on:livewire-upload-finish="fileUploading = false; if(cancelTimeout) { clearTimeout(cancelTimeout); cancelTimeout = null }"
          x-on:livewire-upload-error="fileUploading = false; if(cancelTimeout) { clearTimeout(cancelTimeout); cancelTimeout = null }"
          x-on:livewire-upload-cancel="fileUploading = false; if(cancelTimeout) { clearTimeout(cancelTimeout); cancelTimeout = null }">

        {{-- Спиннер сразу при выборе файла (пока файл загружается) --}}
        <div x-show="fileUploading"
             x-cloak
             class="fixed inset-0 z-[200] flex items-center justify-center bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm"
             aria-live="polite">
            @include('components.upload-loading-spinner-box')
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="expenseFile,saveExpense"
                    x-bind:disabled="fileUploading"
                    class="flex-shrink-0 bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98]
                           text-white font-medium rounded-xl px-6 py-2 transition disabled:opacity-60 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="expenseFile,saveExpense">Pievienot</span>
                <span wire:loading wire:target="expenseFile,saveExpense" class="animate-pulse">⏳ {{ __('app.please_wait') }}</span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">

            {{-- Category --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategorija</label>
                <select wire:model.blur="category"
                        class="w-full border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                               text-gray-800 dark:text-gray-100 rounded-xl">
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
                       class="w-full border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                              text-gray-800 dark:text-gray-100 rounded-xl">
                @error('description') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Amount --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Summa (€)</label>
                <input type="number" wire:model.blur="amount" step="0.01"
                       class="w-full border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                              text-gray-800 dark:text-gray-100 rounded-xl">
                @error('amount') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Date --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Datums</label>
                <input type="date" wire:model.blur="expense_date"
                       class="w-full border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                              text-gray-800 dark:text-gray-100 rounded-xl">
                @error('expense_date') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- File --}}
            <div class="flex flex-col w-full">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Fails (nav obligāti)
                </label>
                <input type="file"
                       wire:model="expenseFile"
                       accept="image/*"
                       class="text-sm file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0
                              file:bg-indigo-50 dark:file:bg-indigo-900/40 file:text-indigo-700 dark:file:text-indigo-300"
                       x-on:click="fileUploading = true; if(cancelTimeout) clearTimeout(cancelTimeout); cancelTimeout = setTimeout(() => { fileUploading = false; cancelTimeout = null }, 15000)">
                <div wire:loading wire:target="expenseFile" class="text-xs text-gray-500 dark:text-gray-400 mt-1 inline-flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    {{ __('app.please_wait') }}
                </div>
                @error('expenseFile') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

        </div>
    </form>

    {{-- ============================================================ --}}
    {{-- FILTERS --}}
    {{-- ============================================================ --}}

    {{-- Mobile filters (collapse sheet) --}}
    <div class="lg:hidden"
         x-data="{ open: false }"
         x-on:expenses-open-filters.window="open = true">

        <button type="button"
                @click="open = !open"
                class="w-full bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100
                       px-4 py-3 rounded-2xl flex items-center justify-between font-semibold">
            🔎 Filtri
            <span class="text-xs" x-text="open ? '▲' : '▼'"></span>
        </button>

        <div x-show="open" x-collapse x-cloak class="mt-3 rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 space-y-3">
            {{-- Search --}}
            <div class="relative">
                <input type="text"
                       wire:model.live="search"
                       placeholder="🔍 Meklēt aprakstā, kategorijā..."
                       class="w-full border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                              rounded-xl px-3 py-3 pr-10 text-sm text-gray-800 dark:text-gray-100">

                @if($search)
                    <button type="button"
                            wire:click="$set('search', '')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        ✖
                    </button>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-3">
                {{-- Per page --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Rindas</label>
                    <select wire:model="perPage"
                            class="w-full border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                                   rounded-xl text-sm px-3 py-3 text-gray-800 dark:text-gray-100">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>

                {{-- Quick clear --}}
                <div class="flex items-end">
                    <button type="button"
                            @click="$wire.set('search','')"
                            class="w-full rounded-xl px-3 py-3 text-sm font-semibold
                                   bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100 active:scale-[0.98]">
                        Notīrīt
                    </button>
                </div>
            </div>

            <div class="text-xs text-gray-500 dark:text-gray-400">
                Padoms: “Notīrīt” iztīra meklēšanu. Kārtošana paliek (galdiņā uz datora).
            </div>
        </div>
    </div>

    {{-- Desktop filters (original) --}}
    <div class="hidden lg:flex flex-wrap justify-between items-center gap-3">

        {{-- Search --}}
        <div class="relative w-64">
            <input type="text"
                   wire:model.live="search"
                   placeholder="🔍 Meklēt aprakstā, kategorijā..."
                   class="w-full border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-xl px-3 py-2 pr-9 text-sm">

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
    {{-- LIST: cards on <lg, table on lg+ --}}
    {{-- ============================================================ --}}
    <div class="space-y-4">

        {{-- MOBILE / PWA: CARDS ( < lg ) --}}
        <div class="lg:hidden space-y-3">

            @forelse($this->filteredExpenses as $exp)

                @php
                    $url = $exp->file_url;
                    $ext = $url ? strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION)) : null;
                    $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp'], true);
                    $isPdf = $ext === 'pdf';

                    $categoryValue = $exp->category instanceof \BackedEnum
                        ? $exp->category->value
                        : (string) $exp->category;

                    $categoryLabel = method_exists($exp->category, 'label')
                        ? $exp->category->label()
                        : $categoryValue;

                    $isFuelOrAdblue = in_array($categoryValue, ['fuel', 'adblue'], true);

                    $liters = $exp->liters ?? null;
                    $odoKm  = $exp->odometer_km ?? null;
                    $odoSrc = $exp->odometer_source ?? null;

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
                @endphp

                <div class="rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
                    <div class="p-4 flex items-start gap-3">

                        {{-- File preview / icon --}}
                        <div class="shrink-0">
                            @if($url && $isImage)
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="block">
                                    <img src="{{ $url }}" class="w-14 h-14 rounded-xl object-cover border dark:border-gray-700" alt="Expense file">
                                </a>
                            @elseif($url && $isPdf)
                                <a href="{{ $url }}" target="_blank" rel="noopener"
                                   class="w-14 h-14 rounded-xl flex items-center justify-center
                                          bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800">
                                    <span class="text-red-600 dark:text-red-300 font-semibold text-sm">PDF</span>
                                </a>
                            @else
                                <div class="w-14 h-14 rounded-xl flex items-center justify-center
                                            bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                                    <span class="text-gray-500 dark:text-gray-300 text-xs">—</span>
                                </div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                                        €{{ number_format((float)$exp->amount, 2, ',', ' ') }}
                                    </div>

                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs">
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg border text-xs font-semibold {{ $catBadge }}">
                                            {{ $categoryLabel }}
                                        </span>

                                        <span class="text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                            {{ $exp->expense_date?->format('d.m.Y') ?? '—' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Details: liters/odo/description --}}
                            <div class="mt-3 text-sm text-gray-700 dark:text-gray-200 space-y-1">
                                @if($liters !== null)
                                    <div class="text-xs font-semibold">
                                        🧴 {{ number_format((float)$liters, 2, ',', ' ') }} L
                                    </div>
                                @endif

                                @if($isFuelOrAdblue && $odoKm !== null)
                                    <div class="text-xs font-semibold">
                                        ⛽ {{ number_format((float)$odoKm, 1, ',', ' ') }} km
                                        @if($odoSrc)
                                            <span class="text-[11px] text-gray-500 dark:text-gray-400 font-normal">
                                                ({{ $odoSrc }})
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                @if($exp->description)
                                    <div class="text-gray-600 dark:text-gray-300">
                                        {{ $exp->description }}
                                    </div>
                                @endif
                            </div>

                            {{-- open file --}}
                            @if($url)
                                <div class="mt-3">
                                    <a href="{{ $url }}" target="_blank" rel="noopener"
                                       class="inline-flex items-center justify-center rounded-xl px-3 py-2
                                              text-sm font-medium bg-gray-100 dark:bg-gray-800
                                              text-gray-800 dark:text-gray-100 active:scale-[0.98]">
                                        Atvērt failu
                                    </a>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>

            @empty
                <div class="rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/30 p-6 text-center">
                    <div class="text-gray-600 dark:text-gray-300 font-medium">Nav izdevumu</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pievieno pirmo izdevumu augšā.</div>
                </div>
            @endforelse
        </div>

        {{-- DESKTOP: TABLE ( lg+ ) --}}
        <div class="hidden lg:block">
            <div class="overflow-x-auto -mx-2 sm:mx-0">
                <table class="min-w-full border border-gray-200 dark:border-gray-700 text-sm">

                    <thead class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 uppercase text-xs">
                        <tr>
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
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">

                        @forelse($this->filteredExpenses as $exp)

                            @php
                                $url = $exp->file_url;
                                $ext = $url ? strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION)) : null;
                                $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp'], true);
                                $isPdf = $ext === 'pdf';

                                $categoryValue = $exp->category instanceof \BackedEnum
                                    ? $exp->category->value
                                    : (string) $exp->category;

                                $categoryLabel = method_exists($exp->category, 'label')
                                    ? $exp->category->label()
                                    : $categoryValue;

                                $isFuelOrAdblue = in_array($categoryValue, ['fuel', 'adblue'], true);

                                $liters = $exp->liters ?? null;
                                $odoKm  = $exp->odometer_km ?? null;
                                $odoSrc = $exp->odometer_source ?? null;

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
                            </tr>

                        @empty
                            <tr>
                                <td colspan="5"
                                    class="px-3 py-3 text-center text-gray-500 dark:text-gray-400">
                                    Nav izdevumu
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>
            </div>
        </div>

    </div>

    {{-- PAGINATION --}}
    <div class="mt-4">
        {{ $this->filteredExpenses->links() }}
    </div>

    {{-- TOTALS --}}
    <div class="mt-4 text-right text-sm font-semibold text-gray-700 dark:text-gray-200">
        @if($this->isFiltered)
            <div>
                Kopā (pēc filtrēšanas):
                €{{ number_format($this->filteredTotal, 2, ',', ' ') }}
            </div>
            <div class="text-gray-500 text-xs">
                Pilnā summa: €{{ number_format($this->totalAll, 2, ',', ' ') }}
            </div>
        @else
            <div>
                Kopējā summa:
                €{{ number_format($this->totalAll, 2, ',', ' ') }}
            </div>
        @endif
    </div>

    {{-- ✅ PWA bottom shortcut (mobile only) --}}
    <div class="lg:hidden fixed left-0 right-0 bottom-0 z-40 px-4 pb-[calc(env(safe-area-inset-bottom,0)+12px)] space-y-2">

        <button type="button"
                class="w-full rounded-2xl py-3 font-semibold bg-gray-100 dark:bg-gray-800
                       text-gray-800 dark:text-gray-100 shadow-lg active:scale-[0.99]"
                x-data
                @click="window.dispatchEvent(new CustomEvent('expenses-open-filters')); window.scrollTo({ top: 0, behavior: 'smooth' });">
            🔎 Filtri
        </button>

        <button type="button"
                class="w-full rounded-2xl py-3 font-semibold text-white bg-indigo-600 hover:bg-indigo-700
                       shadow-lg active:scale-[0.99]"
                x-data
                @click="window.scrollTo({ top: 0, behavior: 'smooth' });">
            ➕ Pievienot izdevumu
        </button>
    </div>

</div>
