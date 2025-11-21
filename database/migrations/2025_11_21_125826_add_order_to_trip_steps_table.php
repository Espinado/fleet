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
        Schema::table('trip_steps', function (Blueprint $table) {
            Schema::table('trip_steps', function (Blueprint $table) {
        $table->integer('order')->default(0)->after('type');
    });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::table('trip_steps', function (Blueprint $table) {
        $table->dropColumn('order');
    });
    }
};
