<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();

            // === 1️⃣ Expeditor company (снимок из config/companies.php) ===
            $table->unsignedBigInteger('expeditor_id');
            $table->string('expeditor_name');
            $table->string('expeditor_reg_nr')->nullable();
            $table->string('expeditor_country')->nullable();
            $table->string('expeditor_city')->nullable();
            $table->string('expeditor_address')->nullable();
            $table->string('expeditor_post_code')->nullable();
            $table->string('expeditor_email')->nullable();
            $table->string('expeditor_phone')->nullable();

            // === 2️⃣ Связи с транспортом ===
            $table->foreignId('driver_id')->constrained('drivers')->cascadeOnDelete();
            $table->foreignId('truck_id')->constrained('trucks')->cascadeOnDelete();
            $table->foreignId('trailer_id')->nullable()->constrained('trailers')->nullOnDelete();

            // === 3️⃣ Отправитель / Получатель ===
            $table->foreignId('shipper_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('consignee_id')->nullable()->constrained('clients')->nullOnDelete();

            // === 4️⃣ Маршрут ===
            $table->unsignedSmallInteger('origin_country_id')->nullable();
            $table->unsignedSmallInteger('origin_city_id')->nullable();
            $table->string('origin_address')->nullable();

            $table->unsignedSmallInteger('destination_country_id')->nullable();
            $table->unsignedSmallInteger('destination_city_id')->nullable();
            $table->string('destination_address')->nullable();

            // === 5️⃣ Даты рейса ===
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // === 6️⃣ Информация о грузе (основные данные для быстрого просмотра) ===
            $table->string('cargo_description')->nullable();
            $table->integer('cargo_packages')->nullable();
            $table->decimal('cargo_weight', 10, 2)->nullable();
            $table->decimal('cargo_volume', 10, 2)->nullable();
            $table->string('cargo_marks')->nullable();
            $table->text('cargo_instructions')->nullable();
            $table->text('cargo_remarks')->nullable();

            // === 7️⃣ Оплата ===
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 10)->default('EUR');
            $table->date('payment_terms')->nullable();
            $table->unsignedTinyInteger('payer_type_id')->nullable(); // 1=Shipper, 2=Consignee, 3=Other

            // === 8️⃣ Статус и доп. поля ===
            $table->string('status', 20)->default('planned'); // planned / in_progress / done
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
