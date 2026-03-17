<?php

return [
    'api_key' => env('OPENROUTESERVICE_API_KEY', ''),
    'base_url' => env('OPENROUTESERVICE_BASE_URL', 'https://api.openrouteservice.org'),
    'timeout' => (int) env('OPENROUTESERVICE_TIMEOUT', 15),
    // driving-hgv = грузовики: ограничения по скорости (напр. автобаны 85 km/h), запреты для HGV, габариты
    'profile' => env('OPENROUTESERVICE_DIRECTIONS_PROFILE', 'driving-hgv'),
];
