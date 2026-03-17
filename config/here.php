<?php

return [
    'api_key' => env('HERE_API_KEY', ''),
    'geocode_url' => env('HERE_GEOCODE_URL', 'https://geocode.search.hereapi.com/v1/geocode'),
    'routing_url' => env('HERE_ROUTING_URL', 'https://router.hereapi.com/v8/routes'),
    'timeout' => (int) env('HERE_TIMEOUT', 30),
    'transport_mode' => env('HERE_TRANSPORT_MODE', 'truck'), // car | truck
];
