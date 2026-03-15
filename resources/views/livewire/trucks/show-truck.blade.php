<div class="p-6 bg-white rounded-2xl shadow-lg max-w-6xl mx-auto mt-10 relative">
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
    {{-- Augšējā darbību josla --}}
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">
            {{ $truck->brand }} {{ $truck->model }} <span class="text-gray-500">({{ $truck->plate }})</span>
        </h1>

        <div class="flex items-center gap-4">
            {{-- Poga "Labot" --}}
            <a href="{{ route('trucks.edit', $truck->id) }}"
               class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                ✏️ {{ __('app.truck.show.edit') }}
            </a>

        </div>
    </div>

    {{-- Pamatinformācija --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="space-y-2">
            <p><strong>{{ __('app.truck.show.year') }}:</strong> {{ $truck->year }}</p>
            <p><strong>{{ __('app.truck.show.status') }}:</strong>
                {{ $truck->status ? '✅ ' . __('app.trucks.status_active') : '❌ ' . __('app.trucks.status_inactive') }}
            </p>
            <p><strong>{{ __('app.truck.show.active') }}:</strong>
                {{ $truck->is_active ? '✅ ' . __('app.truck.show.yes') : '❌ ' . __('app.truck.show.no') }}
            </p>
            <p><strong>VIN:</strong> {{ $truck->vin }}</p>
            <p><strong>{{ __('app.truck.show.company') }}:</strong> {{ $truck->company?->name ?? '—' }}</p>
        </div>

        <div class="space-y-2">
            <p><strong>{{ __('app.truck.show.ins_company') }}:</strong> {{ $truck->insurance_company }}</p>
            <p><strong>{{ __('app.truck.show.ins_number') }}:</strong> {{ $truck->insurance_number }}</p>
            <p><strong>{{ __('app.truck.show.ins_valid') }}:</strong> {{  $fmt($truck->insurance_issued) }} → {{ $fmt($truck->insurance_expired) }}</p>
        </div>
    </div>
   {{-- MAPON --}}
<div class="mb-8">
    <div class="flex items-center justify-between border-b pb-1 mb-3">
        <h2 class="text-xl font-semibold">Mapon</h2>

        <button
            type="button"
            wire:click="refreshMaponData"
            wire:loading.attr="disabled"
            class="px-3 py-1.5 text-sm font-semibold rounded-lg bg-gray-100 hover:bg-gray-200
                   text-gray-700 transition disabled:opacity-60 disabled:cursor-not-allowed"
        >
            <span wire:loading.remove wire:target="refreshMaponData">🔄 {{ __('app.truck.show.mapon_refresh') }}</span>
            <span wire:loading wire:target="refreshMaponData">⏳ {{ __('app.truck.show.mapon_refreshing') }}</span>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Unit ID --}}
        <div class="bg-gray-50 border rounded-xl p-4">
            <p class="text-sm text-gray-500 mb-1">{{ __('app.truck.show.mapon_unit_id') }}</p>
            <p class="text-base font-semibold text-gray-800">
                {{ $truck->mapon_unit_id ?? '—' }}
            </p>
        </div>

      {{-- Odometer (CAN → mileage) --}}
<div class="bg-gray-50 border rounded-xl p-4">
    <p class="text-sm text-gray-500 mb-1">{{ __('app.truck.show.mapon_odo') }}</p>

   @if($maponError && $maponCanMileageKm === null)
    <p class="text-sm font-semibold text-red-700">{{ $maponError }}</p>
@else
        <p class="text-base font-semibold text-gray-800">
            {{ $maponCanMileageKm !== null ? number_format($maponCanMileageKm, 0, '.', ' ') . ' km' : '—' }}
        </p>

        @php
            $at = $maponCanAt ? \Carbon\Carbon::parse($maponCanAt) : null;
            $daysAgo = $at ? $at->diffInDays(now()) : null;
            $minutesAgo = $at ? $at->diffInMinutes(now()) : null;

            $staleDays = (int) config('mapon.can_stale_days', 2);
            $staleMinutes = (int) config('mapon.can_stale_minutes', 30);

            // если источник = CAN (т.е. can.odom.value был) — обычно maponCanAt будет can.odom.gmt
            // если CAN нет — maponCanAt мы ставим в last_update
            $isStale = false;
            if ($minutesAgo !== null && $staleMinutes > 0 && $minutesAgo >= $staleMinutes) $isStale = true;
            if ($daysAgo !== null && $staleDays > 0 && $daysAgo >= $staleDays) $isStale = true;

            // Попытка определить источник по наличию CAN timestamp:
            // если can.odom.gmt пустой, а last_update есть — вероятнее всего mileage.
            $source = ($maponCanAt && $maponLastUpdate && $maponCanAt === $maponLastUpdate) ? 'mileage' : 'CAN';
        @endphp

        {{-- Source badge --}}
        <div class="mt-2 inline-flex items-center gap-1 px-2 py-1
                    text-xs font-semibold rounded-full
                    bg-gray-200 text-gray-700">
            📡 {{ __('app.truck.show.mapon_source') }}: {{ $source }}
        </div>

        {{-- Updated / stale badge --}}
        @if($at)
            @if($isStale)
                <div class="mt-2 inline-flex items-center gap-1 px-2 py-1
                            text-xs font-semibold rounded-full
                            bg-red-100 text-red-700">
                    🚨 {{ __('app.truck.show.mapon_not_updated') }} ({{ $at->diffForHumans() }})
                </div>
            @else
                <div class="mt-2 inline-flex items-center gap-1 px-2 py-1
                            text-xs font-semibold rounded-full
                            bg-green-100 text-green-700">
                    ✅ {{ __('app.truck.show.mapon_updated') }} {{ $at->diffForHumans() }}
                </div>
            @endif

            <p class="text-xs text-gray-400 mt-2">
                {{ __('app.truck.show.mapon_odo_at') }}: {{ $maponCanAt }}
            </p>
        @else
            <div class="mt-2 inline-flex items-center gap-1 px-2 py-1
                        text-xs font-semibold rounded-full
                        bg-yellow-100 text-yellow-800">
                ⚠️ {{ __('app.truck.show.mapon_no_timestamp') }}
            </div>
        @endif

        @if($maponLastUpdate)
            <p class="text-xs text-gray-400 mt-1">
                {{ __('app.truck.show.mapon_last_update') }}: {{ $maponLastUpdate }}
            </p>
        @endif
    @endif
</div>

        {{-- Unit name --}}
        <div class="bg-gray-50 border rounded-xl p-4">
            <p class="text-sm text-gray-500 mb-1">{{ __('app.truck.show.mapon_unit_name') }}</p>
            <p class="text-base font-semibold text-gray-800">
                {{ $maponUnitName ?? '—' }}
            </p>
        </div>
    </div>

    {{-- Карта позиции (когда есть lat/lng из Mapon). Контейнер wire:ignore — при обновлении меняется только маркер. --}}
    @if($maponLat !== null && $maponLng !== null)
        @php
            $leafletCss = config('mapon.use_local_leaflet') ? asset('vendor/leaflet/leaflet.css') : config('mapon.leaflet_css_url');
            $leafletJs  = config('mapon.use_local_leaflet') ? asset('vendor/leaflet/leaflet.js') : config('mapon.leaflet_js_url');
            $tileUrl = config('mapon.tile_layer_url');
            if (config('mapon.tile_use_proxy')) { $tileUrl = url('/map/tiles/{z}/{x}/{y}.png'); }
            if (empty($tileUrl)) { $tileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'; }
            $tileAttribution = config('mapon.tile_attribution', '');
        @endphp
        {{-- Скрытый элемент с координатами — его обновляет Livewire при Refresh, по нему двигаем маркер --}}
        <div id="truck-map-coords-{{ $truck->id }}"
             data-truck-id="{{ $truck->id }}"
             data-lat="{{ $maponLat }}"
             data-lng="{{ $maponLng }}"
             data-tooltip="{{ e($maponMarkerTooltip) }}"
             data-state="{{ $maponStateName ?? 'standing' }}"
             class="hidden"
             aria-hidden="true"></div>
        <div id="truck-map-outer-{{ $truck->id }}" class="mt-6" wire:ignore>
            <p class="text-sm text-gray-500 mb-2">{{ __('app.truck.show.mapon_map_title') }}</p>
            <div id="truck-map-wrap-{{ $truck->id }}"
                 class="rounded-xl overflow-hidden border border-gray-200 shadow-sm bg-gray-50"
                 style="height: 320px;"
                 data-tile-url="{{ e($tileUrl) }}"
                 data-tile-attribution="{{ e($tileAttribution) }}">
            </div>
        </div>
        <link rel="stylesheet" href="{{ $leafletCss }}" crossorigin=""/>
        <style>
            .truck-marker-tooltip { font-weight: 600; font-size: 13px; white-space: nowrap; }
        </style>
        @push('scripts')
        <script src="{{ $leafletJs }}" crossorigin=""></script>
        <script>
            (function() {
                var truckId = {{ $truck->id }};
                var coordsEl = document.getElementById('truck-map-coords-' + truckId);
                var wrap = document.getElementById('truck-map-wrap-' + truckId);
                if (!coordsEl || !wrap) return;
                var lat = parseFloat(coordsEl.getAttribute('data-lat'));
                var lng = parseFloat(coordsEl.getAttribute('data-lng'));
                if (isNaN(lat) || isNaN(lng)) return;
                var tileUrl = wrap.getAttribute('data-tile-url') || 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
                var tileAttribution = wrap.getAttribute('data-tile-attribution') || '';

                var map = L.map(wrap).setView([lat, lng], 15);
                L.tileLayer(tileUrl, { attribution: tileAttribution }).addTo(map);
                var state = (coordsEl.getAttribute('data-state') || 'standing').toLowerCase();
                var isMoving = state === 'moving';
                var marker = L.circleMarker([lat, lng], {
                    radius: 12,
                    fillColor: isMoving ? '#22c55e' : '#6b7280',
                    color: '#fff',
                    weight: 2,
                    fillOpacity: 1
                }).addTo(map);
                var tooltipText = (coordsEl.getAttribute('data-tooltip') || '').trim();
                if (tooltipText) {
                    marker.bindTooltip(tooltipText, {
                        permanent: true,
                        direction: 'top',
                        offset: [0, -16],
                        className: 'truck-marker-tooltip'
                    });
                }

                window.__truckMaps = window.__truckMaps || {};
                window.__truckMaps[truckId] = { map: map, marker: marker };

                Livewire.hook('morph.updated', function() {
                    var el = document.getElementById('truck-map-coords-' + truckId);
                    var stored = window.__truckMaps && window.__truckMaps[truckId];
                    if (!el || !stored) return;
                    var newLat = parseFloat(el.getAttribute('data-lat'));
                    var newLng = parseFloat(el.getAttribute('data-lng'));
                    if (isNaN(newLat) || isNaN(newLng)) return;
                    stored.marker.setLatLng([newLat, newLng]);
                    stored.map.panTo([newLat, newLng]);
                    var newState = (el.getAttribute('data-state') || 'standing').toLowerCase();
                    var moving = newState === 'moving';
                    if (stored.marker.setStyle) {
                        stored.marker.setStyle({ fillColor: moving ? '#22c55e' : '#6b7280' });
                    }
                    var newTooltip = (el.getAttribute('data-tooltip') || '').trim();
                    if (stored.marker.getTooltip()) {
                        stored.marker.setTooltipContent(newTooltip || ' ');
                    } else if (newTooltip) {
                        stored.marker.bindTooltip(newTooltip, {
                            permanent: true,
                            direction: 'top',
                            offset: [0, -16],
                            className: 'truck-marker-tooltip'
                        });
                    }
                });

                function destroyTruckMaps() {
                    if (!window.__truckMaps) return;
                    for (var id in window.__truckMaps) {
                        try {
                            window.__truckMaps[id].map.remove();
                        } catch (e) {}
                        var outer = document.getElementById('truck-map-outer-' + id);
                        if (outer && outer.parentNode) outer.parentNode.removeChild(outer);
                    }
                    window.__truckMaps = {};
                }
                if (!window.__truckMapsCleanupRegistered) {
                    window.__truckMapsCleanupRegistered = true;
                    document.addEventListener('livewire:navigate', destroyTruckMaps);
                }
            })();
        </script>
        @endpush
    @elseif($truck->mapon_unit_id && $maponError)
        <p class="mt-3 text-sm text-amber-600">{{ __('app.truck.show.mapon_no_position') }}</p>
    @endif

    <div wire:loading wire:target="refreshMaponData" class="mt-3 text-sm text-gray-500 animate-pulse">
        {{ __('app.truck.show.mapon_loading') }}
    </div>
</div>

    {{-- Пробег за период --}}
    <div class="mb-8">
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
                        class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium">
                    30 {{ __('app.stats.clients.days') }}
                </button>
                <button type="button" wire:click="setMileagePeriod(90)"
                        class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium">
                    90 {{ __('app.stats.clients.days') }}
                </button>
                <button type="button" wire:click="clearMileagePeriod"
                        class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium text-gray-600"
                        title="{{ __('app.stats.clients.all_time') }}">
                    ∞
                </button>
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

    {{-- Техосмотр --}}
   <div class="mb-8">
    <h2 class="text-xl font-semibold mb-2 border-b pb-1">{{ __('app.truck.show.inspection_title') }}</h2>
    <p><strong>{{ __('app.truck.show.issued') }}:</strong> {{ $fmt($truck->inspection_issued) }}</p>
    <p><strong>{{ __('app.truck.show.expires') }}:</strong> {{ $fmt($truck->inspection_expired) }}</p>
</div>

    {{-- License --}}
<div class="bg-gray-50 border rounded-xl p-4">
    <p class="text-sm text-gray-500 mb-1">{{ __('app.truck.show.license_expired_title') }}</p>
    <p class="text-base font-semibold text-gray-800">
        {{ $truck->license_expired ? $truck->license_expired->format('d.m.Y') : '—' }}
    </p>

    @if($truck->license_expired)
        @if($truck->license_expired->isPast())
            <span class="inline-flex mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">
                {{ __('app.truck.show.license_expired') }}
            </span>
        @elseif($truck->license_expired->diffInDays(now()) <= 30)
            <span class="inline-flex mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                {{ __('app.truck.show.license_expires_soon') }}
            </span>
        @else
            <span class="inline-flex mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                {{ __('app.truck.show.license_valid') }}
            </span>
        @endif
    @endif
</div>


    {{-- Техпаспорт --}}
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-2 border-b pb-1">{{ __('app.truck.show.tech_passport_title') }}</h2>
        <p><strong>{{ __('app.truck.show.tech_number') }}:</strong> {{ $truck->tech_passport_nr ?? '-' }}</p>
       <p>
    <strong>{{ __('app.truck.show.issued') }}:</strong>
    {{ $fmt($truck->tech_passport_issued) }}
</p>

<p>
    <strong>{{ __('app.truck.show.expires') }}:</strong>
    {{ $fmt($truck->tech_passport_expired) }}
</p>
    </div>

    {{-- Фото --}}
    <div>
        <h2 class="text-xl font-semibold mb-3 border-b pb-1">{{ __('app.truck.show.docs_title') }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-3">
            @php $photoUrl = $truck->tech_passport_photo_url; @endphp
            <div class="border rounded-lg p-2 flex items-center justify-center h-48 bg-gray-50">
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="Tech Passport Photo"
                         class="h-full object-contain rounded">
                @else
                    <span class="text-gray-400 text-sm">{{ __('app.truck.show.no_image') }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-10 flex justify-between items-center">
        <a href="{{ route('trucks.index') }}"
           class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
            ← {{ __('app.truck.show.back') }}
        </a>
    </div>
</div>


