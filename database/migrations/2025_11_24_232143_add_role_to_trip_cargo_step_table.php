<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_cargo_step', function (Blueprint $table) {
            // Добавляем только если ещё нет
            if (!Schema::hasColumn('trip_cargo_step', 'role')) {
                $table->string('role', 20)
                    ->nullable()
                    ->after('trip_step_id')
                    ->comment('loading|unloading');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trip_cargo_step', function (Blueprint $table) {
            if (Schema::hasColumn('trip_cargo_step', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
