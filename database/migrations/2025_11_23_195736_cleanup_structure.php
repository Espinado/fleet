<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

 public function up(): void
{
    /** =========================
     *  TRIPS — очищаем
     * =========================*/
    Schema::table('trips', function (Blueprint $table) {

        // 1) Удаляем внешние ключи, если существуют
        $fks = [
            'trips_shipper_id_foreign',
            'trips_consignee_id_foreign',
        ];

        foreach ($fks as $fk) {
            try {
                $table->dropForeign($fk);
            } catch (\Exception $e) {}
        }

        // 2) Удаляем поля
        $drop = [
            'shipper_id',
            'consignee_id',
            'origin_country_id',
            'origin_city_id',
            'origin_address',
            'destination_country_id',
            'destination_city_id',
            'destination_address',
            'cargo_description',
            'cargo_packages',
            'cargo_weight',
            'cargo_volume',
            'cargo_marks',
            'cargo_instructions',
            'cargo_remarks',
            'price',
            'payment_terms',
            'payer_type_id'
        ];

        foreach ($drop as $col) {
            if (Schema::hasColumn('trips', $col)) {
                $table->dropColumn($col);
            }
        }
    });


    /** =========================
     *  TRIP_CARGOS — очищаем
     * =========================*/
    Schema::table('trip_cargos', function (Blueprint $table) {

        // FK на клиентов (вдруг надо)
        $fks = [
            'trip_cargos_shipper_id_foreign',
            'trip_cargos_consignee_id_foreign',
            'trip_cargos_customer_id_foreign',
        ];

        foreach ($fks as $fk) {
            try {
                $table->dropForeign($fk);
            } catch (\Exception $e) {}
        }

        $drop = [
            'cargo_description',
            'cargo_packages',
            'cargo_weight',
            'cargo_volume',
            'cargo_marks',
            'cargo_instructions',
            'cargo_remarks',
            'cargo_paletes',
            'cargo_netto_weight',
            'cargo_tonnes'
        ];

        foreach ($drop as $col) {
            if (Schema::hasColumn('trip_cargos', $col)) {
                $table->dropColumn($col);
            }
        }
    });


    /** =========================
     *  TRIP_STEPS — очищаем
     * =========================*/
    Schema::table('trip_steps', function (Blueprint $table) {
        if (Schema::hasColumn('trip_steps', 'sequence')) {
            $table->dropColumn('sequence');
        }
    });
}



    public function down(): void
    {
        /** ============
         *  TRIPS
         * ============*/
        Schema::table('trips', function (Blueprint $table) {

            // Восстановление для отката
            $table->unsignedBigInteger('shipper_id')->nullable();
            $table->unsignedBigInteger('consignee_id')->nullable();

            $table->unsignedSmallInteger('origin_country_id')->nullable();
            $table->unsignedSmallInteger('origin_city_id')->nullable();
            $table->string('origin_address')->nullable();

            $table->unsignedSmallInteger('destination_country_id')->nullable();
            $table->unsignedSmallInteger('destination_city_id')->nullable();
            $table->string('destination_address')->nullable();

            $table->string('cargo_description')->nullable();
            $table->integer('cargo_packages')->nullable();
            $table->decimal('cargo_weight', 10, 2)->nullable();
            $table->decimal('cargo_volume', 10, 2)->nullable();
            $table->string('cargo_marks')->nullable();
            $table->text('cargo_instructions')->nullable();
            $table->text('cargo_remarks')->nullable();

            $table->decimal('price', 10, 2)->nullable();
            $table->date('payment_terms')->nullable();
            $table->unsignedTinyInteger('payer_type_id')->nullable();
        });

        /** ============
         *  TRIP_CARGOS
         * ============*/
        Schema::table('trip_cargos', function (Blueprint $table) {

            $table->string('cargo_description')->nullable();
            $table->integer('cargo_packages')->nullable();
            $table->decimal('cargo_weight', 10, 2)->nullable();
            $table->decimal('cargo_volume', 10, 2)->nullable();
            $table->string('cargo_marks')->nullable();
            $table->text('cargo_instructions')->nullable();
            $table->text('cargo_remarks')->nullable();
            $table->integer('cargo_paletes')->nullable();
            $table->decimal('cargo_netto_weight', 10, 2)->nullable();
            $table->decimal('cargo_tonnes', 10, 2)->nullable();
        });

        /** ============
         *  TRIP_STEPS
         * ============*/
        Schema::table('trip_steps', function (Blueprint $table) {
            $table->unsignedInteger('sequence')->default(0);
        });
    }
};
