<div class="space-y-3">

    {{-- Форма загрузки --}}
    <form wire:submit.prevent="upload" class="bg-gray-50 rounded-xl p-3 space-y-2">

        <div class="flex flex-col gap-1">
            <label class="text-xs">Tips</label>
            <input type="text" wire:model="type"
                   class="border-gray-300 rounded-lg text-sm p-2"
                   placeholder="CMR, Invoice, Foto utt.">
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-xs">Komentārs</label>
            <input type="text" wire:model="comment"
                   class="border-gray-300 rounded-lg text-sm p-2">
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-xs">Fails</label>
            <input type="file"
                   wire:model="file"
                   accept="image/*,application/pdf"
                   capture="environment"
                   class="text-sm">
            @error('file') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 text-white py-2 rounded-lg font-semibold text-sm">
            ⬆ Augšupielādēt
        </button>

    </form>

    {{-- Список документов --}}
    @if ($step->documents->count() === 0)
        <p class="text-xs text-gray-400 text-center">Dokumenti nav pievienoti…</p>
    @endif

    <div class="grid grid-cols-4 gap-2">
        @foreach ($step->documents as $doc)
            <div class="relative group">

                @if (str_ends_with($doc->file, '.pdf'))
                    <a href="{{ $doc->file_url }}" target="_blank">
                        <div class="w-full pb-[100%] bg-red-100 text-red-700 rounded-lg flex items-center justify-center shadow">
                            PDF
                        </div>
                    </a>
                @else
                    <a href="{{ route('driver.documents.view', $doc->id) }}">
                        <img src="{{ $doc->file_url }}"
                             class="w-full h-20 rounded-lg object-cover shadow">
                    </a>
                @endif

                @if($doc->type)
                    <span class="absolute bottom-1 left-1 text-[10px]
                        bg-black/60 text-white px-1.5 py-0.5 rounded">
                        {{ strtoupper($doc->type) }}
                    </span>
                @endif

            </div>
        @endforeach
    </div>

</div>
