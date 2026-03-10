<div class="p-6 max-w-full mx-auto">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800">{{ $mapTitle }}</h1>
        <button type="button"
                wire:click="refreshUnits"
                wire:loading.attr="disabled"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-60 text-sm font-medium">
            <span wire:loading.remove wire:target="refreshUnits">🔄 {{ __('app.map.refresh') }}</span>
            <span wire:loading wire:target="refreshUnits">⏳ {{ __('app.map.refreshing') }}</span>
        </button>
    </div>

    @if(empty($unitsForMap))
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 text-amber-800">
            {{ __('app.map.no_units') }}
        </div>
    @else
        <div id="fleet-map-container"
             class="rounded-xl overflow-hidden border border-gray-200 shadow-sm bg-gray-50"
             style="height: calc(100vh - 220px); min-height: 400px;"
             data-units="{{ e(json_encode($unitsForMap)) }}">
        </div>

        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        <style>.fleet-marker-tooltip { font-weight: 600; font-size: 12px; white-space: nowrap; }</style>
        @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            (function() {
                var el = document.getElementById('fleet-map-container');
                if (!el) return;
                var raw = el.getAttribute('data-units');
                var units = [];
                try {
                    units = JSON.parse(raw || '[]');
                } catch (e) {
                    return;
                }
                if (units.length === 0) return;

                var map = L.map(el).setView([units[0].lat, units[0].lng], 6);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(map);

                var bounds = L.latLngBounds();
                for (var i = 0; i < units.length; i++) {
                    var u = units[i];
                    var latlng = [u.lat, u.lng];
                    bounds.extend(latlng);
                    var marker = L.marker(latlng).addTo(map);
                    if (u.tooltip) {
                        marker.bindTooltip(u.tooltip, {
                            permanent: true,
                            direction: 'top',
                            offset: [0, -22],
                            className: 'fleet-marker-tooltip'
                        });
                    }
                }
                if (units.length > 1) {
                    map.fitBounds(bounds, { padding: [40, 40], maxZoom: 12 });
                }
            })();
        </script>
        @endpush
    @endif
</div>
