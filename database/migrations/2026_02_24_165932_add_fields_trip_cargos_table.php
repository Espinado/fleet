<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('trip_cargos', function (Blueprint $table) {
            $table->string('supplier_invoice_nr', 64)->nullable()->after('payer_type_id');
            $table->decimal('supplier_invoice_amount', 12, 2)->nullable()->after('supplier_invoice_nr');
        });
    }

    public function down(): void
    {
        Schema::table('trip_cargos', function (Blueprint $table) {
            $table->dropColumn(['supplier_invoice_nr', 'supplier_invoice_amount']);
        });
    }
};
