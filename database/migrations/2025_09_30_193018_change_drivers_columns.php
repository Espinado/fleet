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
            // сначала убираем unique индексы
            $table->dropUnique('drivers_95code_issued_unique');
            $table->dropUnique('drivers_95code_end_unique');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            // возвращаем назад
            $table->renameColumn('code95_issued', '95code_issued');
            $table->renameColumn('code95_end', '95code_end');


        });
    }
    };

