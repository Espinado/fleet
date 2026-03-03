<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Для тягача и прицепа третьей стороны нужны только номера (plate).
     * VIN и остальные поля делаем nullable.
     */
    public function up(): void
    {
        Schema::table('trucks', function (Blueprint $table) {
            $table->string('vin')->nullable()->change();
            $table->string('brand')->nullable()->change();
            $table->string('model')->nullable()->change();
            $table->year('year')->nullable()->change();
        });

        Schema::table('trailers', function (Blueprint $table) {
            $table->string('vin')->nullable()->change();
            $table->string('brand')->nullable()->change();
            $table->year('year')->nullable()->change();
            $table->unsignedBigInteger('type_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('trucks', function (Blueprint $table) {
            $table->string('vin')->nullable(false)->change();
            $table->string('brand')->nullable(false)->change();
            $table->string('model')->nullable(false)->change();
            $table->year('year')->nullable(false)->change();
        });

        Schema::table('trailers', function (Blueprint $table) {
            $table->string('vin')->nullable(false)->change();
            $table->string('brand')->nullable(false)->change();
            $table->year('year')->nullable(false)->change();
            $table->unsignedBigInteger('type_id')->nullable(false)->change();
        });
    }
};
