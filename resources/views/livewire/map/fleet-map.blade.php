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
        <div x-data x-init="window.dispatchEvent(new CustomEvent('fleet-map-dom-ready'))">
        <div class="mb-4" x-data="fleetMapSearch()" x-init="init()">
            <label for="fleet-map-search" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.map.search_label') }}</label>
            <div class="relative">
                <input type="text"
                       id="fleet-map-search"
                       x-model="query"
                       x-on:input.debounce.200ms="filter()"
                       placeholder="{{ __('app.map.search_placeholder') }}"
                       class="w-full max-w-md rounded-lg border border-gray-300 px-4 py-2 pr-10 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">🔍</span>
            </div>
            <template x-if="query.length > 0 && filtered.length > 0">
                <ul class="mt-2 max-w-md rounded-lg border border-gray-200 bg-white shadow-lg overflow-hidden divide-y divide-gray-100 max-h-60 overflow-y-auto">
                    <template x-for="u in filtered" :key="u.unit_id">
                        <li>
                            <button type="button"
                                    x-on:click="focusOn(u)"
                                    class="w-full text-left px-4 py-3 hover:bg-blue-50 transition flex justify-between items-center">
                                <span class="font-medium" x-text="u.number"></span>
                                <span class="text-sm text-gray-500" x-text="u.tooltip ? u.tooltip.replace(u.number + ' — ', '') : ''"></span>
                            </button>
                        </li>
                    </template>
                </ul>
            </template>
            <template x-if="query.length > 0 && filtered.length === 0">
                <p class="mt-2 text-sm text-gray-500">{{ __('app.map.search_no_results') }}</p>
            </template>
        </div>
        @php
            $leafletCss = config('mapon.use_local_leaflet') ? asset('vendor/leaflet/leaflet.css') : config('mapon.leaflet_css_url');
            $tileUrl = config('mapon.tile_layer_url');
            if (config('mapon.tile_use_proxy')) {
                $tileUrl = url('/map/tiles/{z}/{x}/{y}.png');
            }
            if (empty($tileUrl)) {
                $tileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            }
            $tileAttribution = config('mapon.tile_attribution', '');
        @endphp
        <link rel="stylesheet" href="{{ $leafletCss }}" crossorigin=""/>
        <style>.fleet-marker-tooltip { font-weight: 600; font-size: 12px; white-space: nowrap; }.fleet-map-circle-marker { background: none !important; border: none !important; }</style>
        <script type="application/json" id="fleet-map-data">{!! json_encode(['units' => $unitsForMap, 'tile_url' => $tileUrl, 'tile_attribution' => $tileAttribution]) !!}</script>
        <div id="fleet-map-container"
             wire:ignore
             class="relative z-0 rounded-xl overflow-hidden border border-gray-200 shadow-sm bg-gray-50"
             style="height: calc(100vh - 220px); min-height: 400px;">
        </div>
        {{-- Инициализация карты по событию fleet-map-dom-ready из layout --}}
        </div>
    @endif
</div>
