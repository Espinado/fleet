<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_cargos', function (Blueprint $table) {
            $table->unsignedTinyInteger('payment_days')->nullable()->after('payment_terms')
                ->comment('Срок оплаты инвойса в днях: 7, 14, 21, 30 с момента генерации');
        });
    }

    public function down(): void
    {
        Schema::table('trip_cargos', function (Blueprint $table) {
            $table->dropColumn('payment_days');
        });
    }
};
