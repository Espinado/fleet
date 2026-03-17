<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_maintenance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('truck_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('trailer_id')->nullable()->constrained()->cascadeOnDelete();
            $table->date('performed_at');
            $table->unsignedInteger('odometer_km')->nullable();
            $table->text('description')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->timestamps();

            $table->index(['company_id', 'performed_at']);
            $table->index('truck_id');
            $table->index('trailer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenance');
    }
};
