<div class="p-8 bg-gray-100 min-h-screen flex justify-center">

    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-7xl p-8">

        {{-- Верхняя панель действий --}}
        <div class="flex justify-between items-start mb-10 gap-4">
            <h1 class="text-4xl font-extrabold text-gray-800">
                {{ $trailer->brand }} {{ $trailer->model }}
                <span class="text-gray-500 text-lg">({{ $trailer->plate }})</span>
            </h1>

            <div class="flex gap-3 mt-4 md:mt-0">
                <a href="{{ route('trailers.edit', $trailer->id) }}"
                   class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition shadow-md">
                    ✏️ Edit
                </a>
            </div>
        </div>

        {{-- Основная информация --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-6 rounded-2xl shadow-inner space-y-3">
                <p><strong>Year:</strong> {{ $trailer->year }}</p>
                <p><strong>Status:</strong> {{ $trailer->status ? '✅ Active' : '❌ Inactive' }}</p>
                <p><strong>Active:</strong> {{ $trailer->is_active ? '✅ Yes' : '❌ No' }}</p>
                <p><strong>VIN:</strong> {{ $trailer->vin }}</p>
            </div>

            <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-6 rounded-2xl shadow-inner space-y-3">
                <p><strong>Insurance Company:</strong> {{ $trailer->insurance_company }}</p>
                <p><strong>Insurance #:</strong> {{ $trailer->insurance_number }}</p>
                <p><strong>Valid:</strong> {{ $trailer->insurance_issued }} → {{ $trailer->insurance_expired }}</p>
            </div>
        </div>

        {{-- Техосмотр --}}
        <div class="bg-gray-50 p-6 rounded-2xl shadow-inner mb-10">
            <h2 class="text-2xl font-semibold mb-4 border-b pb-2">Inspection</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <p><strong>Issued:</strong> {{ $trailer->inspection_issued }}</p>
                <p><strong>Expires:</strong> {{ $trailer->inspection_expired }}</p>
            </div>
        </div>

        {{-- Техпаспорт --}}
        <div class="bg-gray-50 p-6 rounded-2xl shadow-inner mb-10">
            <h2 class="text-2xl font-semibold mb-4 border-b pb-2">Technical Passport</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <p><strong>Number:</strong> {{ $trailer->tech_passport_nr ?? '-' }}</p>
                <p><strong>Issued:</strong> {{ $trailer->tech_passport_issued ?? '-' }}</p>
                <p><strong>Expired:</strong> {{ $trailer->tech_passport_expired ?? '-' }}</p>
            </div>
        </div>

        {{-- Фото --}}
        <div class="bg-gray-50 p-6 rounded-2xl shadow-inner mb-10">
            <h2 class="text-2xl font-semibold mb-4 border-b pb-2">Documents</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-4">
                @php $photos = [$trailer->tech_passport_photo]; @endphp
                @foreach($photos as $photo)
                    <div class="border rounded-2xl p-2 flex items-center justify-center h-56 bg-white shadow-sm">
                        @if($photo)
                            <img src="{{ asset('storage/' . $photo) }}" alt="Tech Passport Photo"
                                 class="h-full object-contain rounded-xl">
                        @else
                            <span class="text-gray-400 text-sm">No image</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

       <div class="flex justify-between items-center mt-6">
    <a href="{{ route('trailers.index') }}"
       class="px-6 py-2 bg-gray-200 text-gray-800 font-semibold rounded-xl hover:bg-gray-300 transition shadow-md">
        ← Back
    </a>

    <button type="button"
        wire:click="destroy"
        onclick="confirm('Are you sure you want to delete this driver?') || event.stopImmediatePropagation()"
        class="px-4 py-2 bg-red-600 text-white font-semibold rounded-xl hover:bg-red-700 transition shadow-md">
        🗑 Delete
    </button>
</div>

    </div>

</div>
