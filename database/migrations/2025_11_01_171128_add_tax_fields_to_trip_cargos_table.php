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
            if (!Schema::hasColumn('trip_cargos', 'total_tax_amount')) {
                $table->decimal('total_tax_amount', 10, 2)
                      ->default(0)
                      ->after('price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trip_cargos', function (Blueprint $table) {
             $table->dropColumn(['total_tax_amount', 'price_with_tax']);
        });
    }
};
