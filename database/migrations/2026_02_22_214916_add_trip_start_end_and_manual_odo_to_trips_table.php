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
    Schema::table('trips', function (Blueprint $table) {
        $table->timestamp('started_at')->nullable()->after('status');
        $table->timestamp('ended_at')->nullable()->after('started_at');

        $table->unsignedInteger('odo_start_km')->nullable()->after('ended_at');
        $table->unsignedInteger('odo_end_km')->nullable()->after('odo_start_km');
    });
}

public function down(): void
{
    Schema::table('trips', function (Blueprint $table) {
        $table->dropColumn(['started_at','ended_at','odo_start_km','odo_end_km']);
    });
}
};
