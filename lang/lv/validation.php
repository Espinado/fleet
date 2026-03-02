<?php

return [
    'required' => 'Šis lauks ir obligāts.',
    'email'    => 'Lūdzu, ievadiet derīgu e-pasta adresi.',
    'string'   => 'Vērtībai jābūt tekstam.',
    'max'      => [
        'string' => 'Nedrīkst būt garāks par :max rakstzīmēm.',
    ],
    'min'      => [
        'string' => 'Jābūt vismaz :min rakstzīmēm.',
        'numeric'=> 'Jābūt vismaz :min.',
    ],
    'confirmed' => 'Paroles apstiprinājums nesakrīt.',

    'attributes' => [
        'email'    => 'e-pasts',
        'password' => 'parole',
        'pin'      => 'PIN kods',

        // Driver create form
        'first_name'            => 'vārds',
        'last_name'             => 'uzvārds',
        'pers_code'             => 'personas kods',
        'citizenship_id'        => 'pilsonība',
        'phone'                 => 'tālrunis',
        'company'               => 'kompānija',
        'company_id'            => 'kompānija',

        'declared_country_id'   => 'deklarētās adreses valsts',
        'declared_city_id'      => 'deklarētās adreses pilsēta',
        'declared_street'       => 'deklarētās adreses iela',
        'declared_building'     => 'deklarētās adreses māja',
        'declared_room'         => 'deklarētās adreses dzīvoklis',
        'declared_postcode'     => 'deklarētās adreses pasta indekss',

        'actual_country_id'     => 'faktiskās adreses valsts',
        'actual_city_id'        => 'faktiskās adreses pilsēta',
        'actual_street'         => 'faktiskās adreses iela',
        'actual_building'       => 'faktiskās adreses māja',
        'actual_room'           => 'faktiskās adreses dzīvoklis',
        'actual_postcode'       => 'faktiskās adreses pasta indekss',

        'license_number'        => 'vadītāja apliecības numurs',
        'license_issued'        => 'vadītāja apliecības izdošanas datums',
        'license_end'           => 'vadītāja apliecības derīguma termiņš',
        'code95_issued'         => '95. koda izdošanas datums',
        'code95_end'            => '95. koda derīguma termiņš',
        'permit_issued'         => 'darba atļaujas izdošanas datums',
        'permit_expired'        => 'darba atļaujas derīguma termiņš',
        'medical_issued'        => 'medicīniskās izziņas datums',
        'medical_expired'       => 'medicīniskās izziņas derīguma termiņš',
        'medical_exam_passed'   => 'OVP medicīniskās pārbaudes datums',
        'medical_exam_expired'  => 'OVP medicīniskās pārbaudes derīguma termiņš',
        'declaration_issued'    => 'deklarācijas datums',
        'declaration_expired'   => 'deklarācijas derīguma termiņš',

        // Truck create/edit
        'brand'                 => 'marka',
        'model'                 => 'modelis',
        'plate'                 => 'numurzīme',
        'year'                  => 'gads',
        'inspection_issued'     => 'tehniskās apskates izsniegšanas datums',
        'inspection_expired'    => 'tehniskās apskates derīguma termiņš',
        'insurance_company'     => 'apdrošinātājs',
        'insurance_number'      => 'apdrošināšanas numurs',
        'insurance_issued'      => 'apdrošināšanas izsniegšanas datums',
        'insurance_expired'     => 'apdrošināšanas derīguma termiņš',
        'vin'                   => 'VIN numurs',
        'tech_passport_nr'      => 'tehniskās pases numurs',
        'tech_passport_issued'  => 'tehniskās pases izsniegšanas datums',
        'tech_passport_expired' => 'tehniskās pases derīguma termiņš',
        'tech_passport_photo'   => 'tehniskās pases foto',

        // Client edit
        'company_name'          => 'kompānijas nosaukums',
        'reg_nr'               => 'reģ. nr.',
        'representative'       => 'pārstāvis',
        'bank_name'             => 'banka',
        'swift'                 => 'SWIFT kods',
        'jur_country_id'        => 'juridiskās adreses valsts',
        'jur_city_id'           => 'juridiskās adreses pilsēta',
        'jur_address'           => 'juridiskā adrese',
        'jur_post_code'         => 'juridiskās adreses pasta indekss',
        'fiz_country_id'        => 'faktiskās adreses valsts',
        'fiz_city_id'           => 'faktiskās adreses pilsēta',
        'fiz_address'           => 'faktiskā adrese',
        'fiz_post_code'         => 'faktiskās adreses pasta indekss',
    ],
];

