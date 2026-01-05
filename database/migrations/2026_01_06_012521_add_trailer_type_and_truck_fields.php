<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =========================
        // trailers: type_id (default 1)
        // =========================
        Schema::table('trailers', function (Blueprint $table) {
            if (!Schema::hasColumn('trailers', 'type_id')) {
                $table->unsignedTinyInteger('type_id')
                    ->default(1)
                    ->index()
                    ->after('plate'); // поменяй after(...) если у тебя другая подходящая колонка
            }
        });

        // =========================
        // trucks: license + mapon fields (nullable)
        // =========================
        Schema::table('trucks', function (Blueprint $table) {
            if (!Schema::hasColumn('trucks', 'license_number')) {
                $table->string('license_number', 50)->nullable()->after('plate');
            }

            if (!Schema::hasColumn('trucks', 'license_issued')) {
                $table->date('license_issued')->nullable()->after('license_number');
            }

            if (!Schema::hasColumn('trucks', 'license_expired')) {
                $table->date('license_expired')->nullable()->after('license_issued');
            }

            if (!Schema::hasColumn('trucks', 'mapon_box_id')) {
                $table->string('mapon_box_id', 50)->nullable()->index()->after('license_expired');
            }

            if (!Schema::hasColumn('trucks', 'mapon_unit_id')) {
                $table->string('mapon_unit_id', 50)->nullable()->index()->after('mapon_box_id');
            }
        });
    }

    public function down(): void
    {
        // trailers
        Schema::table('trailers', function (Blueprint $table) {
            if (Schema::hasColumn('trailers', 'type_id')) {
                $table->dropIndex(['type_id']);
                $table->dropColumn('type_id');
            }
        });

        // trucks
        Schema::table('trucks', function (Blueprint $table) {
            if (Schema::hasColumn('trucks', 'mapon_unit_id')) {
                $table->dropIndex(['mapon_unit_id']);
                $table->dropColumn('mapon_unit_id');
            }

            if (Schema::hasColumn('trucks', 'mapon_box_id')) {
                $table->dropIndex(['mapon_box_id']);
                $table->dropColumn('mapon_box_id');
            }

            if (Schema::hasColumn('trucks', 'license_expired')) {
                $table->dropColumn('license_expired');
            }

            if (Schema::hasColumn('trucks', 'license_issued')) {
                $table->dropColumn('license_issued');
            }

            if (Schema::hasColumn('trucks', 'license_number')) {
                $table->dropColumn('license_number');
            }
        });
    }
};
