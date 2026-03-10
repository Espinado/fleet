<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base Mapon API URL
    |--------------------------------------------------------------------------
    */
    'base_url' => env('MAPON_API_BASE_URL', 'https://mapon.com/api/v1'),

    /*
    |--------------------------------------------------------------------------
    | API keys
    |--------------------------------------------------------------------------
    |
    | key         – fallback (старый вариант, оставляем для совместимости)
    | keys[ID]    – ключи по компаниям (trucks.company)
    |
    */

    // 🔙 старый вариант (НЕ УДАЛЯЕМ)
    'key' => env('MAPON_API_KEY', ''),

    // ✅ новый вариант: ключи по компаниям
    'keys' => [
        1 => env('MAPON_API_KEY_LAKNA', ''),
        2 => env('MAPON_API_KEY_PADEKS', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | CAN / odometer freshness
    |--------------------------------------------------------------------------
    */
    'can_stale_days'    => env('MAPON_CAN_STALE_DAYS', 2),
    'can_stale_minutes' => env('MAPON_CAN_STALE_MINUTES', 30),

    /*
    |--------------------------------------------------------------------------
    | Leaflet (карты) — свои домены / без CDN
    |--------------------------------------------------------------------------
    |
    | Если на проде блокируют unpkg.com — положите Leaflet в public/vendor/leaflet/
    | (npm run copy-leaflet) и задайте пути ниже или включите use_local_leaflet.
    |
    */
    'use_local_leaflet'   => env('MAPON_LEAFLET_LOCAL', false),
    'leaflet_js_url'      => env('MAPON_LEAFLET_JS_URL', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'),
    'leaflet_css_url'     => env('MAPON_LEAFLET_CSS_URL', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'),

    /*
    |--------------------------------------------------------------------------
    | Тайлы карты
    |--------------------------------------------------------------------------
    |
    | tile_layer_url: подставятся {z},{x},{y}. Если пусто — используется
    | прокси на своём домене: /map/tiles/{z}/{x}/{y} (см. маршрут).
    | Альтернативы: CartoDB, Yandex и др. — см. docs/MAP_PRODUCTION.md
    |
    */
    'tile_layer_url'      => env('MAPON_TILE_LAYER_URL', 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'),
    'tile_attribution'    => env('MAPON_TILE_ATTRIBUTION', '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'),
    'tile_use_proxy'      => env('MAPON_TILE_USE_PROXY', false),

];
