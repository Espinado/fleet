<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
 public function up(): void
{
    // 1) колонка
    Schema::table('trips', function (Blueprint $table) {
        if (!Schema::hasColumn('trips', 'vehicle_run_id')) {
            $table->unsignedBigInteger('vehicle_run_id')
                ->nullable()
                ->after('truck_id');
        }
    });

    // 2) индекс (проверяем, есть ли уже) — совместимо с MySQL и SQLite (тесты)
    $driver = Schema::getConnection()->getDriverName();
    if ($driver === 'sqlite') {
        $indexes = DB::select("PRAGMA index_list('trips')");
        $hasIndex = collect($indexes)->pluck('name')->contains('trips_vehicle_run_id_index');
    } else {
        $db = DB::getDatabaseName();
        $hasIndex = DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', $db)
            ->where('TABLE_NAME', 'trips')
            ->where('INDEX_NAME', 'trips_vehicle_run_id_index')
            ->exists();
    }

    if (!$hasIndex) {
        Schema::table('trips', function (Blueprint $table) {
            $table->index('vehicle_run_id', 'trips_vehicle_run_id_index');
        });
    }

    // ❌ FK не добавляем (как договорились)
}


    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // FK (только MySQL)
        if ($driver !== 'sqlite') {
            $db = DB::getDatabaseName();
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
        }

        // index
        $hasIndex = false;
        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('trips')");
            $hasIndex = collect($indexes)->pluck('name')->contains('trips_vehicle_run_id_index');
        } else {
            $db = DB::getDatabaseName();
            $hasIndex = DB::table('information_schema.STATISTICS')
                ->where('TABLE_SCHEMA', $db)
                ->where('TABLE_NAME', 'trips')
                ->where('INDEX_NAME', 'trips_vehicle_run_id_index')
                ->exists();
        }
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
