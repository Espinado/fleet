<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Notification Channels
    |--------------------------------------------------------------------------
    */

    'default' => env('NOTIFICATION_MAIL_DRIVER', 'mail'),

    /*
    |--------------------------------------------------------------------------
    | Notification Channels
    |--------------------------------------------------------------------------
    */

    'channels' => [
        'mail' => [
            'driver' => 'mail',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'notifications',
        ],

        // Добавляем WebPush
        'webpush' => [
            'driver' => 'webpush',
        ],
    ],

];
