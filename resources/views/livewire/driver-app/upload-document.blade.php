<div class="p-6 space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center gap-3">
        <a href="{{ url()->previous() }}" class="text-gray-600 text-2xl">
            ←
        </a>

        <h1 class="text-2xl font-bold flex items-center gap-2">
            📤 Загрузка документа
        </h1>
    </div>

    {{-- DOCUMENT INFO CARD --}}
    <div class="bg-white shadow-md rounded-xl p-5 border border-gray-200 space-y-2">
        <div class="text-gray-700"><strong>Тип документа:</strong>
            <span class="uppercase text-blue-700 font-semibold">{{ $type }}</span>
        </div>

        <div class="text-gray-700"><strong>Trip ID:</strong> {{ $trip }}</div>
        <div class="text-gray-700"><strong>Step:</strong> {{ $step }}</div>
    </div>

    {{-- SUCCESS --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg text-center">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{-- UPLOAD BOX --}}
    <div
        class="bg-white border-2 border-dashed border-gray-300 rounded-xl p-8 text-center space-y-4 shadow-sm">

        <div class="text-gray-500 text-xl">
            📎 Нажмите чтобы выбрать файл
        </div>

        <label
            class="cursor-pointer inline-block px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow hover:bg-blue-700 transition">
            Выбрать файл
            <input type="file" wire:model="file" class="hidden">
        </label>

        @error('file')
            <div class="text-red-600 text-sm">{{ $message }}</div>
        @enderror

        {{-- FILE PREVIEW --}}
        @if ($file)
            <div class="mt-4 bg-gray-50 border p-4 rounded-lg text-left">
                <div class="font-medium text-gray-700 mb-1">📄 Вы выбрали:</div>
                <div class="text-blue-700 font-semibold">
                    {{ $file->getClientOriginalName() }}
                </div>
            </div>
        @endif
    </div>

    {{-- BUTTON --}}
    <button
        wire:click="upload"
        class="w-full py-4 bg-green-600 text-white text-xl font-semibold rounded-xl shadow hover:bg-green-700 transition">
        ⬆️ Загрузить документ
    </button>

    {{-- FOOTER HINT --}}
    <p class="text-center text-gray-500 text-sm">
        Допустимые форматы: JPG, PNG, PDF. Максимум 10 MB.
    </p>

</div>
