<?php

Schema::create('trip_cargo_step', function (Blueprint $table) {
    $table->id();

    $table->foreignId('trip_cargo_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->foreignId('trip_step_id')
        ->constrained()
        ->cascadeOnDelete();

    // loading / unloading / maybe later: transit, extra_stop и т.д.
    $table->string('role', 20);

    $table->timestamps();

    $table->unique(['trip_cargo_id', 'trip_step_id']);
});
