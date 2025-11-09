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
              $table->unsignedTinyInteger('expeditor_bank_id')->nullable()->after('expeditor_phone');
            $table->string('expeditor_bank')->nullable()->after('expeditor_bank_id');
            $table->string('expeditor_iban', 34)->nullable()->after('expeditor_bank');
            $table->string('expeditor_bic', 20)->nullable()->after('expeditor_iban');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
             $table->dropColumn([
                'expeditor_bank_id',
                'expeditor_bank',
                'expeditor_iban',
                'expeditor_bic',
            ]);
        });
    }
};
