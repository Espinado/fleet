<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            $table->string('slug')->unique(); // lakna/padex/expeditor
            $table->string('name');

            $table->string('type')->default('carrier')->index(); // carrier|forwarder|mixed

            $table->string('reg_nr')->nullable()->index();
            $table->string('vat_nr')->nullable()->index();

            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('post_code')->nullable();

            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->json('banks_json')->nullable(); // банки из config (пока так)

            $table->boolean('is_system')->default(false)->index(); // “наши” компании из конфига
            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
