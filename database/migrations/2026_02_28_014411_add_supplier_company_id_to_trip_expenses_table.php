<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('trip_expenses', 'supplier_company_id')) {
                $table->unsignedBigInteger('supplier_company_id')->nullable()->after('trip_id');
                $table->index('supplier_company_id');

                $table->foreign('supplier_company_id')
                    ->references('id')
                    ->on('companies')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('trip_expenses', function (Blueprint $table) {
            if (Schema::hasColumn('trip_expenses', 'supplier_company_id')) {
                $table->dropForeign(['supplier_company_id']);
                $table->dropIndex(['supplier_company_id']);
                $table->dropColumn('supplier_company_id');
            }
        });
    }
};
