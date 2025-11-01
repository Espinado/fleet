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
        Schema::create('trip_cargo_items', function (Blueprint $table) {
           $table->id();
            $table->foreignId('trip_cargo_id')->constrained()->cascadeOnDelete();

            $table->string('description')->nullable();
            $table->unsignedInteger('packages')->default(0);
            $table->decimal('cargo_paletes', 10, 2)->default(0);
            $table->decimal('cargo_tonnes', 10, 2)->default(0);
            $table->decimal('weight', 10, 2)->default(0);
            $table->decimal('cargo_netto_weight', 10, 2)->default(0);
            $table->decimal('volume', 10, 2)->default(0);

            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('price_with_tax', 12, 2)->default(0);

            $table->text('instructions')->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_cargo_items');
    }
};
