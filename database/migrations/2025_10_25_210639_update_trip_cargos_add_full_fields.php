<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_cargos', function (Blueprint $table) {
            // маршруты
            $table->smallInteger('loading_country_id')->unsigned()->nullable()->after('consignee_id');
            $table->smallInteger('loading_city_id')->unsigned()->nullable();
            $table->string('loading_address')->nullable();
            $table->date('loading_date')->nullable();

            $table->smallInteger('unloading_country_id')->unsigned()->nullable();
            $table->smallInteger('unloading_city_id')->unsigned()->nullable();
            $table->string('unloading_address')->nullable();
            $table->date('unloading_date')->nullable();

            // оплата
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 10)->default('EUR');
            $table->date('payment_terms')->nullable();
            $table->unsignedSmallInteger('payer_type_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('trip_cargos', function (Blueprint $table) {
            $table->dropColumn([
                'loading_country_id', 'loading_city_id', 'loading_address', 'loading_date',
                'unloading_country_id', 'unloading_city_id', 'unloading_address', 'unloading_date',
                'price', 'currency', 'payment_terms', 'payer_type_id'
            ]);
        });
    }
};
