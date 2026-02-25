<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // Relations (1 cargo = 1 invoice)
            $table->foreignId('trip_id')->constrained('trips')->cascadeOnDelete();
            $table->foreignId('trip_cargo_id')->constrained('trip_cargos')->cascadeOnDelete();
            $table->unique('trip_cargo_id');

            // Number & dates
            $table->string('invoice_no', 191)->index();
            $table->timestamp('issued_at')->nullable();
            $table->date('due_date')->nullable();

            // Payer (optional for now)
            $table->unsignedTinyInteger('payer_type_id')->nullable();
            $table->foreignId('payer_client_id')->nullable()->constrained('clients')->nullOnDelete();

            // Money snapshot
            $table->string('currency', 10)->default('EUR');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_percent', 8, 2)->nullable();
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            // PDF file path
            $table->string('pdf_file', 191)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
