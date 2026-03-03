<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_cargos', function (Blueprint $table) {
            $table->boolean('has_delay')->default(false)->after('commercial_invoice_amount');
            $table->unsignedTinyInteger('delay_days')->nullable()->after('has_delay');
            $table->decimal('delay_amount', 10, 2)->nullable()->after('delay_days')->comment('Amount without VAT');
        });
    }

    public function down(): void
    {
        Schema::table('trip_cargos', function (Blueprint $table) {
            $table->dropColumn(['has_delay', 'delay_days', 'delay_amount']);
        });
    }
};
