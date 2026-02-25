<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();

            $table->date('paid_at');
            $table->decimal('amount', 12, 2);

            $table->string('currency', 10)->default('EUR');
            $table->string('method', 32)->nullable();      // bank / cash / other
            $table->string('reference', 191)->nullable();  // payment reference
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['invoice_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};
