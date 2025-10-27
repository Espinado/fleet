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
             if (!Schema::hasColumn('trip_cargos', 'cmr_created_at')) {
                $table->timestamp('cmr_created_at')->nullable()->after('cmr_file');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trip_cargos', function (Blueprint $table) {
             if (Schema::hasColumn('trip_cargos', 'cmr_created_at')) {
                $table->dropColumn('cmr_created_at');
            }
        });
    }
};
