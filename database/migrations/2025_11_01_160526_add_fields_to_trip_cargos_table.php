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
             $table->integer('cargo_paletes')->nullable();
              $table->decimal('cargo_netto_weight', 10, 2)->nullable();
               $table->decimal('cargo_tonnes', 10, 2)->nullable();
                $table->integer('tax_percent')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trip_cargos', function (Blueprint $table) {
            $table->dropColumn(['cargo_paletes', 'cargo_netto_weight', 'cargo_tonnes', 'tax_percent']);
        });
    }
};
