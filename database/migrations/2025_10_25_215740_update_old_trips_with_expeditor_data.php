<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $trips = DB::table('trips')->get();

        foreach ($trips as $trip) {
            $exp = config("companies.{$trip->expeditor_id}");

            if (!$exp) continue;

            DB::table('trips')
                ->where('id', $trip->id)
                ->update([
                    'expeditor_name'      => $exp['name'] ?? $trip->expeditor_name,
                    'expeditor_reg_nr'    => $exp['reg_nr'] ?? $trip->expeditor_reg_nr,
                    'expeditor_country'   => $exp['country'] ?? $trip->expeditor_country,
                    'expeditor_city'      => $exp['city'] ?? $trip->expeditor_city,
                    'expeditor_address'   => $exp['address'] ?? $trip->expeditor_address,
                    'expeditor_post_code' => $exp['post_code'] ?? $trip->expeditor_post_code,
                    'expeditor_email'     => $exp['email'] ?? $trip->expeditor_email,
                    'expeditor_phone'     => $exp['phone'] ?? $trip->expeditor_phone,
                ]);
        }
    }

    public function down(): void
    {
        // Ничего не откатываем, т.к. обновление данных необратимо
    }
};