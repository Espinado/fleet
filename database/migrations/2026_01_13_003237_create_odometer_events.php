<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('odometer_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('truck_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_run_id')->nullable()->constrained('vehicle_runs')->nullOnDelete();
            $table->foreignId('trip_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('trip_step_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedTinyInteger('event_type'); // enum int
            $table->dateTime('event_at');

            $table->decimal('can_odom_km', 12, 1)->nullable();
            $table->dateTime('can_at')->nullable();

            $table->string('source', 20)->default('can'); // can|gps|manual
            $table->boolean('is_stale')->default(false);

            $table->timestamps();

            $table->index(['truck_id', 'event_at']);
            $table->index(['vehicle_run_id', 'event_type']);
            $table->index(['trip_id', 'event_type']);
            $table->index(['trip_step_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odometer_events');
    }
};
