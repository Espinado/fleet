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
            $table->renameColumn('95code_issued', 'code95_issued');
            $table->renameColumn('95code_end', 'code95_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->renameColumn('code95_issued', '95code_issued');
            $table->renameColumn('code95_end', '95code_end');
        });
    }
};
