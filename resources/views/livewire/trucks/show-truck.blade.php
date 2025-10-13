<div class="p-6 bg-white rounded-2xl shadow-lg max-w-6xl mx-auto mt-10 relative">

    {{-- Верхняя панель действий --}}
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">
            {{ $truck->brand }} {{ $truck->model }} <span class="text-gray-500">({{ $truck->plate }})</span>
        </h1>

        <div class="flex items-center gap-4">
            {{-- Кнопка редактирования --}}
            <a href="{{ route('trucks.edit', $truck->id) }}"
               class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                ✏️ Edit
            </a>

            {{-- Кнопка удаления --}}

        </div>
    </div>

    {{-- Основная информация --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="space-y-2">
            <p><strong>Year:</strong> {{ $truck->year }}</p>
            <p><strong>Status:</strong> {{ $truck->status ? '✅ Active' : '❌ Inactive' }}</p>
            <p><strong>Active:</strong> {{ $truck->is_active ? '✅ Yes' : '❌ No' }}</p>
            <p><strong>VIN:</strong> {{ $truck->vin }}</p>
        </div>

        <div class="space-y-2">
            <p><strong>Insurance Company:</strong> {{ $truck->insurance_company }}</p>
            <p><strong>Insurance #:</strong> {{ $truck->insurance_number }}</p>
            <p><strong>Valid:</strong> {{ $truck->insurance_issued }} → {{ $truck->insurance_expired }}</p>
        </div>
    </div>

    {{-- Техосмотр --}}
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-2 border-b pb-1">Inspection</h2>
        <p><strong>Issued:</strong> {{ $truck->inspection_issued }}</p>
        <p><strong>Expires:</strong> {{ $truck->inspection_expired }}</p>
    </div>

    {{-- Техпаспорт --}}
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-2 border-b pb-1">Technical Passport</h2>
        <p><strong>Number:</strong> {{ $truck->tech_passport_nr ?? '-' }}</p>
        <p><strong>Issued:</strong> {{ $truck->tech_passport_issued ?? '-' }}</p>
        <p><strong>Expired:</strong> {{ $truck->tech_passport_expired ?? '-' }}</p>
    </div>

    {{-- Фото --}}
    <div>
        <h2 class="text-xl font-semibold mb-3 border-b pb-1">Documents</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-3">
            @php $photos = [$truck->tech_passport_photo]; @endphp
            @foreach($photos as $photo)
                <div class="border rounded-lg p-2 flex items-center justify-center h-48 bg-gray-50">
                    @if($photo)
                        <img src="{{ asset('storage/' . $photo) }}" alt="Tech Passport Photo"
                             class="h-full object-contain rounded">
                    @else
                        <span class="text-gray-400 text-sm">No image</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Кнопка "Назад" --}}
    <div class="mt-10 flex justify-between items-center">
        <a href="{{ route('trucks.index') }}"
           class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
            ← Back
        </a>
         {{-- Кнопка удаления --}}
        <button type="button"
        wire:click="destroy"
        onclick="confirm('Are you sure you want to delete this driver?') || event.stopImmediatePropagation()"
        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
        🗑 Delete
    </button>
    </div>
</div>


