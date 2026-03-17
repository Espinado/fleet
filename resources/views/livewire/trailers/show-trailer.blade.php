<div class="p-8 bg-gray-100 min-h-screen flex justify-center">
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
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-7xl p-8">

        {{-- Augšējā darbību josla --}}
        <div class="flex justify-between items-start mb-10 gap-4">
            <h1 class="text-4xl font-extrabold text-gray-800">
                {{ $trailer->brand }} {{ $trailer->model }}
                <span class="text-gray-500 text-lg">({{ $trailer->plate }})</span>
            </h1>

            <div class="flex gap-3 mt-4 md:mt-0">
                <a href="{{ route('trailers.edit', $trailer->id) }}"
                   class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition shadow-md">
                    ✏️ {{ __('app.trailer.show.edit') }}
                </a>
            </div>
        </div>

        {{-- Pamatinformācija --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-6 rounded-2xl shadow-inner space-y-3">
                <p><strong>{{ __('app.trailer.show.year') }}:</strong> {{ $trailer->year }}</p>
                <p><strong>{{ __('app.trailer.show.status') }}:</strong>
                    {{ $trailer->status ? '✅ ' . __('app.trailers.status_active') : '❌ ' . __('app.trailers.status_inactive') }}
                </p>
                <p><strong>{{ __('app.trailers.col_active') }}:</strong>
                    {{ $trailer->is_active ? '✅ ' . __('app.trailer.show.yes') : '❌ ' . __('app.trailer.show.no') }}
                </p>
                <p><strong>VIN:</strong> {{ $trailer->vin }}</p>
                <p>
                    <strong>{{ __('app.trailer.show.type') }}:</strong>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-semibold
                                 bg-white border border-gray-200 shadow-sm">
                        <span class="text-base">{{ $trailer->type_icon ?? '📦' }}</span>
                        <span>{{ $trailer->type_label ?? '—' }}</span>
                        @if($trailer->type_key)
                            <span class="text-xs font-medium text-gray-500">({{ $trailer->type_key }})</span>
                        @endif
                    </span>
                </p>
                 <p><strong>{{ __('app.trailer.show.company') }}:</strong>  {{ $trailer->company?->name ?? '—' }}</p>
            </div>

            <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-6 rounded-2xl shadow-inner space-y-3">
                <p><strong>{{ __('app.trailer.show.ins_company') }}:</strong> {{ $trailer->insurance_company }}</p>
                <p><strong>{{ __('app.trailer.show.ins_number') }}:</strong> {{ $trailer->insurance_number }}</p>
                <p><strong>{{ __('app.trailer.show.ins_valid') }}:</strong> {{ $fmt($trailer->insurance_issued) }} → {{ $fmt($trailer->insurance_expired) }}</p>
            </div>
        </div>

        {{-- Следующее ТО / Записи ТО --}}
        <div class="bg-gray-50 p-6 rounded-2xl shadow-inner mb-10">
            @if($trailer->next_service_date || $trailer->next_service_km)
                <div class="rounded-xl bg-amber-50 border border-amber-200 p-4 mb-4">
                    <p class="text-sm font-medium text-amber-900 mb-1">{{ __('app.maintenance.next_service_title') }}</p>
                    <div class="flex flex-wrap gap-4 text-sm text-amber-800">
                        @if($trailer->next_service_date)
                            <span class="tabular-nums">{{ __('app.maintenance.due_by_date') }}: {{ $trailer->next_service_date->format('d.m.Y') }}</span>
                        @endif
                        @if($trailer->next_service_km)
                            <span class="tabular-nums">{{ __('app.maintenance.due_by_km') }}: {{ number_format($trailer->next_service_km, 0, '.', ' ') }} km</span>
                        @endif
                    </div>
                </div>
            @endif
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <h2 class="text-2xl font-semibold border-b pb-2">{{ __('app.maintenance_record.journal_title') }}</h2>
                <a href="{{ route('maintenance.records.create', ['trailer_id' => $trailer->id]) }}" wire:navigate
                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium min-h-[44px] inline-flex items-center">
                    {{ __('app.maintenance_record.add_record') }}
                </a>
            </div>
            @if($trailer->maintenanceRecords->isNotEmpty())
                <ul class="divide-y divide-gray-200 rounded-xl overflow-hidden bg-white border border-gray-200">
                    @foreach($trailer->maintenanceRecords as $rec)
                        <li>
                            <a href="{{ route('maintenance.records.show', $rec) }}" wire:navigate
                               class="block px-4 py-3 hover:bg-gray-50 grid grid-cols-1 sm:grid-cols-[1fr_8rem_1fr] items-center gap-2">
                                <span class="text-sm text-gray-900 tabular-nums">{{ $rec->performed_at->format('d.m.Y') }}</span>
                                <span class="text-sm text-gray-600">{{ $rec->odometer_km !== null ? number_format($rec->odometer_km, 0, '.', ' ') . ' km' : '—' }}</span>
                                <span class="text-sm text-gray-600 truncate">{{ Str::limit($rec->description, 40) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-gray-500">{{ __('app.maintenance_record.no_records') }}</p>
                <a href="{{ route('maintenance.records.create', ['trailer_id' => $trailer->id]) }}" wire:navigate class="text-sm text-blue-600 hover:underline mt-1 inline-block">
                    {{ __('app.maintenance_record.add_record') }} →
                </a>
            @endif
        </div>

        {{-- Tehniskā apskate --}}
        <div class="bg-gray-50 p-6 rounded-2xl shadow-inner mb-10">
            <h2 class="text-2xl font-semibold mb-4 border-b pb-2">{{ __('app.trailer.show.inspection') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <p><strong>{{ __('app.trailer.show.issued') }}:</strong> {{  $fmt($trailer->inspection_issued) }}</p>
                <p><strong>{{ __('app.trailer.show.expires') }}:</strong> {{ $fmt($trailer->inspection_expired) }}</p>
            </div>
        </div>

        {{-- Tehniskā pase --}}
        <div class="bg-gray-50 p-6 rounded-2xl shadow-inner mb-10">
            <h2 class="text-2xl font-semibold mb-4 border-b pb-2">{{ __('app.trailer.show.tech_passport') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <p><strong>{{ __('app.trailer.show.tech_number') }}:</strong> {{ $trailer->tech_passport_nr ?? '-' }}</p>
                <p><strong>{{ __('app.trailer.show.issued') }}:</strong> {{  $fmt($trailer->tech_passport_issued ?? null) }}</p>
                <p><strong>{{ __('app.trailer.show.expires') }}:</strong> {{ $fmt($trailer->tech_passport_expired ?? null) }}</p>
            </div>
        </div>

        {{-- Dokumenti / foto --}}
        <div class="bg-gray-50 p-6 rounded-2xl shadow-inner mb-10">
            <h2 class="text-2xl font-semibold mb-4 border-b pb-2">{{ __('app.trailer.show.docs') }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-4">
                @php $photoUrl = $trailer->tech_passport_photo_url; @endphp
                <div class="border rounded-2xl p-2 flex items-center justify-center h-56 bg-white shadow-sm">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="Tech Passport Photo"
                             class="h-full object-contain rounded-xl">
                    @else
                        <span class="text-gray-400 text-sm">{{ __('app.trailer.show.no_image') }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Пробег за период (выезд из гаража — заезд в гараж) --}}
        <div class="mb-10">
            <h2 class="text-xl font-semibold mb-3 border-b pb-1">{{ __('app.truck.show.mileage_period_title') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end mb-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('app.truck.show.mileage_date_departure') }}</label>
                    <input type="date" wire:model.live="mileagePeriodFrom"
                           class="w-full px-3 py-2 rounded-xl border border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('app.truck.show.mileage_date_return') }}</label>
                    <input type="date" wire:model.live="mileagePeriodTo"
                           class="w-full px-3 py-2 rounded-xl border border-gray-300 text-sm">
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" wire:click="setMileagePeriod(30)"
                            class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium">30 {{ __('app.stats.clients.days') }}</button>
                    <button type="button" wire:click="setMileagePeriod(90)"
                            class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium">90 {{ __('app.stats.clients.days') }}</button>
                    <button type="button" wire:click="clearMileagePeriod"
                            class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium text-gray-600"
                            title="{{ __('app.stats.clients.all_time') }}">∞</button>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="bg-gray-50 border rounded-xl p-4">
                    <p class="text-sm text-gray-500 mb-1">{{ __('app.truck.show.mileage_total_km') }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($mileageStats['total_km'], 0, ',', ' ') }} km</p>
                </div>
                <div class="bg-gray-50 border rounded-xl p-4">
                    <p class="text-sm text-gray-500 mb-1">{{ __('app.truck.show.mileage_trips_count') }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $mileageStats['trips_count'] }}</p>
                </div>
            </div>
            @if($mileageTripsPaginator->total() > 0)
            <div class="flex flex-wrap items-center gap-2 mb-2">
                <span class="text-sm text-gray-600">{{ __('app.truck.show.mileage_per_page') }}</span>
                <select wire:model.live="mileageTripsPerPage" class="rounded-lg border border-gray-300 text-sm py-1.5 px-2">
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
            {{-- Мобильные карточки --}}
            <div class="md:hidden space-y-3">
                @foreach($mileageTripsPaginator->items() as $t)
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
                        @foreach($mileageTripsPaginator->items() as $t)
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
                @if($mileageTripsPaginator->currentPage() > 1)
                    <button type="button" wire:click="setMileageTripsPage({{ $mileageTripsPaginator->currentPage() - 1 }})"
                            class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium">{{ __('app.pagination.previous') }}</button>
                @endif
                <span class="text-sm text-gray-600">
                    {{ __('app.pagination.page_of', ['current' => $mileageTripsPaginator->currentPage(), 'last' => $mileageTripsPaginator->lastPage()]) }}
                </span>
                @if($mileageTripsPaginator->currentPage() < $mileageTripsPaginator->lastPage())
                    <button type="button" wire:click="setMileageTripsPage({{ $mileageTripsPaginator->currentPage() + 1 }})"
                            class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium">{{ __('app.pagination.next') }}</button>
                @endif
            </div>
            @else
            <p class="text-gray-500 text-sm">{{ __('app.truck.show.mileage_no_trips') }}</p>
            @endif
        </div>

       <div class="flex justify-between items-center mt-6">
    <a href="{{ route('trailers.index') }}"
       class="px-6 py-2 bg-gray-200 text-gray-800 font-semibold rounded-xl hover:bg-gray-300 transition shadow-md">
        ← {{ __('app.trailer.show.back') }}
    </a>
</div>

    </div>

</div>
