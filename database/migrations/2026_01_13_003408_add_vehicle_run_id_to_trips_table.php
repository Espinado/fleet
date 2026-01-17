<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->foreignId('vehicle_run_id')
                ->nullable()
                ->after('truck_id') // подстрой под свою структуру
                ->constrained('vehicle_runs')
                ->nullOnDelete();

            $table->index(['vehicle_run_id']);
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vehicle_run_id');
        });
    }
};
