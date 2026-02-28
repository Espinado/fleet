<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // если вдруг колонки нет (на чистой базе) — добавим
        Schema::table('trips', function (Blueprint $table) {
            if (!Schema::hasColumn('trips', 'carrier_company_id')) {
                $table->unsignedBigInteger('carrier_company_id')->nullable()->after('id');
                $table->index('carrier_company_id');
            }
        });

        // проверяем, есть ли уже FK (по имени)
        $exists = DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'trips'
              AND COLUMN_NAME = 'carrier_company_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ");

        if (!$exists) {
            Schema::table('trips', function (Blueprint $table) {
                $table->foreign('carrier_company_id', 'trips_carrier_company_id_fk')
                    ->references('id')
                    ->on('companies')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            // снимаем FK по нашему имени
            try { $table->dropForeign('trips_carrier_company_id_fk'); } catch (\Throwable $e) {}
        });
    }
};
