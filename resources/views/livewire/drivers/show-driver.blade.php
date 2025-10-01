@section('title', $driver->first_name . ' ' . $driver->last_name)

<div class="w-full bg-white shadow-md rounded-lg p-8 space-y-8 text-lg">

    {{-- Основная информация и кнопка Edit --}}
    <div class="flex flex-col md:flex-row gap-8">
        {{-- Фото водителя --}}
        <div class="w-full md:w-1/4 h-64">
            @if($driver->photo && file_exists(storage_path('app/public/' . $driver->photo)))
                <a href="{{ asset('storage/' . $driver->photo) }}" target="_blank">
                    <img src="{{ asset('storage/' . $driver->photo) }}"
                         class="w-full h-full object-cover rounded-lg border">
                </a>
            @else
                <div class="w-full h-full bg-gray-200 flex items-center justify-center rounded-lg text-gray-500">
                    No Photo
                </div>
            @endif
        </div>

        {{-- Личные данные --}}
        <div class="flex-1 space-y-2">
            <div class="flex items-center justify-between mb-2">
                <h1 class="text-3xl font-bold">{{ $driver->first_name }} {{ $driver->last_name }}</h1>
                <a href=""
                   class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold">
                   ✏️ Edit
                </a>
            </div>

            <p>👤 Personal Code: {{ $driver->pers_code ?? $driver->pers_code }}</p>
            <p>📞 Phone: {{ $driver->phone ?? '-' }}</p>
            <p>📧 Email: {{ $driver->email ?? '-' }}</p>
            <p>🛂 Citizenship: {{ $driver->citizenship ?? '-' }}</p>
            <p>📍 Declared Address:
                {{ $driver->declared_country ?? '-' }}, {{ $driver->declared_city ?? '-' }},
                {{ $driver->declared_street ?? '-' }} {{ $driver->declared_building ?? '' }}
                {{ $driver->declared_room ?? '' }}, {{ $driver->declared_postcode ?? '' }}
            </p>
            <p>📍 Actual Address:
                {{ $driver->actual_country ?? '-' }}, {{ $driver->actual_city ?? '-' }},
                {{ $driver->actual_street ?? '-' }} {{ $driver->actual_building ?? '' }}
                {{ $driver->actual_room ?? '' }}
            </p>
            <p>🚦 License: {{ $driver->license_number ?? '-' }}
               ({{ $driver->license_issued ?? '-' }} – {{ $driver->license_end ?? '-' }})</p>
            <p>95 Code: {{ $driver->{"code95_issued"} ?? '-' }} – {{ $driver->{"code95_end"} ?? '-' }}</p>
            <p>Permit: {{ $driver->permit_issued ?? '-' }} – {{ $driver->permit_expired ?? '-' }}</p>
            <p>Medical Exam: {{ $driver->medical_exam_passed ?? $driver->medical_issued ?? '-' }} – {{ $driver->medical_exam_expired ?? $driver->medical_expired ?? '-' }}</p>
            <p>Declaration: {{ $driver->declaration_issued ?? '-' }} – {{ $driver->declaration_expired ?? '-' }}</p>
            <p>Status:    {{ $driver->status_label }}</p>

        </div>
    </div>

    {{-- Фото документов --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @php
            $photos = [
                'License Photo' => $driver->license_photo,
                'Medical Certificate' => $driver->medical_certificate_photo,
                'Driver Photo' => $driver->photo
            ];
        @endphp

        @foreach($photos as $title => $path)
            <div>
                <h2 class="font-semibold mb-2 text-lg">{{ $title }}</h2>
                @if($path && file_exists(storage_path('app/public/' . $path)))
                    <a href="{{ asset('storage/' . $path) }}" target="_blank">
                        <img src="{{ asset('storage/' . $path) }}" class="rounded-lg border shadow w-full h-52 object-cover">
                    </a>
                @else
                    <div class="w-full h-52 bg-gray-200 flex items-center justify-center rounded-lg text-gray-500">
                        No {{ $title }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Кнопка "Назад" --}}
    <div>
        <a href="{{ route('drivers.index') }}" class="px-5 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-lg font-semibold">
            ⬅ Back to Drivers
        </a>
    </div>

</div>
