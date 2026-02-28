<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'is_broker')) {
                $table->boolean('is_broker')->default(false)->after('type');
            }

            if (!Schema::hasColumn('companies', 'is_third_party')) {
                $table->boolean('is_third_party')->default(false)->after('is_broker');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'is_third_party')) {
                $table->dropColumn('is_third_party');
            }

            if (Schema::hasColumn('companies', 'is_broker')) {
                $table->dropColumn('is_broker');
            }
        });
    }
};
