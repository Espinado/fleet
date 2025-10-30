<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            // === Основная информация ===
            $table->string('company_name');
            $table->string('reg_nr')->nullable();
            $table->string('representative')->nullable();

            // === Юридический адрес ===
            $table->unsignedInteger('jur_country_id')->nullable();
            $table->unsignedInteger('jur_city_id')->nullable();
            $table->string('jur_address')->nullable();
            $table->string('jur_post_code')->nullable();

            // === Фактический адрес ===
            $table->unsignedInteger('fiz_country_id')->nullable();
            $table->unsignedInteger('fiz_city_id')->nullable();
            $table->string('fiz_address')->nullable();
            $table->string('fiz_post_code')->nullable();

            // === Банковская информация ===
            $table->string('bank_name')->nullable();
            $table->string('swift')->nullable();

            // === Контакты ===
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            // === Служебные поля ===
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
