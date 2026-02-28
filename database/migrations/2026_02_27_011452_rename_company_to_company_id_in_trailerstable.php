<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('trip_cargos', function (Blueprint $table) {
            $table->renameColumn('supplier_invoice_nr', 'commercial_invoice_nr');
            $table->renameColumn('supplier_invoice_amount', 'commercial_invoice_amount');
        });
    }

    public function down(): void
    {
        Schema::table('trip_cargos', function (Blueprint $table) {
            $table->renameColumn('commercial_invoice_nr', 'supplier_invoice_nr');
            $table->renameColumn('commercial_invoice_amount', 'supplier_invoice_amount');
        });
    }
};
