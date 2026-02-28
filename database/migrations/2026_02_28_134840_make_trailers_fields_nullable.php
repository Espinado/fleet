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
        //
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trailers', function (Blueprint $table) {
            $table->string('brand')->nullable()->change();
            $table->year('year')->nullable()->change();
            $table->string('vin')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('trailers', function (Blueprint $table) {
            $table->string('brand')->nullable(false)->change();
            $table->year('year')->nullable(false)->change();
            $table->string('vin')->nullable(false)->change();
        });
    }
};
