<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_steps', function (Blueprint $table) {
            $table->string('contact_phone_1', 50)->nullable()->after('address');
            $table->string('contact_phone_2', 50)->nullable()->after('contact_phone_1');
        });
    }

    public function down(): void
    {
        Schema::table('trip_steps', function (Blueprint $table) {
            $table->dropColumn(['contact_phone_1', 'contact_phone_2']);
        });
    }
};
