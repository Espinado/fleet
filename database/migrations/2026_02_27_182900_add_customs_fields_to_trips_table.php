<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            if (!Schema::hasColumn('trips', 'customs')) {
                $table->boolean('customs')->default(false)->after('seal_nr');
            }

            if (!Schema::hasColumn('trips', 'customs_address')) {
                $table->string('customs_address', 255)->nullable()->after('customs');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            if (Schema::hasColumn('trips', 'customs_address')) {
                $table->dropColumn('customs_address');
            }
            if (Schema::hasColumn('trips', 'customs')) {
                $table->dropColumn('customs');
            }
        });
    }
};
