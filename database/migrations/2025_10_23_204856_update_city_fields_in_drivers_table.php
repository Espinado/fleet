<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            // Удаляем старые поля, только если они существуют
            if (Schema::hasColumn('drivers', 'declared_country')) {
                $table->dropColumn('declared_country');
            }
            if (Schema::hasColumn('drivers', 'declared_city')) {
                $table->dropColumn('declared_city');
            }
            if (Schema::hasColumn('drivers', 'actual_country')) {
                $table->dropColumn('actual_country');
            }
            if (Schema::hasColumn('drivers', 'actual_city')) {
                $table->dropColumn('actual_city');
            }

            // Добавляем новые поля, если их ещё нет
            if (!Schema::hasColumn('drivers', 'declared_country_id')) {
                $table->unsignedSmallInteger('declared_country_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('drivers', 'declared_city_id')) {
                $table->unsignedSmallInteger('declared_city_id')->nullable()->after('declared_country_id');
            }
            if (!Schema::hasColumn('drivers', 'actual_country_id')) {
                $table->unsignedSmallInteger('actual_country_id')->nullable()->after('declared_city_id');
            }
            if (!Schema::hasColumn('drivers', 'actual_city_id')) {
                $table->unsignedSmallInteger('actual_city_id')->nullable()->after('actual_country_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            // При откате возвращаем старые поля
            if (!Schema::hasColumn('drivers', 'declared_country')) {
                $table->string('declared_country')->nullable();
            }
            if (!Schema::hasColumn('drivers', 'declared_city')) {
                $table->string('declared_city')->nullable();
            }
            if (!Schema::hasColumn('drivers', 'actual_country')) {
                $table->string('actual_country')->nullable();
            }
            if (!Schema::hasColumn('drivers', 'actual_city')) {
                $table->string('actual_city')->nullable();
            }

            // Удаляем новые, если есть
            foreach ([
                'declared_country_id',
                'declared_city_id',
                'actual_country_id',
                'actual_city_id'
            ] as $col) {
                if (Schema::hasColumn('drivers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
