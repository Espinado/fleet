<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_steps', function (Blueprint $table) {
            /**
             * Timeline for TripStepStatus (ON_THE_WAY / ARRIVED / PROCESSING)
             * У тебя уже есть started_at и completed_at — мы их не трогаем.
             * started_at можно трактовать как "начал шаг" (часто это PROCESSING),
             * а новые поля дают точные точки для статусов.
             */
            $table->dateTime('on_the_way_at')->nullable()->after('started_at');
            $table->dateTime('arrived_at')->nullable()->after('on_the_way_at');
            $table->dateTime('processing_at')->nullable()->after('arrived_at');

            /**
             * Odometer snapshots for step points
             * (decimal 10,1 как в trip_expenses.odometer_km / truck_odometer_events.odometer_km)
             */
            $table->decimal('odo_on_the_way_km', 10, 1)->nullable()->after('processing_at');
            $table->decimal('odo_arrived_km', 10, 1)->nullable()->after('odo_on_the_way_km');
            $table->decimal('odo_completed_km', 10, 1)->nullable()->after('odo_arrived_km');

            /**
             * Sources for odometer snapshots
             * (используем те же коды, что в TruckOdometerEvent::SOURCE_*)
             */
            $table->unsignedTinyInteger('odo_on_the_way_source')->nullable()->after('odo_completed_km');
            $table->unsignedTinyInteger('odo_arrived_source')->nullable()->after('odo_on_the_way_source');
            $table->unsignedTinyInteger('odo_completed_source')->nullable()->after('odo_arrived_source');

            /**
             * Индексы под расчёт статистики
             */
            $table->index(['trip_id', 'order'], 'trip_steps_trip_order_idx');
            $table->index(['trip_id', 'type', 'order'], 'trip_steps_trip_type_order_idx');
        });
    }

    public function down(): void
    {
        Schema::table('trip_steps', function (Blueprint $table) {
            $table->dropIndex('trip_steps_trip_order_idx');
            $table->dropIndex('trip_steps_trip_type_order_idx');

            $table->dropColumn([
                'on_the_way_at',
                'arrived_at',
                'processing_at',
                'odo_on_the_way_km',
                'odo_arrived_km',
                'odo_completed_km',
                'odo_on_the_way_source',
                'odo_arrived_source',
                'odo_completed_source',
            ]);
        });
    }
};
