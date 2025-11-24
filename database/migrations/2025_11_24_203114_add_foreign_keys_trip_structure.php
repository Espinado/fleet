<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | trip_steps
        |--------------------------------------------------------------------------
        */
        Schema::table('trip_steps', function (Blueprint $table) {
            // индексы
            $table->index('client_id', 'ts_client_idx');
            $table->index('trip_cargo_id', 'ts_cargo_idx');

            // FK — если нет, MySQL просто проигнорирует повтор
            $table->foreign('client_id', 'ts_client_fk')
                ->references('id')->on('clients')
                ->nullOnDelete();

            $table->foreign('trip_cargo_id', 'ts_cargo_fk')
                ->references('id')->on('trip_cargos')
                ->cascadeOnDelete();
        });


        /*
        |--------------------------------------------------------------------------
        | trip_cargos
        |--------------------------------------------------------------------------
        */
        Schema::table('trip_cargos', function (Blueprint $table) {

            // индексы
            $table->index('loading_step_id', 'tc_load_idx');
            $table->index('unloading_step_id', 'tc_unload_idx');
            $table->index('customer_id', 'tc_customer_idx');
            $table->index('shipper_id', 'tc_shipper_idx');
            $table->index('consignee_id', 'tc_consignee_idx');

            // FK
            $table->foreign('loading_step_id', 'tc_load_fk')
                ->references('id')->on('trip_steps')
                ->nullOnDelete();

            $table->foreign('unloading_step_id', 'tc_unload_fk')
                ->references('id')->on('trip_steps')
                ->nullOnDelete();

            foreach (['customer_id', 'shipper_id', 'consignee_id'] as $col) {
                $table->foreign($col, 'tc_' . $col . '_fk')
                    ->references('id')->on('clients')
                    ->nullOnDelete();
            }
        });


        /*
        |--------------------------------------------------------------------------
        | trip_cargo_items
        |--------------------------------------------------------------------------
        */
        Schema::table('trip_cargo_items', function (Blueprint $table) {
            $table->index('trip_cargo_id', 'tci_cargo_idx');

            $table->foreign('trip_cargo_id', 'tci_cargo_fk')
                ->references('id')->on('trip_cargos')
                ->cascadeOnDelete();
        });
    }


    public function down(): void
    {
        // trip_steps
        Schema::table('trip_steps', function (Blueprint $table) {
            foreach (['ts_client_fk', 'ts_cargo_fk'] as $fk) {
                $table->dropForeign($fk);
            }
        });

        // trip_cargos
        Schema::table('trip_cargos', function (Blueprint $table) {
            foreach ([
                'tc_load_fk',
                'tc_unload_fk',
                'tc_customer_id_fk',
                'tc_shipper_id_fk',
                'tc_consignee_id_fk'
            ] as $fk) {
                $table->dropForeign($fk);
            }
        });

        // trip_cargo_items
        Schema::table('trip_cargo_items', function (Blueprint $table) {
            $table->dropForeign('tci_cargo_fk');
        });
    }
};
