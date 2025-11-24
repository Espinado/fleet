<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('trip_cargo_items', function (Blueprint $table) {

            // === Добавляем новые EU-поля ===
            if (!Schema::hasColumn('trip_cargo_items', 'pallets')) {
                $table->integer('pallets')->nullable()->after('packages');
            }
            if (!Schema::hasColumn('trip_cargo_items', 'units')) {
                $table->integer('units')->nullable()->after('pallets');
            }
            if (!Schema::hasColumn('trip_cargo_items', 'gross_weight')) {
                $table->decimal('gross_weight', 10, 2)->nullable()->after('packages');
            }
            if (!Schema::hasColumn('trip_cargo_items', 'net_weight')) {
                $table->decimal('net_weight', 10, 2)->nullable()->after('gross_weight');
            }
            if (!Schema::hasColumn('trip_cargo_items', 'tonnes')) {
                $table->decimal('tonnes', 10, 3)->nullable()->after('net_weight');
            }
            if (!Schema::hasColumn('trip_cargo_items', 'loading_meters')) {
                $table->decimal('loading_meters', 10, 2)->nullable()->after('volume');
            }
            if (!Schema::hasColumn('trip_cargo_items', 'hazmat')) {
                $table->string('hazmat')->nullable()->after('loading_meters');
            }
            if (!Schema::hasColumn('trip_cargo_items', 'temperature')) {
                $table->string('temperature')->nullable()->after('hazmat');
            }
            if (!Schema::hasColumn('trip_cargo_items', 'stackable')) {
                $table->boolean('stackable')->default(0)->after('temperature');
            }

            // === Если вдруг старые поля еще есть — удаляем ===
            foreach (['cargo_paletes', 'cargo_tonnes', 'weight', 'cargo_netto_weight'] as $col) {
                if (Schema::hasColumn('trip_cargo_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('trip_cargo_items', function (Blueprint $table) {

            foreach ([
                'pallets', 'units', 'gross_weight', 'net_weight',
                'tonnes', 'loading_meters', 'hazmat', 'temperature', 'stackable'
            ] as $col) {
                if (Schema::hasColumn('trip_cargo_items', $col)) {
                    $table->dropColumn($col);
                }
            }

            // Вернем старые только как заглушки
            $table->decimal('cargo_paletes', 10, 2)->nullable();
            $table->decimal('cargo_tonnes', 10, 2)->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('cargo_netto_weight', 10, 2)->nullable();
        });
    }
};
