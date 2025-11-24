<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // --- удаляем все возможные FK, связанные с trip_cargo_id ---
        $fks = [
            'ts_cargo_fk',
            'trip_steps_trip_cargo_id_foreign',
        ];

        foreach ($fks as $fk) {
            try {
                DB::statement("ALTER TABLE trip_steps DROP FOREIGN KEY $fk");
            } catch (\Throwable $e) {
                // пропускаем, FK может не существовать
            }
        }

        Schema::table('trip_steps', function (Blueprint $table) {
            if (Schema::hasColumn('trip_steps', 'trip_cargo_id')) {
                $table->dropColumn('trip_cargo_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trip_steps', function (Blueprint $table) {
            $table->unsignedBigInteger('trip_cargo_id')->nullable();

            $table->foreign('trip_cargo_id', 'ts_cargo_fk')
                ->references('id')->on('trip_cargos')->nullOnDelete();
        });
    }
};
