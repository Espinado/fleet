@section('title', $driver->first_name . ' ' . $driver->last_name)

<div class="w-full bg-white shadow-md rounded-lg p-8 space-y-10 text-lg">

    {{-- Основная информация --}}
    <div class="flex flex-col md:flex-row gap-8">
        {{-- Фото --}}
        <div class="w-full md:w-1/4 h-64">
            @if($driver->photo && file_exists(storage_path('app/public/' . $driver->photo)))
                <a href="{{ asset('storage/' . $driver->photo) }}" target="_blank">
                    <img src="{{ asset('storage/' . $driver->photo) }}"
                         class="w-full h-full object-cover rounded-lg border shadow">
                </a>
            @else
                <div class="w-full h-full bg-gray-200 flex items-center justify-center rounded-lg text-gray-500">
                    No Photo
                </div>
            @endif
        </div>

        {{-- Личные данные --}}
        <div class="flex-1 space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold">{{ $driver->first_name }} {{ $driver->last_name }}</h1>
                <a href="{{ route('drivers.edit', $driver) }}"
                   class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold">
                   ✏️ Edit
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    {{-- LEFT: Personal Info --}}
    <div class="md:col-span-2">
        <h2 class="text-xl font-semibold mb-2 border-b pb-1">👤 Personal Info</h2>

        <p>
            <span class="font-semibold">Personal Code:</span>
            <span class="text-gray-700">{{ $driver->pers_code ?? '-' }}</span>
        </p>

        <p>
            <span class="font-semibold">Phone:</span>
            <span class="text-gray-700">{{ $driver->phone ?? '-' }}</span>
        </p>

        <p>
            <span class="font-semibold">Email:</span>
            <span class="text-gray-700">{{ $driver->email ?? '-' }}</span>
        </p>

        <p>
            <span class="font-semibold">Citizenship:</span>
            <span class="text-gray-700">
                {{ config('countries')[$driver->citizenship_id]['name'] ?? '-' }}
            </span>
        </p>

        <p>
            <span class="font-semibold">Company:</span>
            <span class="text-gray-700">
                {{ config('companies')[$driver->company]['name'] ?? '-' }}
            </span>
        </p>
    </div>

    {{-- RIGHT: PIN --}}
    <div class="flex items-center justify-center">
        <div class="w-full bg-gray-50 border border-gray-200 rounded-xl p-4 text-center shadow-sm">
            <div class="text-sm text-gray-500 uppercase tracking-wide mb-1">
                Driver PIN
            </div>

            <div class="text-3xl font-bold tracking-widest text-gray-800">
                {{ $driver->login_pin ?? '— — — —' }}
            </div>

            <div class="text-xs text-gray-400 mt-2">
                Used for driver login
            </div>
        </div>
    </div>

</div>


          <div>
    <h2 class="text-xl font-semibold mb-2 border-b pb-1">🏠 Addresses</h2>
    <p>
        <span class="font-semibold">Declared:</span>
        <span class="text-gray-700">
            @php
                $declaredCountry = config('countries')[$driver->declared_country_id]['name'] ?? '-';
                $declaredCities = getCitiesByCountryId($driver->declared_country_id);
                $declaredCity = $declaredCities[$driver->declared_city_id]['name'] ?? '-';
            @endphp

            {{ $declaredCountry }}, {{ $declaredCity }},
            {{ $driver->declared_street ?? '-' }}
            {{ $driver->declared_building ?? '' }}
            {{ $driver->declared_room ?? '' }},
            {{ $driver->declared_postcode ?? '' }}
        </span>
    </p>

    <p>
        <span class="font-semibold">Actual:</span>
        <span class="text-gray-700">
            @php
                $actualCountry = config('countries')[$driver->actual_country_id]['name'] ?? '-';
                $actualCities = getCitiesByCountryId($driver->actual_country_id);
                $actualCity = $actualCities[$driver->actual_city_id]['name'] ?? '-';
            @endphp

            {{ $actualCountry }}, {{ $actualCity }},
            {{ $driver->actual_street ?? '-' }}
            {{ $driver->actual_building ?? '' }}
            {{ $driver->actual_room ?? '' }}
        </span>
    </p>
</div>


        @php
    use Carbon\Carbon;

    $fmt = function ($value) {
        if (blank($value)) return '-';
        try {
            return Carbon::parse($value)->format('d.m.Y');
        } catch (\Throwable $e) {
            return '-';
        }
    };
@endphp

<div>
    <h2 class="text-xl font-semibold mb-2 border-b pb-1">📄 Documents</h2>

    <p>
        <span class="font-semibold">License:</span>
        <span class="text-gray-700">
            {{ $driver->license_number ?? '-' }}
            ({{ $fmt($driver->license_issued) }} – {{ $fmt($driver->license_end) }})
        </span>
    </p>

    <p>
        <span class="font-semibold">95 Code:</span>
        <span class="text-gray-700">
            {{ $fmt($driver->code95_issued) }} – {{ $fmt($driver->code95_end) }}
        </span>
    </p>

    <p>
        <span class="font-semibold">Permit:</span>
        <span class="text-gray-700">
            {{ $fmt($driver->permit_issued) }} – {{ $fmt($driver->permit_expired) }}
        </span>
    </p>

    <p>
        <span class="font-semibold">Medical Exam:</span>
        <span class="text-gray-700">
            {{ $fmt($driver->medical_exam_passed ?? $driver->medical_issued) }}
            – {{ $fmt($driver->medical_exam_expired ?? $driver->medical_expired) }}
        </span>
    </p>

    <p>
        <span class="font-semibold">Declaration:</span>
        <span class="text-gray-700">
            {{ $fmt($driver->declaration_issued) }} – {{ $fmt($driver->declaration_expired) }}
        </span>
    </p>
</div>
            <div>
                <h2 class="text-xl font-semibold mb-2 border-b pb-1">📌 Status</h2>
                <p><span class="font-semibold">Current Status:</span> <span class="text-gray-700">{{ $driver->status_label }}</span></p>
            </div>
        </div>
    </div>

    {{-- Фото документов --}}
    <div>
        <h2 class="text-2xl font-bold mb-4">📷 Documents</h2>
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
                    <h3 class="font-semibold mb-2 text-lg">{{ $title }}</h3>
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
    </div>

    {{-- Кнопки --}}
    <div class="flex justify-between">
        <a href="{{ route('drivers.index') }}"
           class="px-5 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-lg font-semibold">
            ⬅ Back to Drivers
        </a>

    </div>
</div>
