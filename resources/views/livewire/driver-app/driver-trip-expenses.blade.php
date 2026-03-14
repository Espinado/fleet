{{-- resources/views/livewire/driver-app/driver-trip-expenses.blade.php --}}
@php
    $litersCategories = [
        \App\Enums\TripExpenseCategory::FUEL->value,
        \App\Enums\TripExpenseCategory::ADBLUE->value,
        \App\Enums\TripExpenseCategory::WASHER_FLUID->value,
    ];

    $odometerCategories = [
        \App\Enums\TripExpenseCategory::FUEL->value,
        \App\Enums\TripExpenseCategory::ADBLUE->value,
    ];
@endphp

<div x-data="{ open: false, openList: false }" class="space-y-4">

    {{-- Заголовок --}}
    <button
        type="button"
        @click="open = !open"
        class="w-full bg-yellow-100 px-4 py-3 rounded-xl flex items-center justify-between font-semibold"
    >
        💶 {{ __('app.driver.expenses.add_title') }}
        <span x-text="open ? '▲' : '▼'" class="text-xs"></span>
    </button>

    {{-- Форма --}}
    <div x-show="open" x-collapse x-cloak class="bg-white rounded-xl p-4 space-y-4 shadow">

        @if (session('success'))
            <div class="bg-green-50 border border-green-300 text-green-700 px-3 py-2 rounded-lg text-xs">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border border-red-300 text-red-700 px-3 py-2 rounded-lg text-xs">
                {{ session('error') }}
            </div>
        @endif

        @error('save')
            <div class="bg-red-50 border border-red-300 text-red-700 px-3 py-2 rounded-lg text-xs">
                {{ $message }}
            </div>
        @enderror

        @include('components.upload-loading-overlay', ['targets' => 'file,saveExpense'])

        <form wire:submit.prevent="saveExpense"
             class="space-y-3"
x-data="{ fileUploading: false }"
     x-on:livewire-upload-start="fileUploading = true"
     x-on:livewire-upload-finish="fileUploading = false"
     x-on:livewire-upload-error="fileUploading = false"
     x-on:livewire-upload-cancel="fileUploading = false">

            {{-- Спиннер только во время загрузки файла на сервер (после выбора файла) --}}
            <div x-show="fileUploading"
                 x-cloak
                 class="fixed inset-0 z-[200] flex items-center justify-center bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm"
                 aria-live="polite">
                @include('components.upload-loading-spinner-box')
            </div>

            {{-- Категория --}}
            <div>
                <label class="text-xs font-semibold">{{ __('app.driver.expenses.category') }}</label>
                <select
                    wire:model.live="category"
                    class="w-full border-gray-300 rounded-lg text-sm p-2 bg-white"
                >
                    <option value="">{{ __('app.driver.expenses.category_choose') }}</option>

                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>

                {{-- ✅ debug (Livewire variable) --}}
                

                @error('category')
                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Описание --}}
            <div>
                <label class="text-xs font-semibold">{{ __('app.driver.expenses.description') }}</label>
                <input
                    type="text"
                    wire:model.live="description"
                    class="w-full border-gray-300 rounded-lg text-sm p-2"
                >
                @error('description')
                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Сумма (необязательно) --}}
            <div>
                <label class="text-xs font-semibold">{{ __('app.driver.expenses.amount') }} <span class="text-gray-500 font-normal">({{ __('app.driver.expenses.optional') }})</span></label>
                <input
                    type="number"
                    step="0.01"
                    inputmode="decimal"
                    wire:model.live="amount"
                    class="w-full border-gray-300 rounded-lg text-sm p-2"
                    placeholder="{{ __('app.driver.expenses.amount_placeholder') }}"
                >
                @error('amount')
                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Перегрузка (необязательно) --}}
            <div>
                <label class="text-xs font-semibold">{{ __('app.driver.expenses.overload') }} <span class="text-gray-500 font-normal">({{ __('app.driver.expenses.optional') }})</span></label>
                <input
                    type="text"
                    wire:model.live="overload_note"
                    class="w-full border-gray-300 rounded-lg text-sm p-2"
                    placeholder="{{ __('app.driver.expenses.overload_placeholder') }}"
                >
                @error('overload_note')
                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- ✅ Litri (fuel/adblue/washer_fluid) --}}
            @if(in_array($category, $litersCategories, true))
                <div class="space-y-2">
                    @error('liters')
                        <div class="bg-red-50 border border-red-300 text-red-700 px-3 py-2 rounded-lg text-xs">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="p-3 rounded-xl bg-blue-50 border border-blue-200">
                        <div class="text-xs text-blue-900 font-semibold mb-2">
                            🧴 {{ __('app.driver.expenses.liters_title') }}
                        </div>

                        <label class="text-xs font-semibold">{{ __('app.driver.expenses.liters_label') }}</label>
                        <input
                            type="number"
                            step="0.01"
                            inputmode="decimal"
                            wire:model.live="liters"
                            class="w-full border-gray-300 rounded-lg text-sm p-2 bg-white"
                            placeholder="{{ __('app.driver.expenses.liters_placeholder') }}"
                        >

                        <div class="text-[11px] text-gray-600 mt-1">
                            {{ __('app.driver.expenses.liters_hint') }}
                        </div>
                    </div>
                </div>
            @endif

            {{-- ✅ ОДОМЕТР (fuel + adblue) --}}
            @if(in_array($category, $odometerCategories, true))
                <div class="space-y-2">
                    @error('manualOdometerKm')
                        <div class="bg-red-50 border border-red-300 text-red-700 px-3 py-2 rounded-lg text-xs">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="p-3 rounded-xl bg-yellow-50 border border-yellow-200">
                        <div class="text-xs text-yellow-900 font-semibold mb-2">
                            ⛽ {{ __('app.driver.expenses.odo_title') }}
                        </div>

                        <label class="text-xs font-semibold">{{ __('app.driver.expenses.odo_label') }}</label>
                        <input
                            type="number"
                            step="0.1"
                            inputmode="decimal"
                            wire:model.live="manualOdometerKm"
                            class="w-full border-gray-300 rounded-lg text-sm p-2 bg-white"
                            placeholder="{{ __('app.driver.expenses.odo_placeholder') }}"
                        >

                        <div class="text-[11px] text-gray-600 mt-1">
                            {{ __('app.driver.expenses.odo_hint') }}
                        </div>
                    </div>
                </div>
            @endif

            {{-- Дата --}}
            <div>
                <label class="text-xs font-semibold">{{ __('app.driver.expenses.date') }}</label>
                <input
                    type="date"
                    wire:model.live="expense_date"
                    class="w-full border-gray-300 rounded-lg text-sm p-2"
                >
                @error('expense_date')
                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Файл (клиентское сжатие изображений перед загрузкой) --}}
            <div>
                <label class="text-xs font-semibold">{{ __('app.driver.expenses.file') }}</label>
                <input
                    type="file"
                    wire:model="file"
                    accept="image/*"
                    capture="environment"
                    class="text-sm"
                >
                @if($file ?? null)
                    <p class="text-xs text-gray-600 mt-1">📄 {{ $file->getClientOriginalName() }}</p>
                @endif
                <div wire:loading wire:target="file" class="text-xs text-gray-500 mt-1">{{ __('app.please_wait') }}</div>
                @error('file')
                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="saveExpense,file"
                x-bind:disabled="fileUploading"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg font-semibold text-sm
                       disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span wire:loading.remove wire:target="saveExpense,file">💾 {{ __('app.driver.expenses.save') }}</span>
                <span wire:loading wire:target="saveExpense,file">⏳ {{ __('app.driver.expenses.saving') }}</span>
            </button>

        </form>
    </div>

    {{-- Список --}}
    <button
        type="button"
        @click="openList = !openList"
        class="w-full bg-gray-100 px-4 py-3 rounded-xl flex items-center justify-between font-semibold"
    >
        📁 {{ __('app.driver.expenses.list_title') }} ({{ $expenses->count() }})
        <span x-text="openList ? '▲' : '▼'" class="text-xs"></span>
    </button>

    <div x-show="openList" x-collapse x-cloak class="bg-white rounded-xl p-4 shadow">
        @forelse($expenses as $exp)

            @php
                $url = $exp->file_url;
                $ext = $url ? strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION)) : null;
                $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp'], true);
                $isPdf = $ext === 'pdf';

                $categoryValue = $exp->category instanceof \BackedEnum ? $exp->category->value : (string) $exp->category;
                $categoryLabel = method_exists($exp->category, 'label') ? $exp->category->label() : $categoryValue;

                $isFuelOrAdblue = in_array($categoryValue, ['fuel','adblue'], true);

                $odoKm = $exp->odometer_km ?? null;
                $odoSrc = $exp->odometer_source ?? null;

                $liters = $exp->liters ?? null;
            @endphp

            <div class="flex items-start justify-between gap-3 py-3 border-b last:border-b-0">

                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-sm truncate">
                        {{ $categoryLabel }} — €{{ number_format((float)$exp->amount, 2) }}
                    </div>

                    <div class="text-xs text-gray-500 mt-1">
                        {{ $exp->expense_date?->format('d.m.Y') ?? '—' }}
                        @if($exp->description)
                            • {{ $exp->description }}
                        @endif
                        @if(!empty($exp->overload_note))
                            <span class="block mt-0.5 text-gray-600">📦 {{ __('app.driver.expenses.overload_short') }}: {{ $exp->overload_note }}</span>
                        @endif
                    </div>

                    @if($liters !== null)
                        <div class="text-xs text-gray-700 mt-1">
                            🧴 {{ __('app.driver.expenses.liters_short') }}: <span class="font-semibold">{{ number_format((float)$liters, 2) }}</span>
                        </div>
                    @endif

                    @if($isFuelOrAdblue && $odoKm !== null)
                        <div class="text-xs text-gray-700 mt-1">
                            ⛽ {{ __('app.driver.expenses.odo_short') }}: <span class="font-semibold">{{ number_format((float)$odoKm, 1) }}</span> km
                            @if($odoSrc)
                                <span class="text-gray-500">({{ $odoSrc }})</span>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="w-14 h-14 flex items-center justify-center bg-gray-100 rounded-lg overflow-hidden shrink-0">
                    @if ($url && $isPdf)
                        <a href="{{ $url }}" target="_blank" rel="noopener" class="text-red-600 font-bold text-sm">
                            {{ __('app.driver.expenses.file_pdf') }}
                        </a>
                    @elseif ($url && $isImage)
                        <a href="{{ $url }}" target="_blank" rel="noopener" class="block">
                            <img src="{{ $url }}" class="w-14 h-14 object-cover" alt="Expense file">
                        </a>
                    @elseif ($url)
                        <a href="{{ $url }}" target="_blank" rel="noopener" class="text-gray-700 font-semibold text-xs">
                            {{ __('app.driver.expenses.file_other') }}
                        </a>
                    @else
                        <span class="text-gray-400 text-xs">{{ __('app.driver.expenses.file_none') }}</span>
                    @endif
                </div>

            </div>

        @empty
            <div class="text-sm text-gray-500">
                {{ __('app.driver.expenses.no_expenses') }}
            </div>
        @endforelse

        @if($expenses->count())
            <div class="font-semibold text-right mt-3">
                {{ __('app.driver.expenses.total') }}: €{{ number_format((float)$total, 2) }}
            </div>
        @endif
    </div>

</div>
