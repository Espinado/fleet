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
        Schema::create('drivers', function (Blueprint $table) {
     $table->id();
    $table->string('first_name');
    $table->string('last_name');
    $table->string('personal_code')->unique();
    $table->string('citizenship')->nullable();
    $table->string('declared_country')->nullable();
    $table->string('declared_city')->nullable();
    $table->string('declared_street')->nullable();
    $table->string('declared_building')->nullable();
    $table->string('declared_room')->nullable();
    $table->string('declared_postcode')->nullable();
   $table->string( 'actual_country')->nullable();
    $table->string('actual_city')->nullable();
    $table->string('actual_street')->nullable();
    $table->string('actual_building')->nullable();
    $table->string('actual_room')->nullable();
    $table->string('phone')->nullable();
    $table->string('email')->nullable();
    $table->string('license_number')->unique(); // номер прав
    $table->date('license_issued'); // срок действия прав
    $table->date('license_end'); // срок действия прав
    $table->string('95code_issued')->unique(); // OVP passed
    $table->string('95code_end')->unique(); // OVP passed
   $table->date('permit_issued')->nullable();
    $table->date('permit_expired')->nullable();
    $table->date('medical_issued'); // Медсправка
    $table->date('medical_expired');
    $table->date('declaration_issued'); // Дорожная декларация
    $table->date('declaration_expired');
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
        Schema::dropIfExists('drivers');
    }
};
