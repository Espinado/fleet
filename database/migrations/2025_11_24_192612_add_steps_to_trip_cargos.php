<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::table('trip_cargos', function (Blueprint $table) {
            $table->unsignedBigInteger('loading_step_id')->nullable()->after('consignee_id');
            $table->unsignedBigInteger('unloading_step_id')->nullable()->after('loading_step_id');

            $table->foreign('loading_step_id')
                ->references('id')->on('trip_steps')
                ->nullOnDelete();

            $table->foreign('unloading_step_id')
                ->references('id')->on('trip_steps')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('trip_cargos', function (Blueprint $table) {
            $table->dropForeign(['loading_step_id']);
            $table->dropForeign(['unloading_step_id']);
            $table->dropColumn(['loading_step_id', 'unloading_step_id']);
        });
    }
};
