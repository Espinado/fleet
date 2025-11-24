<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1) TRIP_CARGO_ITEMS — EU-структура
        |--------------------------------------------------------------------------
        */
        Schema::table('trip_cargo_items', function (Blueprint $table) {

            // новые поля
            $table->integer('pallets')->nullable()->after('packages');
            $table->integer('units')->nullable()->after('pallets');

            $table->decimal('gross_weight', 10, 2)->nullable()->after('cargo_netto_weight');
            $table->decimal('net_weight', 10, 2)->nullable()->after('gross_weight');
            $table->decimal('tonnes', 10, 3)->nullable()->after('net_weight');

            $table->decimal('loading_meters', 10, 2)->nullable()->after('volume');
            $table->string('hazmat')->nullable()->after('loading_meters');
            $table->string('temperature')->nullable()->after('hazmat');
            $table->boolean('stackable')->default(0)->after('temperature');

            // удаляем старые поля
            $table->dropColumn([
                'cargo_paletes',
                'cargo_tonnes',
                'weight',
                'cargo_netto_weight',
            ]);
        });


        /*
        |--------------------------------------------------------------------------
        | 2) TRIP_STEPS — добавляем TIME (вариант B)
        |--------------------------------------------------------------------------
        */
        Schema::table('trip_steps', function (Blueprint $table) {
            $table->time('time')->nullable()->after('date');
        });


        /*
        |--------------------------------------------------------------------------
        | 3) TRIP_CARGOS — удаляем старые поля загрузок/разгрузок
        |--------------------------------------------------------------------------
        */
        Schema::table('trip_cargos', function (Blueprint $table) {
            $table->dropColumn([
                'loading_country_id',
                'loading_city_id',
                'loading_address',
                'loading_date',
                'unloading_country_id',
                'unloading_city_id',
                'unloading_address',
                'unloading_date',
            ]);
        });
    }


    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Обратный откат (минимальный, только базовое восстановление)
        |--------------------------------------------------------------------------
        */

        Schema::table('trip_steps', function (Blueprint $table) {
            $table->dropColumn('time');
        });

        Schema::table('trip_cargo_items', function (Blueprint $table) {
            $table->dropColumn([
                'pallets', 'units', 'gross_weight', 'net_weight', 'tonnes',
                'loading_meters', 'hazmat', 'temperature', 'stackable',
            ]);

            // вернуть старые поля (минимальная заглушка)
            $table->decimal('cargo_paletes', 10, 2)->default(0);
            $table->decimal('cargo_tonnes', 10, 2)->default(0);
            $table->decimal('weight', 10, 2)->default(0);
            $table->decimal('cargo_netto_weight', 10, 2)->default(0);
        });

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
