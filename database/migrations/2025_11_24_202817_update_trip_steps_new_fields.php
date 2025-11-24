<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('trip_steps', function (Blueprint $table) {

            if (!Schema::hasColumn('trip_steps', 'client_id')) {
                $table->unsignedBigInteger('client_id')->nullable()->after('type');
            }

            if (!Schema::hasColumn('trip_steps', 'time')) {
                $table->time('time')->nullable()->after('date');
            }

            if (!Schema::hasColumn('trip_steps', 'notes')) {
                $table->text('notes')->nullable()->after('time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trip_steps', function (Blueprint $table) {
            if (Schema::hasColumn('trip_steps', 'client_id')) {
                $table->dropColumn('client_id');
            }
            if (Schema::hasColumn('trip_steps', 'time')) {
                $table->dropColumn('time');
            }
            if (Schema::hasColumn('trip_steps', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
