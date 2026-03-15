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

    {{-- Рейсы за период (выезд из гаража — заезд в гараж) --}}
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-3 border-b pb-1">{{ __('app.driver.show.trips_period_title') }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end mb-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('app.truck.show.mileage_date_departure') }}</label>
                <input type="date" wire:model.live="tripsPeriodFrom"
                       class="w-full px-3 py-2 rounded-xl border border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('app.truck.show.mileage_date_return') }}</label>
                <input type="date" wire:model.live="tripsPeriodTo"
                       class="w-full px-3 py-2 rounded-xl border border-gray-300 text-sm">
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="setTripsPeriod(30)"
                        class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium">30 {{ __('app.stats.clients.days') }}</button>
                <button type="button" wire:click="setTripsPeriod(90)"
                        class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium">90 {{ __('app.stats.clients.days') }}</button>
                <button type="button" wire:click="clearTripsPeriod"
                        class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium text-gray-600"
                        title="{{ __('app.stats.clients.all_time') }}">∞</button>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="bg-gray-50 border rounded-xl p-4">
                <p class="text-sm text-gray-500 mb-1">{{ __('app.truck.show.mileage_total_km') }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($driverTripsStats['total_km'], 0, ',', ' ') }} km</p>
            </div>
            <div class="bg-gray-50 border rounded-xl p-4">
                <p class="text-sm text-gray-500 mb-1">{{ __('app.truck.show.mileage_trips_count') }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ $driverTripsStats['trips_count'] }}</p>
            </div>
        </div>
        @if($driverTripsPaginator->total() > 0)
        <div class="flex flex-wrap items-center gap-2 mb-2">
            <span class="text-sm text-gray-600">{{ __('app.truck.show.mileage_per_page') }}</span>
            <select wire:model.live="driverTripsPerPage" class="rounded-lg border border-gray-300 text-sm py-1.5 px-2">
                <option value="10">10</option>
                <option value="15">15</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
        {{-- Мобильные карточки --}}
        <div class="md:hidden space-y-3">
            @foreach($driverTripsPaginator->items() as $t)
                <a href="{{ route('trips.show', $t['id']) }}" wire:navigate
                   class="block bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl p-4 transition">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-semibold text-blue-600">#{{ $t['id'] }}</span>
                        <span class="text-lg font-bold text-gray-900 tabular-nums">{{ number_format($t['distance_km'], 0, ',', ' ') }} km</span>
                    </div>
                    <div class="mt-2 flex justify-between text-sm text-gray-600">
                        <span>{{ __('app.truck.show.mileage_col_departure') }}: {{ $t['departure_date'] ? \Carbon\Carbon::parse($t['departure_date'])->format('d.m.Y') : '—' }}</span>
                        <span>{{ __('app.truck.show.mileage_col_return') }}: {{ $t['return_date'] ? \Carbon\Carbon::parse($t['return_date'])->format('d.m.Y') : '—' }}</span>
                    </div>
                </a>
            @endforeach
        </div>
        {{-- Десктоп: таблица --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full text-sm border border-gray-200 rounded-xl overflow-hidden">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700">{{ __('app.truck.show.mileage_col_trip') }}</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700">{{ __('app.truck.show.mileage_col_departure') }}</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700">{{ __('app.truck.show.mileage_col_return') }}</th>
                        <th class="px-4 py-2 text-right font-semibold text-gray-700">{{ __('app.truck.show.mileage_col_km') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($driverTripsPaginator->items() as $t)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2">
                            <a href="{{ route('trips.show', $t['id']) }}" wire:navigate class="text-blue-600 hover:underline font-medium">#{{ $t['id'] }}</a>
                        </td>
                        <td class="px-4 py-2 text-gray-700">{{ $t['departure_date'] ? \Carbon\Carbon::parse($t['departure_date'])->format('d.m.Y') : '—' }}</td>
                        <td class="px-4 py-2 text-gray-700">{{ $t['return_date'] ? \Carbon\Carbon::parse($t['return_date'])->format('d.m.Y') : '—' }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format($t['distance_km'], 0, ',', ' ') }} km</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3 flex flex-wrap items-center gap-2">
            @if($driverTripsPaginator->currentPage() > 1)
                <button type="button" wire:click="setDriverTripsPage({{ $driverTripsPaginator->currentPage() - 1 }})"
                        class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium">{{ __('app.pagination.previous') }}</button>
            @endif
            <span class="text-sm text-gray-600">
                {{ __('app.pagination.page_of', ['current' => $driverTripsPaginator->currentPage(), 'last' => $driverTripsPaginator->lastPage()]) }}
            </span>
            @if($driverTripsPaginator->currentPage() < $driverTripsPaginator->lastPage())
                <button type="button" wire:click="setDriverTripsPage({{ $driverTripsPaginator->currentPage() + 1 }})"
                        class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium">{{ __('app.pagination.next') }}</button>
            @endif
        </div>
        @else
        <p class="text-gray-500 text-sm">{{ __('app.driver.show.trips_no_trips') }}</p>
        @endif
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
