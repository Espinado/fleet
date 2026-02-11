<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('truck_odometer_events', function (Blueprint $table) {
            $table->index(
                ['truck_id', 'driver_id', 'type', 'occurred_at'],
                'toe_truck_driver_type_date_idx'
            );
        });

        Schema::table('trip_cargos', function (Blueprint $table) {
            $table->index(
                ['trip_id'],
                'trip_cargos_trip_id_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('truck_odometer_events', function (Blueprint $table) {
            $table->dropIndex('toe_truck_driver_type_date_idx');
        });

        Schema::table('trip_cargos', function (Blueprint $table) {
            $table->dropIndex('trip_cargos_trip_id_idx');
        });
    }
};
