<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            if (!Schema::hasColumn('trips', 'carrier_company_id')) {
                $table->unsignedBigInteger('carrier_company_id')->nullable()->after('id');
                $table->foreign('carrier_company_id', 'trips_carrier_company_id_foreign')
                    ->references('id')
                    ->on('companies')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            if (Schema::hasColumn('trips', 'carrier_company_id')) {
                $table->dropForeign('trips_carrier_company_id_foreign');
                $table->dropColumn('carrier_company_id');
            }
        });
    }
};
