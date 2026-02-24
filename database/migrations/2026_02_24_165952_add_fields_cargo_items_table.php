<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('trip_cargo_items', function (Blueprint $table) {
            $table->string('customs_code', 32)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('trip_cargo_items', function (Blueprint $table) {
            $table->dropColumn('customs_code');
        });
    }
};
