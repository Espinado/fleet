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
        Schema::create('trucks', function (Blueprint $table) {
            $table->id();
              $table->string('brand');
            $table->string('model');
            $table->string('plate')->unique();
            $table->year('year');

            // Техосмотр
            $table->date('inspection_issued');
            $table->date('inspection_expired');

            // Страховка
            $table->string('insurance_number');
            $table->date('insurance_issued');
            $table->date('insurance_expired');
            $table->string('insurance_company');

            $table->string('vin')->unique();
             $table->integer('status')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trucks');
    }
};
