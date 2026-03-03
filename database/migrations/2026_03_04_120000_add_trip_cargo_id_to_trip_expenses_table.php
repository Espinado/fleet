<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('trip_expenses', 'trip_cargo_id')) {
                $table->foreignId('trip_cargo_id')->nullable()->after('trip_id')
                    ->constrained('trip_cargos')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('trip_expenses', function (Blueprint $table) {
            if (Schema::hasColumn('trip_expenses', 'trip_cargo_id')) {
                $table->dropForeign(['trip_cargo_id']);
            }
        });
    }
};
