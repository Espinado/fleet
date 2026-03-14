{{-- resources/views/livewire/driver-app/trip-documents.blade.php --}}
<div class="bg-white dark:bg-gray-900 shadow rounded-2xl p-4 sm:p-6 space-y-6 transition-colors
            lg:pb-6 pb-24">

    {{-- Заголовок --}}
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="text-lg sm:text-xl font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                📁 Dokumenti par reisu
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

    {{-- Уведомления --}}
    @if (session('success'))
        <div class="bg-green-50 dark:bg-green-900/30 border border-green-300 dark:border-green-800
                    text-green-800 dark:text-green-200 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 dark:bg-red-900/30 border border-red-300 dark:border-red-800
                    text-red-800 dark:text-red-200 px-4 py-3 rounded-xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    @include('components.upload-loading-overlay', ['targets' => 'documentFile,saveDocument'])

    {{-- Форма: блокируем кнопку на время загрузки файла (livewire-upload-*), чтобы срабатывало с первого клика --}}
    <form wire:submit.prevent="saveDocument"
          enctype="multipart/form-data"
          wire:key="upload-form-{{ $trip->id }}"
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
                    wire:target="documentFile,saveDocument"
                    x-bind:disabled="fileUploading"
                    class="flex-shrink-0 bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98]
                           text-white font-medium rounded-xl px-6 py-2 transition
                           disabled:opacity-60 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="documentFile,saveDocument">
                    Augšupielādēt
                </span>
                <span wire:loading wire:target="documentFile,saveDocument" class="animate-pulse">
                    ⏳ Augšupielāde...
                </span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">

            {{-- Тип документа --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Dokumenta tips
                </label>

                <select wire:model.blur="type"
                        class="w-full border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                               text-gray-800 dark:text-gray-100 rounded-xl focus:ring-indigo-500 focus:border-indigo-500">

                    {{-- TRIP DOCUMENTS --}}
                    @if(isset($types['trip']))
                        <optgroup label="📁 Dokumenti par reisu">
                            @foreach ($types['trip'] as $enum)
                                <option value="{{ $enum->value }}">{{ $enum->label() }}</option>
                            @endforeach
                        </optgroup>
                    @endif

                    {{-- EXPENSES --}}
                    @if(isset($types['expenses']))
                        <optgroup label="💰 Izdevumi">
                            @foreach ($types['expenses'] as $enum)
                                <option value="{{ $enum->value }}">{{ $enum->label() }}</option>
                            @endforeach
                        </optgroup>
                    @endif

                    {{-- STEP --}}
                    @if(isset($types['step']))
                        <optgroup label="🚛 Iekraušana / Izkraušana">
                            @foreach ($types['step'] as $enum)
                                <option value="{{ $enum->value }}">{{ $enum->label() }}</option>
                            @endforeach
                        </optgroup>
                    @endif

                    {{-- OTHER --}}
                    @if(isset($types['other']))
                        <optgroup label="📦 Citi">
                            @foreach ($types['other'] as $enum)
                                <option value="{{ $enum->value }}">{{ $enum->label() }}</option>
                            @endforeach
                        </optgroup>
                    @endif

                </select>

                @error('type')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            {{-- Название документа --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nosaukums</label>

                <input type="text"
                       wire:model.blur="name"
                       placeholder="Piemēram: CMR 09.11.2025"
                       class="w-full border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                              text-gray-800 dark:text-gray-100 rounded-xl focus:ring-indigo-500 focus:border-indigo-500">

                @error('name')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            {{-- Файл --}}
            <div class="flex flex-col gap-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fails</label>

                <input type="file"
                       wire:model.live="documentFile"
                       accept="image/*"
                       class="block w-full text-sm text-gray-600 dark:text-gray-200
                              file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0
                              file:bg-indigo-50 dark:file:bg-indigo-900/40
                              file:text-indigo-700 dark:file:text-indigo-300
                              hover:file:bg-indigo-100 dark:hover:file:bg-indigo-800/50
                              file:cursor-pointer"
                       x-on:click="fileUploading = true; if(cancelTimeout) clearTimeout(cancelTimeout); cancelTimeout = setTimeout(() => { fileUploading = false; cancelTimeout = null }, 15000)">

                @error('documentFile')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

        </div>
    </form>

    {{-- ============================= --}}
    {{-- LIST: cards on <lg, table on lg+ --}}
    {{-- ============================= --}}
    <div class="space-y-4">

        {{-- MOBILE / PWA: CARDS ( < lg ) --}}
        <div class="lg:hidden space-y-3">

            @forelse($documents as $doc)

                @php
                    $url = $doc->file_url;
                    $ext = $url ? strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION)) : null;
                    $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp']);
                    $isPdf = $ext === 'pdf';
                @endphp

                <div id="document-{{ $doc->id ?? '' }}" class="rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
                    <div class="p-4 flex items-start gap-3">

                        {{-- preview/icon --}}
                        <div class="shrink-0">
                            @if($url && $isImage)
                                <a href="{{ $url }}" target="_blank" class="block">
                                    <img src="{{ $url }}" alt="Document preview"
                                         class="w-14 h-14 rounded-xl object-cover border border-gray-200 dark:border-gray-700">
                                </a>
                            @elseif($url && $isPdf)
                                <a href="{{ $url }}" target="_blank"
                                   class="w-14 h-14 rounded-xl flex items-center justify-center
                                          bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800">
                                    <span class="text-red-600 dark:text-red-300 font-semibold text-sm">PDF</span>
                                </a>
                            @else
                                <div class="w-14 h-14 rounded-xl flex items-center justify-center
                                            bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                                    <span class="text-gray-500 dark:text-gray-300 text-xs">FILE</span>
                                </div>
                            @endif
                        </div>

                        {{-- info + actions --}}
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                                {{ $doc->name ?? '—' }}
                            </div>

                            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs">
                                <span class="inline-flex items-center rounded-full px-2 py-1
                                             bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200">
                                    {{ $doc->type_label ?? '—' }}
                                </span>
                                @if(!empty($doc->step_label))
                                    <span class="rounded-full px-2 py-1 bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200">
                                        {{ $doc->step_label }}
                                    </span>
                                @endif
                                <span class="text-gray-500 dark:text-gray-400">
                                    {{ $doc->uploaded_at?->format('d.m.Y H:i') ?? '—' }}
                                </span>
                            </div>

                            <div class="mt-3 flex items-center gap-2">
                                @if($url)
                                    <a href="{{ $url }}" target="_blank"
                                       class="flex-1 inline-flex items-center justify-center rounded-xl px-3 py-2
                                              text-sm font-medium bg-gray-100 dark:bg-gray-800
                                              text-gray-800 dark:text-gray-100 active:scale-[0.98]">
                                        Atvērt
                                    </a>
                                @else
                                    <button type="button" disabled
                                            class="flex-1 inline-flex items-center justify-center rounded-xl px-3 py-2
                                                   text-sm font-medium bg-gray-100 dark:bg-gray-800
                                                   text-gray-400 dark:text-gray-500 cursor-not-allowed">
                                        —
                                    </button>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

            @empty
                <div class="rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/30 p-6 text-center">
                    <div class="text-gray-600 dark:text-gray-300 font-medium">Nav dokumentu</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Augšupielādē pirmo dokumentu augšā.</div>
                </div>
            @endforelse
        </div>

        {{-- DESKTOP: TABLE ( lg+ ) --}}
        <div class="hidden lg:block overflow-x-auto -mx-2 sm:mx-0">
            <table class="min-w-full border border-gray-200 dark:border-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 uppercase text-xs">
                    <tr>
                        <th class="px-3 py-2 text-left">Tips</th>
                        <th class="px-3 py-2 text-left">Nosaukums</th>
                        <th class="px-3 py-2 text-left">{{ __('app.trip.show.step_column') }}</th>
                        <th class="px-3 py-2 text-left">Datums</th>
                        <th class="px-3 py-2 text-left">Fails</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($documents as $doc)

                        @php
                            $url = $doc->file_url ?? null;
                            $ext = $url ? strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION)) : null;
                            $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp']);
                            $isPdf = $ext === 'pdf';
                        @endphp

                        <tr id="document-{{ $doc->id ?? '' }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/70 transition">
                            <td class="px-3 py-2">{{ $doc->type_label ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $doc->name ?? '—' }}</td>
                            <td class="px-3 py-2 text-gray-600 dark:text-gray-400">
                                {{ $doc->step_label ?? '—' }}
                            </td>
                            <td class="px-3 py-2 text-gray-600 dark:text-gray-400">
                                {{ $doc->uploaded_at?->format('d.m.Y H:i') ?? '—' }}
                            </td>

                            {{-- Превью --}}
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
                                            <img src="{{ $url }}" alt="Document preview"
                                                 class="w-12 h-12 object-cover rounded-lg border border-gray-300 dark:border-gray-700
                                                        transition-transform group-hover:scale-105 shadow-sm">
                                        </a>
                                    @else
                                        <a href="{{ $url }}" target="_blank"
                                           class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                            Atvērt
                                        </a>
                                    @endif
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-3 text-center text-gray-500 dark:text-gray-400">
                                Nav dokumentu
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    {{-- ✅ PWA bottom upload shortcut (mobile only: < lg) --}}
    <div class="lg:hidden fixed left-0 right-0 bottom-0 z-40 px-4 pb-[calc(env(safe-area-inset-bottom,0)+12px)]">
        <button type="button"
                class="w-full rounded-2xl py-3 font-semibold text-white bg-indigo-600 hover:bg-indigo-700
                       shadow-lg active:scale-[0.99]"
                x-data
                @click="window.scrollTo({ top: 0, behavior: 'smooth' });">
            ⬆️ Augšupielādēt dokumentu
        </button>
    </div>

    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('document-added', function (id) {
                var docId = (typeof id === 'object' && id !== null && 'id' in id) ? id.id : id;
                if (docId == null) return;
                setTimeout(function () {
                    var el = document.getElementById('document-' + docId);
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }, 150);
            });
        });
    </script>
</div>
