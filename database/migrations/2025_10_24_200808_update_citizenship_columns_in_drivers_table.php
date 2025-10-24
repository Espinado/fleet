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
        Schema::table('drivers', function (Blueprint $table) {
             if (Schema::hasColumn('drivers', 'citizenship_id')) {
                $table->dropColumn('citizenship_id');
            }
            $table->unsignedSmallInteger('citizenship')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
             $table->string('citizenship', 5)->nullable()->change();

            // Восстанавливаем колонку citizenship_id
            $table->unsignedSmallInteger('citizenship_id')->nullable();
        });
    }
};
