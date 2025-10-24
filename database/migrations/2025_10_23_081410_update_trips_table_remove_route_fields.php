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
             $table->dropColumn(['route_from', 'route_to']);

            // Добавляем новые
            $table->unsignedTinyInteger('origin_country')->nullable()->after('client_id');
            $table->string('origin_address')->nullable()->after('origin_country');
            $table->unsignedTinyInteger('destination_country')->nullable()->after('origin_address');
            $table->string('destination_address')->nullable()->after('destination_country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
             $table->string('route_from')->nullable();
            $table->string('route_to')->nullable();

            $table->dropColumn([
                'origin_country',
                'origin_address',
                'destination_country',
                'destination_address'
            ]);
        });
    }
};
