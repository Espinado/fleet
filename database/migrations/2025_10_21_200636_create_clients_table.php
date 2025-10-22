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
        Schema::create('clients', function (Blueprint $table) {
    $table->id();
     $table->string('company_name');
    $table->string('reg_nr')->nullable();
    $table->string('jur_country')->nullable();
    $table->string('jur_city')->nullable();
    $table->string('jur_address')->nullable();
    $table->string('jur_post_code')->nullable();
    $table->string('fiz_country')->nullable();
    $table->string('fiz_city')->nullable();
    $table->string('fiz_address')->nullable();
    $table->string('fiz_post_code')->nullable();
    $table->string('bank_name')->nullable();
    $table->string('swift')->nullable();
    $table->string('email')->nullable();
    $table->string('phone')->nullable();
    $table->string('representative')->nullable();
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
