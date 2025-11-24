<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Проверку нужно делать ЗДЕСЬ — ВНЕ Schema::table
        if (!Schema::hasColumn('trip_cargo_step', 'role')) {
            Schema::table('trip_cargo_step', function (Blueprint $table) {
                $table->string('role', 20)
                    ->nullable()
                    ->after('trip_step_id')
                    ->comment('loading|unloading');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('trip_cargo_step', 'role')) {
            Schema::table('trip_cargo_step', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};
