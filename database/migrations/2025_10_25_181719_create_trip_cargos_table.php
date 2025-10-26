<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trip_cargos', function (Blueprint $table) {
            $table->id();

            // связь с рейсом
            $table->foreignId('trip_id')->constrained('trips')->onDelete('cascade');

            // отправитель / получатель
            $table->foreignId('shipper_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('consignee_id')->nullable()->constrained('clients')->nullOnDelete();

            // данные о грузе
            $table->string('cargo_description')->nullable();
            $table->integer('cargo_packages')->nullable();
            $table->decimal('cargo_weight', 10, 2)->nullable();
            $table->decimal('cargo_volume', 10, 2)->nullable();
            $table->string('cargo_marks')->nullable();
            $table->text('cargo_instructions')->nullable();
            $table->text('cargo_remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_cargos');
    }
};
