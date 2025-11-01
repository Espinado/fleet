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
              if (!Schema::hasColumn('trip_cargos', 'price_with_tax')) {
                $table->decimal('price_with_tax', 10, 2)->default(0)->after('total_tax_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trip_cargos', function (Blueprint $table) {
               $table->dropColumn('price_with_tax');
        });
    }
};
