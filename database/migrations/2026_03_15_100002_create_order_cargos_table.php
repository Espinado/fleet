<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_cargos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_order_id')->constrained('transport_orders')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('shipper_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('consignee_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->decimal('weight_kg', 12, 2)->nullable();
            $table->decimal('volume_m3', 12, 3)->nullable();
            $table->unsignedInteger('pallets')->nullable();
            $table->text('description')->nullable();
            $table->decimal('quoted_price', 12, 2)->nullable();
            $table->timestamps();

            $table->index('transport_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_cargos');
    }
};
