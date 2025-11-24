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

            $table->foreignId('trip_cargo_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('trip_step_id')
                ->constrained()
                ->cascadeOnDelete();

            // loading / unloading / maybe later: transit, extra_stop
            $table->string('role', 20)->nullable();

            $table->timestamps();

            $table->unique(['trip_cargo_id', 'trip_step_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_cargo_step');
    }
};
