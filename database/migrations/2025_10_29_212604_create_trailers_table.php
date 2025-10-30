<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Support\MigrationHelper as MH;

return new class extends Migration
{
    public function up(): void
    {
        if (!MH::hasTable('trailers')) {
            Schema::create('trailers', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company')->nullable();

                $table->string('brand');
                $table->string('plate')->unique();
                $table->year('year');

                // Документы
                $table->string('vin')->unique();
                $table->string('tech_passport_nr')->nullable();
                $table->date('tech_passport_issued')->nullable();
                $table->date('tech_passport_expired')->nullable();
                $table->string('tech_passport_photo')->nullable();

                $table->date('inspection_issued')->nullable();
                $table->date('inspection_expired')->nullable();

                $table->string('insurance_number')->nullable();
                $table->string('insurance_company')->nullable();
                $table->date('insurance_issued')->nullable();
                $table->date('insurance_expired')->nullable();

                $table->date('tir_issued')->nullable();
                $table->date('tir_expired')->nullable();

                $table->integer('status')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (MH::hasTable('trailers')) {
            Schema::dropIfExists('trailers');
        }
    }
};
