<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_cargos', function (Blueprint $table) {
            // если раньше были supplier_invoice_*
            if (Schema::hasColumn('trip_cargos', 'supplier_invoice_nr') && !Schema::hasColumn('trip_cargos', 'commercial_invoice_nr')) {
                $table->renameColumn('supplier_invoice_nr', 'commercial_invoice_nr');
            }

            if (Schema::hasColumn('trip_cargos', 'supplier_invoice_amount') && !Schema::hasColumn('trip_cargos', 'commercial_invoice_amount')) {
                $table->renameColumn('supplier_invoice_amount', 'commercial_invoice_amount');
            }

            // если вдруг ни старых ни новых нет — создадим новые
            if (!Schema::hasColumn('trip_cargos', 'commercial_invoice_nr')) {
                $table->string('commercial_invoice_nr', 64)->nullable()->after('payer_type_id');
            }

            if (!Schema::hasColumn('trip_cargos', 'commercial_invoice_amount')) {
                $table->decimal('commercial_invoice_amount', 12, 2)->nullable()->after('commercial_invoice_nr');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trip_cargos', function (Blueprint $table) {
            // откат обратно
            if (Schema::hasColumn('trip_cargos', 'commercial_invoice_nr') && !Schema::hasColumn('trip_cargos', 'supplier_invoice_nr')) {
                $table->renameColumn('commercial_invoice_nr', 'supplier_invoice_nr');
            }

            if (Schema::hasColumn('trip_cargos', 'commercial_invoice_amount') && !Schema::hasColumn('trip_cargos', 'supplier_invoice_amount')) {
                $table->renameColumn('commercial_invoice_amount', 'supplier_invoice_amount');
            }
        });
    }
};
