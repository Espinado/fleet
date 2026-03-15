<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_cargos', function (Blueprint $table) {
            $table->date('requested_date_from')->nullable()->after('quoted_price')->comment('Aptuvenā iekraušana / Datums no');
            $table->date('requested_date_to')->nullable()->after('requested_date_from')->comment('Aptuvenā izkraušana / Datums līdz');
        });
    }

    public function down(): void
    {
        Schema::table('order_cargos', function (Blueprint $table) {
            $table->dropColumn(['requested_date_from', 'requested_date_to']);
        });
    }
};
