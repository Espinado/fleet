<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_expenses', function (Blueprint $table) {
            // ✅ liters for fuel/adblue/washer_fluid
            if (!Schema::hasColumn('trip_expenses', 'liters')) {
                // после amount (сумма €), чтобы логично читалось
                $table->decimal('liters', 10, 2)->nullable()->after('amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trip_expenses', function (Blueprint $table) {
            if (Schema::hasColumn('trip_expenses', 'liters')) {
                $table->dropColumn('liters');
            }
        });
    }
};
