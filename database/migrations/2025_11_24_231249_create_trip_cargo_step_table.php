<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_cargo_step', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_step_id')->constrained('trip_steps')->cascadeOnDelete();
            $table->foreignId('trip_cargo_id')->constrained('trip_cargos')->cascadeOnDelete();
            $table->unique(['trip_step_id', 'trip_cargo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_cargo_step');
    }
};
