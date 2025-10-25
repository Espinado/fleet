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
        Schema::table('trips', function (Blueprint $table) {
            // Consignor (sender)
            $table->string('origin_company_name')->nullable();
            $table->string('origin_contact_person')->nullable();
            $table->string('origin_phone')->nullable();
            $table->string('origin_email')->nullable();

            // Consignee (receiver)
            $table->string('destination_company_name')->nullable();
            $table->string('destination_contact_person')->nullable();
            $table->string('destination_phone')->nullable();
            $table->string('destination_email')->nullable();

            // Cargo
            $table->string('cargo_description')->nullable();
            $table->integer('cargo_packages')->nullable();
            $table->decimal('cargo_weight', 8, 2)->nullable();
            $table->decimal('cargo_volume', 8, 2)->nullable();
            $table->string('cargo_marks')->nullable();
            $table->text('cargo_instructions')->nullable();
            $table->text('cargo_remarks')->nullable();

            // Payment
            $table->string('payment_terms')->nullable();
            $table->string('payer_type')->nullable(); // sender / receiver / third party
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn([
                'origin_company_name', 'origin_contact_person', 'origin_phone', 'origin_email',
                'destination_company_name', 'destination_contact_person', 'destination_phone', 'destination_email',
                'cargo_description', 'cargo_packages', 'cargo_weight', 'cargo_volume',
                'cargo_marks', 'cargo_instructions', 'cargo_remarks',
                'payment_terms', 'payer_type',
            ]);
        });
    }
};
