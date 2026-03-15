<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 32)->unique()->comment('TO-YYYY-NNNNN');
            $table->date('order_date');
            $table->foreignId('expeditor_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->date('requested_date_from')->nullable();
            $table->date('requested_date_to')->nullable();
            $table->decimal('quoted_price', 12, 2)->nullable();
            $table->string('currency', 10)->default('EUR');
            $table->string('status', 20)->default('draft');
            $table->foreignId('trip_id')->nullable()->constrained('trips')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('order_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_orders');
    }
};
