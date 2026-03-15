<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_order_id')->constrained('transport_orders')->cascadeOnDelete();
            $table->string('type', 20)->comment('loading|unloading');
            $table->unsignedInteger('country_id')->nullable();
            $table->unsignedInteger('city_id')->nullable();
            $table->string('address')->nullable();
            $table->date('date')->nullable();
            $table->string('time', 20)->nullable();
            $table->string('contact_phone')->nullable();
            $table->unsignedSmallInteger('order')->default(0)->comment('sort order');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['transport_order_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_steps');
    }
};
