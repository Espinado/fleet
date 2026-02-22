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
    Schema::table('trucks', function (Blueprint $table) {
        $table->boolean('can_available')
            ->default(false)
            ->after('mapon_unit_id'); // или после любого подходящего поля
    });
}

public function down(): void
{
    Schema::table('trucks', function (Blueprint $table) {
        $table->dropColumn('can_available');
    });
}
};
