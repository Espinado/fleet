<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_expenses', function (Blueprint $table) {

            if (!Schema::hasColumn('trip_expenses', 'odometer_km')) {
                $table->decimal('odometer_km', 10, 1)->nullable()->after('expense_date');
            }

            if (!Schema::hasColumn('trip_expenses', 'odometer_source')) {
                $table->string('odometer_source', 20)->nullable()->after('odometer_km'); // manual|can|mileage
            }

            if (!Schema::hasColumn('trip_expenses', 'truck_odometer_event_id')) {
                $table->unsignedBigInteger('truck_odometer_event_id')->nullable()->after('odometer_source');
                $table->index('truck_odometer_event_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trip_expenses', function (Blueprint $table) {

            // index drop only if column exists
            if (Schema::hasColumn('trip_expenses', 'truck_odometer_event_id')) {
                // безопасно: если индекса нет, MySQL может ругаться — но обычно он есть
                try { $table->dropIndex(['truck_odometer_event_id']); } catch (\Throwable $e) {}
            }

            if (Schema::hasColumn('trip_expenses', 'truck_odometer_event_id')) {
                $table->dropColumn('truck_odometer_event_id');
            }

            if (Schema::hasColumn('trip_expenses', 'odometer_source')) {
                $table->dropColumn('odometer_source');
            }

            if (Schema::hasColumn('trip_expenses', 'odometer_km')) {
                $table->dropColumn('odometer_km');
            }
        });
    }
};
