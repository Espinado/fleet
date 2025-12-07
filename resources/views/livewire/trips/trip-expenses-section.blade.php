<div class="bg-white dark:bg-gray-900 shadow rounded-2xl p-4 sm:p-6 space-y-6 transition-colors">

    <div class="flex items-center justify-between">
        <h2 class="text-lg sm:text-xl font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
            💶 Izdevumi par reisu
        </h2>
        <span class="text-sm text-gray-500 dark:text-gray-400">ID: {{ $trip->id }}</span>
    </div>

    {{-- Уведомления --}}
    @if (session('success'))
        <div class="bg-green-50 dark:bg-green-900/30 border border-green-300 text-green-800 dark:text-green-200 px-4 py-2 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Форма --}}
    <form wire:submit.prevent="saveExpense"
      enctype="multipart/form-data"
      class="space-y-4">
        {{-- Верхняя панель с кнопкой справа --}}
        <div class="flex justify-end">
            <button type="submit"
                    class="flex-shrink-0 bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] text-white font-medium rounded-xl
                           px-6 py-2 transition">
                Pievienot
            </button>
        </div>

        {{-- Поля формы сеткой --}}
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategorija</label>
                <select wire:model="category"
                        class="w-full border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 rounded-xl focus:ring-indigo-500 focus:border-indigo-500">
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('category') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Apraksts</label>
                <input type="text" wire:model="description" placeholder="Piemēram: DUS Neste - Viļņa"
                       class="w-full border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 rounded-xl focus:ring-indigo-500 focus:border-indigo-500">
                @error('description') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Summa (€)</label>
                <input type="number" step="0.01" wire:model="amount"
                       class="w-full border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 rounded-xl focus:ring-indigo-500 focus:border-indigo-500">
                @error('amount') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Datums</label>
                <input type="date" wire:model="expense_date"
                       class="w-full border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 rounded-xl focus:ring-indigo-500 focus:border-indigo-500">
                @error('expense_date') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex flex-wrap items-center gap-3 w-full">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fails</label>
                   <input type="file"
       wire:model="expenseFile"
       accept="image/*,application/pdf"  class="block w-full text-sm text-gray-600 dark:text-gray-200
                                  file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0
                                  file:bg-indigo-50 dark:file:bg-indigo-900/40
                                  file:text-indigo-700 dark:file:text-indigo-300
                                  hover:file:bg-indigo-100 dark:hover:file:bg-indigo-800/50
                                  file:cursor-pointer">
                </div>
            </div>
        </div>
    </form>

    {{-- Таблица --}}
    <div class="overflow-x-auto -mx-2 sm:mx-0">
        <table class="min-w-full border border-gray-200 dark:border-gray-700 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 uppercase text-xs">
                <tr>
                    <th class="px-3 py-2 text-left">Datums</th>
                    <th class="px-3 py-2 text-left">Kategorija</th>
                    <th class="px-3 py-2 text-left">Apraksts</th>
                    <th class="px-3 py-2 text-right">Summa (€)</th>
                    <th class="px-3 py-2 text-left">Fails</th>
                    <th class="px-3 py-2 text-right">Darbības</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($expenses as $exp)
                    @php
                        $url = $exp->file_url;
                        $ext = $url ? strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION)) : null;
                        $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp']);
                        $isPdf = $ext === 'pdf';
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/70 transition">
                        <td class="px-3 py-2 text-gray-600 dark:text-gray-400">{{ $exp->expense_date?->format('d.m.Y') ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $exp->category->label() }}</td>
                        <td class="px-3 py-2">{{ $exp->description ?: '—' }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($exp->amount, 2, ',', ' ') }}</td>

                        {{-- ✅ Превью файла --}}
                        <td class="px-3 py-2">
                            @if($url)
                                @if($isPdf)
                                    <a href="{{ $url }}" target="_blank"
                                       class="inline-flex items-center gap-2 text-indigo-600 dark:text-indigo-400 hover:underline">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-500" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M6 2h7l5 5v15a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm7 1.5V8h4.5L13 3.5z"/>
                                        </svg>
                                        PDF
                                    </a>
                                @elseif($isImage)
                                    <a href="{{ $url }}" target="_blank" class="group inline-block">
                                        <img src="{{ $url }}" alt="Expense file"
                                             class="w-12 h-12 object-cover rounded-lg border border-gray-300 dark:border-gray-700
                                                    transition-transform group-hover:scale-105 shadow-sm">
                                    </a>
                                @else
                                    <a href="{{ $url }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                        Atvērt
                                    </a>
                                @endif
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>

                        <td class="px-3 py-2 text-right">
                            <button wire:click="delete({{ $exp->id }})"
                                    class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm">
                                Dzēst
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-3 text-center text-gray-500 dark:text-gray-400">Nav izdevumu</td>
                    </tr>
                @endforelse
            </tbody>

            @if($expenses->count())
                <tfoot class="bg-gray-50 dark:bg-gray-800 font-semibold text-gray-800 dark:text-gray-100">
                    <tr>
                        <td colspan="3" class="px-3 py-2 text-right">Kopā:</td>
                        <td class="px-3 py-2 text-right">{{ number_format($total, 2, ',', ' ') }} €</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
