<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicle_runs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('truck_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();

            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();

            $table->decimal('start_can_odom_km', 12, 1)->nullable();
            $table->decimal('end_can_odom_km', 12, 1)->nullable();

            $table->decimal('start_engine_hours', 12, 1)->nullable();
            $table->decimal('end_engine_hours', 12, 1)->nullable();

            $table->string('status', 20)->default('open');        // open|closed
            $table->string('close_reason', 20)->nullable();       // manual|auto|system
            $table->string('created_by', 20)->default('manual');  // manual|system

            $table->timestamps();

            $table->index(['truck_id', 'status']);
            $table->index(['driver_id', 'status']);
            $table->index(['started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_runs');
    }
};
