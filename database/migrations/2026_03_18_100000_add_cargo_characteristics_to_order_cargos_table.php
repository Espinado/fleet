<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_cargos', function (Blueprint $table) {
            $table->unsignedInteger('packages')->nullable()->after('pallets');
            $table->unsignedInteger('units')->nullable()->after('packages');
            $table->decimal('net_weight', 10, 2)->nullable()->after('weight_kg');
            $table->decimal('gross_weight', 10, 2)->nullable()->after('net_weight');
            $table->decimal('tonnes', 10, 3)->nullable()->after('gross_weight');
            $table->decimal('loading_meters', 10, 2)->nullable()->after('volume_m3');
            $table->string('customs_code', 32)->nullable()->after('description');
            $table->string('hazmat', 64)->nullable()->after('customs_code');
            $table->string('temperature', 32)->nullable()->after('hazmat');
            $table->boolean('stackable')->default(false)->after('temperature');
            $table->text('instructions')->nullable()->after('stackable');
            $table->text('remarks')->nullable()->after('instructions');
        });
    }

    public function down(): void
    {
        Schema::table('order_cargos', function (Blueprint $table) {
            $table->dropColumn([
                'packages', 'units', 'net_weight', 'gross_weight', 'tonnes',
                'loading_meters', 'customs_code', 'hazmat', 'temperature',
                'stackable', 'instructions', 'remarks',
            ]);
        });
    }
};
