<?php
// config/trailer-types.php

return [
    // id => key
    'types' => [
        1 => 'cargo',      // тентованный
        2 => 'container',  // контейнеровоз
        3 => 'ref',        // рефрижератор
    ],

    // key => label (UI)
    'labels' => [
        'cargo'     => 'Tented (cargo)',
        'container' => 'Container',
        'ref'       => 'Refrigerator (ref)',
    ],

    'icons' => [
        'cargo'     => '🟦',
        'container' => '📦',
        'ref'       => '❄️',
    ],
];
