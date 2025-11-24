<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('trip_cargos', function (Blueprint $table) {

            $old = [
                'loading_country_id',
                'loading_city_id',
                'loading_address',
                'loading_date',
                'unloading_country_id',
                'unloading_city_id',
                'unloading_address',
                'unloading_date',
            ];

            foreach ($old as $col) {
                if (Schema::hasColumn('trip_cargos', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('trip_cargos', function (Blueprint $table) {
            $table->smallInteger('loading_country_id')->nullable();
            $table->smallInteger('loading_city_id')->nullable();
            $table->string('loading_address')->nullable();
            $table->date('loading_date')->nullable();
            $table->smallInteger('unloading_country_id')->nullable();
            $table->smallInteger('unloading_city_id')->nullable();
            $table->string('unloading_address')->nullable();
            $table->date('unloading_date')->nullable();
        });
    }
};
