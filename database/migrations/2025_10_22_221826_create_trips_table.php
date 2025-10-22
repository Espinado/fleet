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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
             // Снимок экспедитора (из config/companies.php)
            $table->unsignedBigInteger('expeditor_id'); // ссылка на id из конфига
            $table->string('expeditor_name');
            $table->string('expeditor_reg_nr')->nullable();
            $table->string('expeditor_country')->nullable();
            $table->string('expeditor_city')->nullable();
            $table->string('expeditor_address')->nullable();
            $table->string('expeditor_post_code')->nullable();
            $table->string('expeditor_email')->nullable();
            $table->string('expeditor_phone')->nullable();

            // Связи
            $table->unsignedBigInteger('driver_id');
            $table->unsignedBigInteger('truck_id');
            $table->unsignedBigInteger('trailer_id')->nullable();
            $table->unsignedBigInteger('client_id');

            // Данные рейса
            $table->string('route_from')->nullable();
            $table->string('route_to')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('cargo')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 10)->default('EUR');
            $table->string('status', 20)->default('planned'); // enum stored as string
            $table->timestamps();
            
             $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->foreign('truck_id')->references('id')->on('trucks')->onDelete('cascade');
            $table->foreign('trailer_id')->references('id')->on('trailers')->onDelete('set null');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
