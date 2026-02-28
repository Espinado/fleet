<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            if (!Schema::hasColumn('trips', 'carrier_company_id')) {
                $table->foreignId('carrier_company_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('companies')
                    ->nullOnDelete()
                    ->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            if (Schema::hasColumn('trips', 'carrier_company_id')) {
                $table->dropConstrainedForeignId('carrier_company_id');
            }
        });
    }
};
