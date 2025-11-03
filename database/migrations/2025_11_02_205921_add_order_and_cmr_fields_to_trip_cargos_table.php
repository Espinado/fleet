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
             $table->string('order_file')->nullable()->after('cmr_file');
            $table->timestamp('order_created_at')->nullable()->after('order_file');
            $table->string('order_nr')->nullable()->after('order_created_at');

            $table->string('cmr_nr')->nullable()->after('cmr_file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trip_cargos', function (Blueprint $table) {
           $table->dropColumn(['order_file', 'order_created_at', 'order_nr', 'cmr_nr']);
        });
    }
};
