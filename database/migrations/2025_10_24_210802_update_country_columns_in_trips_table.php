<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
               if (Schema::hasColumn('trips', 'origin_country')) {
                $table->dropColumn('origin_country');
            }
            if (Schema::hasColumn('trips', 'destination_country')) {
                $table->dropColumn('destination_country');
            }
              $table->unsignedSmallInteger('origin_country_id')->nullable()->after('client_id');
            $table->unsignedSmallInteger('origin_city_id')->nullable()->after('origin_country_id');
            $table->string('origin_address', 255)->nullable()->change();

            $table->unsignedSmallInteger('destination_country_id')->nullable()->after('origin_address');
            $table->unsignedSmallInteger('destination_city_id')->nullable()->after('destination_country_id');
            $table->string('destination_address', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            // Откатываем до старых текстовых полей
            $table->dropColumn(['origin_country_id', 'origin_city_id', 'destination_country_id', 'destination_city_id']);
            $table->string('origin_country')->nullable()->after('client_id');
            $table->string('destination_country')->nullable()->after('origin_country');
        });
    }
};
