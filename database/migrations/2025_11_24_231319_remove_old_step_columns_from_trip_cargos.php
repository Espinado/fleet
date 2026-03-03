<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite: нельзя просто DROP COLUMN при наличии FK (нет DROP FOREIGN KEY).
        // Для тестов оставляем колонки; приложение использует pivot trip_cargo_step.
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        // --- безопасное удаление всех возможных FK (MySQL) ---
        $fks = [
            'tc_load_fk',
            'tc_unload_fk',
            'trip_cargos_loading_step_id_foreign',
            'trip_cargos_unloading_step_id_foreign',
        ];

        foreach ($fks as $fk) {
            try {
                DB::statement("ALTER TABLE trip_cargos DROP FOREIGN KEY $fk");
            } catch (\Throwable $e) {
                // игнорируем — ключа может не существовать
            }
        }

        Schema::table('trip_cargos', function (Blueprint $table) {
            if (Schema::hasColumn('trip_cargos', 'loading_step_id')) {
                $table->dropColumn('loading_step_id');
            }

            if (Schema::hasColumn('trip_cargos', 'unloading_step_id')) {
                $table->dropColumn('unloading_step_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trip_cargos', function (Blueprint $table) {
            $table->unsignedBigInteger('loading_step_id')->nullable();
            $table->unsignedBigInteger('unloading_step_id')->nullable();

            // возвращаем FK
            $table->foreign('loading_step_id', 'tc_load_fk')
                ->references('id')->on('trip_steps')->nullOnDelete();

            $table->foreign('unloading_step_id', 'tc_unload_fk')
                ->references('id')->on('trip_steps')->nullOnDelete();
        });
    }
};
