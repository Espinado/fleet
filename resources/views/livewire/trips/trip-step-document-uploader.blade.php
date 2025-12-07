<div class="bg-white dark:bg-gray-900 shadow rounded-2xl p-4 sm:p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h2 class="text-lg sm:text-xl font-semibold text-gray-800 dark:text-gray-100">
            📄 Dokumenti solim
        </h2>
        <span class="text-sm text-gray-500 dark:text-gray-400">
            Step ID: {{ $step->id }}
        </span>
    </div>

    {{-- Notifications --}}
    @if (session()->has('success'))
        <div class="bg-green-50 dark:bg-green-900/30 border border-green-300 text-green-800 dark:text-green-200 px-4 py-2 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Upload Form --}}
  {{-- Upload Form --}}
<form wire:submit.prevent="saveDocument" enctype="multipart/form-data">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- Type --}}
        <div>
            <label class="block text-sm font-medium">Tips</label>
          <select wire:model="type"
        class="w-full border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-xl">

    @foreach(\App\Enums\StepDocumentType::cases() as $case)
        <option value="{{ $case->value }}">
            {{ $case->label() }}
        </option>
    @endforeach

</select>

        </div>

        {{-- Comment --}}
        <div>
            <label class="block text-sm font-medium">Komentārs</label>
            <input type="text" wire:model="comment"
                   class="w-full border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-xl">
        </div>

        {{-- File --}}
        <div>
            <label class="block text-sm font-medium">Fails</label>

            <input type="file"
                   wire:model="file"
                   accept="image/*,application/pdf"
                   class="block w-full text-sm">

            @error('file')
                <span class="text-red-500 text-xs">{{ $message }}</span>
            @enderror
        </div>

    </div>

    <div class="flex justify-end">
        <button type="submit"
                wire:loading.attr="disabled"
                class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-6 py-2">
            <span wire:loading.remove>Augšupielādēt</span>
            <span wire:loading class="animate-pulse">⏳ Lādē...</span>
        </button>
    </div>
</form>


    {{-- Documents Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-200 dark:border-gray-700 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 uppercase text-xs">
            <tr>
                <th class="px-3 py-2 text-left">Tips</th>
                <th class="px-3 py-2 text-left">Komentārs</th>
                <th class="px-3 py-2 text-left">Datums</th>
                <th class="px-3 py-2 text-left">Fails</th>
                <th class="px-3 py-2 text-right">Darbības</th>
            </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse ($documents as $doc)
                @php
                    $url = asset('storage/'.$doc->file_path);
                    $ext = strtolower(pathinfo($doc->file_path, PATHINFO_EXTENSION));
                    $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                    $isPdf = $ext === 'pdf';
                @endphp

                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                    <td class="px-3 py-2">{{ $doc->type ?: '—' }}</td>
                    <td class="px-3 py-2">{{ $doc->comment ?: '—' }}</td>
                    <td class="px-3 py-2 text-gray-600 dark:text-gray-400">
                        {{ $doc->created_at->format('d.m.Y H:i') }}
                    </td>

                    <td class="px-3 py-2">
                        @if ($isPdf)
                            <a href="{{ $url }}" target="_blank" class="text-red-600 underline">PDF</a>
                        @elseif ($isImage)
                            <a href="{{ $url }}" target="_blank">
                                <img src="{{ $url }}" class="w-12 h-12 object-cover rounded-md">
                            </a>
                        @else
                            <a href="{{ $url }}" target="_blank" class="text-indigo-600 underline">Open</a>
                        @endif
                    </td>

                    <td class="px-3 py-2 text-right">
                        <button wire:click="delete({{ $doc->id }})"
                                class="text-red-600 hover:text-red-800">
                            Dzēst
                        </button>
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="5" class="px-3 py-3 text-center text-gray-500">
                        Nav dokumentu
                    </td>
                </tr>
            @endforelse
            </tbody>

        </table>
    </div>

</div>
