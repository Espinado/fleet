<div class="p-6 space-y-6"
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

    {{-- HEADER --}}
    <div class="flex items-center gap-3">
        <a href="{{ url()->previous() }}" class="text-gray-600 text-2xl">
            ←
        </a>

        <h1 class="text-2xl font-bold flex items-center gap-2">
            📤 {{ __('app.driver.upload.title') }}
        </h1>
    </div>

    {{-- DOCUMENT INFO CARD --}}
    <div class="bg-white shadow-md rounded-xl p-5 border border-gray-200 space-y-2">
        <div class="text-gray-700"><strong>{{ __('app.driver.upload.doc_type') }}</strong>
            <span class="uppercase text-blue-700 font-semibold">{{ $type }}</span>
        </div>

        <div class="text-gray-700"><strong>{{ __('app.driver.upload.trip_id') }}</strong> {{ $trip }}</div>
        <div class="text-gray-700"><strong>{{ __('app.driver.upload.step') }}</strong> {{ $step }}</div>
    </div>

    {{-- SUCCESS --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg text-center">
            ✅ {{ session('success') }}
        </div>
    @endif

    @include('components.upload-loading-overlay', ['targets' => 'file,upload'])

    {{-- UPLOAD BOX --}}
    <div
        class="bg-white border-2 border-dashed border-gray-300 rounded-xl p-8 text-center space-y-4 shadow-sm">

        <div class="text-gray-500 text-xl">
            📎 {{ __('app.driver.upload.click_to_choose') }}
        </div>

        <label
            class="cursor-pointer inline-block px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow hover:bg-blue-700 transition"
            x-on:click="fileUploading = true; if(cancelTimeout) clearTimeout(cancelTimeout); cancelTimeout = setTimeout(() => { fileUploading = false; cancelTimeout = null }, 15000)">
            {{ __('app.driver.upload.choose_file') }}
            <input type="file" wire:model="file" class="hidden">
        </label>

        @error('file')
            <div class="text-red-600 text-sm">{{ $message }}</div>
        @enderror

        {{-- FILE PREVIEW --}}
        @if ($file)
            <div class="mt-4 bg-gray-50 border p-4 rounded-lg text-left">
                <div class="font-medium text-gray-700 mb-1">📄 {{ __('app.driver.upload.selected') }}</div>
                <div class="text-blue-700 font-semibold">
                    {{ $file->getClientOriginalName() }}
                </div>
            </div>
        @endif
    </div>

    {{-- BUTTON --}}
    <button
        wire:click="upload"
        wire:loading.attr="disabled"
        wire:target="file,upload"
        x-bind:disabled="fileUploading"
        class="w-full py-4 bg-green-600 text-white text-xl font-semibold rounded-xl shadow hover:bg-green-700 transition disabled:opacity-60 disabled:cursor-not-allowed">
        ⬆️ Загрузить документ
    </button>

    {{-- FOOTER HINT --}}
    <p class="text-center text-gray-500 text-sm">
        Допустимые форматы: JPG, PNG, PDF. Максимум 10 MB.
    </p>

</div>
