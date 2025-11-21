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
        Schema::create('trip_steps', function (Blueprint $table) {
            $table->id();
              $table->unsignedBigInteger('trip_id');
            $table->unsignedBigInteger('trip_cargo_id')->nullable();

            // Тип шага
            $table->enum('type', ['loading', 'unloading']);

            // Локация
            $table->unsignedInteger('country_id')->nullable();
            $table->unsignedInteger('city_id')->nullable();
            $table->string('address')->nullable();

            // Дата (когда водитель должен быть там)
            $table->date('date')->nullable();

            // Порядок выполнения
            $table->unsignedInteger('sequence')->default(0);

            // Дополнительная информация
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->foreign('trip_cargo_id')->references('id')->on('trip_cargos')->onDelete('cascade');
           
        });
            
       
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_steps');
    }
};
