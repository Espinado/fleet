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
       Schema::create('trip_step_documents', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('trip_step_id');
    $table->unsignedBigInteger('trip_id')->nullable();
    $table->unsignedBigInteger('cargo_id')->nullable();

    $table->unsignedBigInteger('uploader_user_id')->nullable();
    $table->unsignedBigInteger('uploader_driver_id')->nullable();

    $table->string('type')->nullable();
    $table->string('file_path');
    $table->string('original_name')->nullable();
    $table->text('comment')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_step_documents');
    }
};
