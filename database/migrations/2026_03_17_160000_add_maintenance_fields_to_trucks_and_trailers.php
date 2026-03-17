<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Поля для напоминаний по ТО (Fleet Maintenance). Все nullable — заполняются по мере появления данных.
     */
    public function up(): void
    {
        Schema::table('trucks', function (Blueprint $table) {
            $table->unsignedInteger('next_service_km')->nullable()->after('tech_passport_photo');
            $table->date('next_service_date')->nullable()->after('next_service_km');
            $table->unsignedInteger('service_interval_km')->nullable()->after('next_service_date');
            $table->unsignedTinyInteger('service_interval_months')->nullable()->after('service_interval_km');
        });

        Schema::table('trailers', function (Blueprint $table) {
            $table->unsignedInteger('next_service_km')->nullable()->after('tech_passport_photo');
            $table->date('next_service_date')->nullable()->after('next_service_km');
            $table->unsignedInteger('service_interval_km')->nullable()->after('next_service_date');
            $table->unsignedTinyInteger('service_interval_months')->nullable()->after('service_interval_km');
        });
    }

    public function down(): void
    {
        Schema::table('trucks', function (Blueprint $table) {
            $table->dropColumn(['next_service_km', 'next_service_date', 'service_interval_km', 'service_interval_months']);
        });
        Schema::table('trailers', function (Blueprint $table) {
            $table->dropColumn(['next_service_km', 'next_service_date', 'service_interval_km', 'service_interval_months']);
        });
    }
};
