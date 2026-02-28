<?php

// database/migrations/xxxx_create_driver_events_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('driver_events', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('driver_id')->nullable();   // может быть null до логина
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('trip_id')->nullable();

            $table->string('channel');  // http | livewire | client | auth
            $table->string('event');    // request | method_call | input_update | click | login | logout...
            $table->string('name')->nullable(); // route name / component / method / element
            $table->string('path')->nullable();
            $table->string('method', 10)->nullable();

            $table->unsignedSmallInteger('status_code')->nullable();
            $table->integer('duration_ms')->nullable();

            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['driver_id', 'created_at']);
            $table->index(['trip_id', 'created_at']);
            $table->index(['channel', 'event', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_events');
    }
};
