@section('title', $driver->first_name . ' ' . $driver->last_name)

<div class="w-full bg-white shadow-md rounded-lg p-8 space-y-10 text-lg">

    {{-- Personīgā informācija --}}
    <div class="flex flex-col md:flex-row gap-8">
        {{-- Foto --}}
        <div class="w-full md:w-1/4 h-64">
            @if($driver->photo_url)
                <a href="{{ $driver->photo_url }}" target="_blank">
                    <img src="{{ $driver->photo_url }}"
                         class="w-full h-full object-cover rounded-lg border shadow" alt="">
                </a>
            @else
                <div class="w-full h-full bg-gray-200 flex items-center justify-center rounded-lg text-gray-500">
                    {{ __('app.driver.show.no_photo') }}
                </div>
            @endif
        </div>

        {{-- Personīgie dati --}}
        <div class="flex-1 space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold">{{ $driver->first_name }} {{ $driver->last_name }}</h1>
                <a href="{{ route('drivers.edit', $driver) }}"
                   class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold">
                   {{ __('app.driver.show.edit') }}
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    {{-- LEFT: Personal Info --}}
    <div class="md:col-span-2">
        <h2 class="text-xl font-semibold mb-2 border-b pb-1">👤 {{ __('app.driver.show.personal_info') }}</h2>

        <p>
            <span class="font-semibold">Personas kods:</span>
            <span class="text-gray-700">{{ $driver->pers_code ?? '-' }}</span>
        </p>

        <p>
            <span class="font-semibold">Tālrunis:</span>
            <span class="text-gray-700">{{ $driver->phone ?? '-' }}</span>
        </p>

        <p>
            <span class="font-semibold">E-pasts:</span>
            <span class="text-gray-700">{{ $driver->email ?? '-' }}</span>
        </p>

        <p>
            <span class="font-semibold">Pilsonība:</span>
            <span class="text-gray-700">
                {{ config('countries')[$driver->citizenship_id]['name'] ?? '-' }}
            </span>
        </p>

        <p>
    <span class="font-semibold">Kompānija:</span>
    <span class="text-gray-700">
        {{ $driver->company?->name ?? '-' }}
    </span>
</p>
    </div>

    {{-- RIGHT: PIN --}}
    <div class="flex items-center justify-center">
        <div class="w-full bg-gray-50 border border-gray-200 rounded-xl p-4 text-center shadow-sm">
            <div class="text-sm text-gray-500 uppercase tracking-wide mb-1">
                {{ __('app.driver.show.pin_title') }}
            </div>

            <div class="text-3xl font-bold tracking-widest text-gray-800">
                {{ $driver->login_pin ?? '— — — —' }}
            </div>

            <div class="text-xs text-gray-400 mt-2">
                {{ __('app.driver.show.pin_hint') }}
            </div>
        </div>
    </div>

</div>


          <div>
    <h2 class="text-xl font-semibold mb-2 border-b pb-1">🏠 {{ __('app.driver.show.addresses') }}</h2>
    <p>
        <span class="font-semibold">{{ __('app.driver.show.declared') }}:</span>
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
        <span class="font-semibold">{{ __('app.driver.show.actual') }}:</span>
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
    <h2 class="text-xl font-semibold mb-2 border-b pb-1">📄 {{ __('app.driver.show.documents') }}</h2>

    <p>
        <span class="font-semibold">{{ __('app.driver.docs.license') }}:</span>
        <span class="text-gray-700">
            {{ $driver->license_number ?? '-' }}
            ({{ $fmt($driver->license_issued) }} – {{ $fmt($driver->license_end) }})
        </span>
    </p>

    <p>
        <span class="font-semibold">{{ __('app.driver.docs.code95') }}:</span>
        <span class="text-gray-700">
            {{ $fmt($driver->code95_issued) }} – {{ $fmt($driver->code95_end) }}
        </span>
    </p>

    <p>
        <span class="font-semibold">{{ __('app.driver.docs.permit') }}:</span>
        <span class="text-gray-700">
            {{ $fmt($driver->permit_issued) }} – {{ $fmt($driver->permit_expired) }}
        </span>
    </p>

    <p>
        <span class="font-semibold">{{ __('app.driver.docs.med_csdD') }}:</span>
        <span class="text-gray-700">
            {{ $fmt($driver->medical_exam_passed ?? $driver->medical_issued) }}
            – {{ $fmt($driver->medical_exam_expired ?? $driver->medical_expired) }}
        </span>
    </p>

    <p>
        <span class="font-semibold">{{ __('app.driver.docs.declaration') }}:</span>
        <span class="text-gray-700">
            {{ $fmt($driver->declaration_issued) }} – {{ $fmt($driver->declaration_expired) }}
        </span>
    </p>
</div>
            <div>
                <h2 class="text-xl font-semibold mb-2 border-b pb-1">📌 {{ __('app.driver.show.status_block') }}</h2>
                <p><span class="font-semibold">{{ __('app.driver.show.current_status') }}:</span> <span class="text-gray-700">{{ $driver->status_label }}</span></p>
            </div>
        </div>
    </div>

    {{-- Foto dokumentiem --}}
    <div>
        <h2 class="text-2xl font-bold mb-4">{{ __('app.driver.show.photo_block') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @php
                $photos = [
                    __('app.driver.show.photo_license') => $driver->license_photo_url,
                    __('app.driver.show.photo_med')     => $driver->medical_certificate_photo_url,
                    __('app.driver.show.photo_driver')  => $driver->photo_url,
                ];
            @endphp

            @foreach($photos as $title => $url)
                <div>
                    <h3 class="font-semibold mb-2 text-lg">{{ $title }}</h3>
                    @if($url)
                        <a href="{{ $url }}" target="_blank">
                            <img src="{{ $url }}" class="rounded-lg border shadow w-full h-52 object-cover" alt="">
                        </a>
                    @else
                        <div class="w-full h-52 bg-gray-200 flex items-center justify-center rounded-lg text-gray-500">
                            {{ __('app.driver.show.photo_missing', ['title' => $title]) }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Pogas --}}
    <div class="flex justify-between">
        <a href="{{ route('drivers.index') }}"
           class="px-5 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-lg font-semibold">
            {{ __('app.driver.show.back') }}
        </a>

    </div>
</div>
