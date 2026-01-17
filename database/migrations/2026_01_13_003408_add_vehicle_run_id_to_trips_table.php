<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
   public function up(): void
{
    Schema::table('trips', function (Blueprint $table) {
        if (!Schema::hasColumn('trips', 'vehicle_run_id')) {
            $table->unsignedBigInteger('vehicle_run_id')
                ->nullable()
                ->after('truck_id');
        }

        $table->index('vehicle_run_id');
    });
}

    public function down(): void
    {
        $db = DB::getDatabaseName();

        // FK
        $hasFk = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', $db)
            ->where('TABLE_NAME', 'trips')
            ->where('COLUMN_NAME', 'vehicle_run_id')
            ->where('CONSTRAINT_NAME', 'trips_vehicle_run_id_foreign')
            ->exists();

        if ($hasFk) {
            Schema::table('trips', function (Blueprint $table) {
                try { $table->dropForeign('trips_vehicle_run_id_foreign'); } catch (\Throwable $e) {}
            });
        }

        // index
        $hasIndex = DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', $db)
            ->where('TABLE_NAME', 'trips')
            ->where('INDEX_NAME', 'trips_vehicle_run_id_index')
            ->exists();

        if ($hasIndex) {
            Schema::table('trips', function (Blueprint $table) {
                try { $table->dropIndex('trips_vehicle_run_id_index'); } catch (\Throwable $e) {}
            });
        }

        // колонку обычно НЕ удаляем на проде (опасно), но если хочешь — раскомментируй:
        /*
        if (Schema::hasColumn('trips', 'vehicle_run_id')) {
            Schema::table('trips', function (Blueprint $table) {
                $table->dropColumn('vehicle_run_id');
            });
        }
        */
    }
};
