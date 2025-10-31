<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Fleet Manager Progressive Web App
    |--------------------------------------------------------------------------
    */

    'name' => 'Fleet Manager',

    'manifest' => [
        'name' => env('APP_NAME', 'Fleet Manager'),
        'short_name' => 'Fleet',
        'start_url' => '/dashboard',
        'background_color' => '#0d6efd',
        'theme_color' => '#0d6efd',
        'display' => 'standalone',
        'orientation' => 'portrait',
        'status_bar' => 'black',

        // === Иконки приложения ===
        'icons' => [
            '72x72'   => ['path' => '/images/icons/fleet.png', 'purpose' => 'any'],
            '96x96'   => ['path' => '/images/icons/fleet.png', 'purpose' => 'any'],
            '128x128' => ['path' => '/images/icons/fleet.png', 'purpose' => 'any'],
            '144x144' => ['path' => '/images/icons/fleet.png', 'purpose' => 'any'],
            '152x152' => ['path' => '/images/icons/fleet.png', 'purpose' => 'any'],
            '192x192' => ['path' => '/images/icons/fleet.png', 'purpose' => 'any'],
            '384x384' => ['path' => '/images/icons/fleet.png', 'purpose' => 'any'],
            '512x512' => ['path' => '/images/icons/fleet.png', 'purpose' => 'any'],
        ],

        // === Splash-экраны (iOS) ===
        'splash' => [
            '640x1136'  => '/images/icons/fleet.png',
            '750x1334'  => '/images/icons/fleet.png',
            '828x1792'  => '/images/icons/fleet.png',
            '1125x2436' => '/images/icons/fleet.png',
            '1242x2208' => '/images/icons/fleet.png',
            '1242x2688' => '/images/icons/fleet.png',
            '1536x2048' => '/images/icons/fleet.png',
            '1668x2224' => '/images/icons/fleet.png',
            '1668x2388' => '/images/icons/fleet.png',
            '2048x2732' => '/images/icons/fleet.png',
        ],

        // === Быстрые ссылки (иконки на домашнем экране) ===
        'shortcuts' => [
            [
                'name' => 'Dashboard',
                'description' => 'View fleet dashboard',
                'url' => '/dashboard',
                'icons' => [
                    'src' => '/images/icons/fleet.png',
                    'purpose' => 'any',
                ],
            ],
            [
                'name' => 'Drivers',
                'description' => 'Manage drivers',
                'url' => '/drivers',
            ],
            [
                'name' => 'Trips',
                'description' => 'View and manage trips',
                'url' => '/trips',
            ],
        ],

        'custom' => [],
    ],
];
