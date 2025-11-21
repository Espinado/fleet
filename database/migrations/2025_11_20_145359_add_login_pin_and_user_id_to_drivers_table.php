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
        Schema::table('drivers', function (Blueprint $table) {
                $table->string('login_pin', 10)->nullable()->after('phone');
    $table->unsignedBigInteger('user_id')->nullable()->after('login_pin');

    $table->foreign('user_id')
          ->references('id')
          ->on('users')
          ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       
        Schema::table('drivers', function (Blueprint $table) {
            // сначала удаляем foreign key
            $table->dropForeign(['user_id']);

            // затем удаляем столбцы
            $table->dropColumn('login_pin');
            $table->dropColumn('user_id');
        });
    }
};
