<div class="space-y-6">

@include('components.upload-loading-overlay', ['targets' => 'file,saveDocument'])

    {{-- Upload Form: спиннер только когда файл уже выбран и идёт загрузка на сервер --}}
    <form wire:submit.prevent="saveDocument"
          enctype="multipart/form-data"
          class="bg-gray-50 p-4 rounded-xl space-y-3 shadow"
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

        {{-- TYPE --}}
        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold">{{ __('app.driver.step_docs.type') }}</label>

            <select wire:model="type"
                    class="border-gray-300 rounded-lg text-sm p-2 bg-white">
                @foreach(\App\Enums\StepDocumentType::cases() as $case)
                    <option value="{{ $case->value }}">
                        {{ $case->icon() }} {{ $case->label() }}
                    </option>
                @endforeach
            </select>

            @error('type')
                <p class="text-red-600 text-xs">{{ $message }}</p>
            @enderror
        </div>

        {{-- COMMENT --}}
        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold">{{ __('app.driver.step_docs.comment') }}</label>
            <input type="text" wire:model="comment"
                   class="border-gray-300 rounded-lg text-sm p-2 bg-white">
            @error('comment')
                <p class="text-red-600 text-xs">{{ $message }}</p>
            @enderror
        </div>

        {{-- FILE (клиентское сжатие изображений перед загрузкой) --}}
        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold">{{ __('app.driver.step_docs.file') }}</label>
            <input type="file"
                   wire:model="file"
                   accept="image/*,application/pdf"
                   capture="environment"
                   class="text-sm">
            @if($file ?? null)
                <p class="text-xs text-gray-600 mt-1">📄 {{ $file->getClientOriginalName() }}</p>
            @endif
            @error('file')
                <p class="text-red-600 text-xs">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                wire:loading.attr="disabled"
                wire:target="file,saveDocument"
                x-bind:disabled="fileUploading"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg font-semibold text-sm disabled:opacity-60 disabled:cursor-not-allowed">
            ⬆ {{ __('app.driver.step_docs.upload') }}
        </button>

    </form>

    {{-- Document Table --}}
    <div class="space-y-2">

        @foreach ($step->stepDocuments as $doc)

            @php
                /** @var \App\Enums\StepDocumentType $typeEnum */
                $typeEnum = $doc->type; // enum через casts

                $url = asset('storage/' . $doc->file_path);
                $ext = strtolower(pathinfo($doc->file_path, PATHINFO_EXTENSION));
                $isPdf = $ext === 'pdf';
                $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
            @endphp

            <div class="flex items-center gap-3 bg-white rounded-xl p-3 border shadow-sm">

                <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold text-gray-800 truncate">
                        {{ $typeEnum->label() }}
                    </div>

                    <div class="text-xs text-gray-500 truncate">
                        {{ $doc->comment ?: '—' }}
                    </div>
                </div>

                <div class="text-[11px] text-gray-400 whitespace-nowrap">
                    {{ $doc->created_at->format('d.m.Y H:i') }}
                </div>

                <div class="w-14 h-14 rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                    @if ($isPdf)
                        <a href="{{ $url }}" target="_blank" class="font-bold text-red-600 text-sm">
                            PDF
                        </a>
                    @elseif ($isImage)
                        <a href="{{ $url }}" target="_blank">
                            <img src="{{ $url }}" class="w-14 h-14 object-cover">
                        </a>
                    @else
                        <a href="{{ $url }}" target="_blank" class="text-indigo-600 underline text-xs">
                            {{ __('app.driver.step_docs.open') }}
                        </a>
                    @endif
                </div>

            </div>

        @endforeach

    </div>

</div>
