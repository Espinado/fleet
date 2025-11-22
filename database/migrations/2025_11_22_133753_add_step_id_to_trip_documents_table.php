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
        Schema::table('trip_documents', function (Blueprint $table) {
             $table->foreignId('step_id')
                  ->nullable()
                  ->after('trip_id')
                  ->constrained('trip_steps')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trip_documents', function (Blueprint $table) {
             $table->dropForeign(['step_id']);
            $table->dropColumn('step_id');
        });
    }
};
