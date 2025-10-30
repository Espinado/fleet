<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_cargos', function (Blueprint $table) {
            $table->id();

            // === 1️⃣ Связь с рейсом ===
            $table->foreignId('trip_id')->constrained('trips')->cascadeOnDelete();

            // === 2️⃣ Отправитель и получатель ===
            $table->foreignId('shipper_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('consignee_id')->nullable()->constrained('clients')->nullOnDelete();

            // === 3️⃣ Место загрузки ===
            $table->unsignedSmallInteger('loading_country_id')->nullable();
            $table->unsignedSmallInteger('loading_city_id')->nullable();
            $table->string('loading_address')->nullable();
            $table->date('loading_date')->nullable();

            // === 4️⃣ Место разгрузки ===
            $table->unsignedSmallInteger('unloading_country_id')->nullable();
            $table->unsignedSmallInteger('unloading_city_id')->nullable();
            $table->string('unloading_address')->nullable();
            $table->date('unloading_date')->nullable();

            // === 5️⃣ Информация о грузе ===
            $table->string('cargo_description')->nullable();
            $table->integer('cargo_packages')->nullable();
            $table->decimal('cargo_weight', 10, 2)->nullable();
            $table->decimal('cargo_volume', 10, 2)->nullable();
            $table->string('cargo_marks')->nullable();
            $table->text('cargo_instructions')->nullable();
            $table->text('cargo_remarks')->nullable();

            // === 6️⃣ Оплата ===
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 10)->default('EUR');
            $table->date('payment_terms')->nullable();
            $table->unsignedTinyInteger('payer_type_id')->nullable(); // 1=Shipper, 2=Consignee, 3=Other

            // === 7️⃣ JSON-массив для дополнительных товаров (если несколько позиций в одном CMR) ===
            $table->json('items_json')->nullable();

            // === 8️⃣ Файлы CMR ===
            $table->string('cmr_file')->nullable();
            $table->timestamp('cmr_created_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_cargos');
    }
};
