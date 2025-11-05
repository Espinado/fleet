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
        Schema::table('trip_cargos', function (Blueprint $table) {
            $table->string('inv_nr')->nullable()->after('cmr_created_at');        // номер инвойса
            $table->string('inv_file')->nullable()->after('inv_nr');              // путь к файлу
            $table->timestamp('inv_created_at')->nullable()->after('inv_file');   // дата создания
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trip_cargos', function (Blueprint $table) {
              $table->dropColumn(['inv_nr', 'inv_file', 'inv_created_at']);
        });
    }
};
