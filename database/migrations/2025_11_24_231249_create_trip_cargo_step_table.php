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

            $table->unsignedBigInteger('trip_step_id');
            $table->unsignedBigInteger('trip_cargo_id');

            $table->foreign('trip_step_id')->references('id')->on('trip_steps')->onDelete('cascade');
            $table->foreign('trip_cargo_id')->references('id')->on('trip_cargos')->onDelete('cascade');

            $table->unique(['trip_step_id', 'trip_cargo_id']); // не допускаем дубликатов
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_cargo_step');
    }
};
