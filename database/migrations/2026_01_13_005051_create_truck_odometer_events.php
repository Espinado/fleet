<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('truck_odometer_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('truck_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();

            // 1 = departure (потом добавим 2 = return)
            $table->unsignedTinyInteger('type')->default(1);

            // одометр в км (CAN может быть с десятыми — сохраняем decimal)
            $table->decimal('odometer_km', 10, 1)->nullable();

            // 1=can,2=gps,3=manual,4=fallback_local
            $table->unsignedTinyInteger('source')->default(1);

            // когда водитель нажал кнопку
            $table->dateTime('occurred_at');

            // когда было измерение/обновление в Mapon (can.odom.gmt)
            $table->dateTime('mapon_at')->nullable();

            // stale статус для UI/аналитики
            $table->boolean('is_stale')->default(false);
            $table->unsignedInteger('stale_minutes')->nullable();

            // для дебага (по желанию)
            $table->json('raw')->nullable();

            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['truck_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('truck_odometer_events');
    }
};
