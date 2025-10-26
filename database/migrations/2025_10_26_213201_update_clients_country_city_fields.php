<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // ✅ Добавляем новые поля, просто в конец таблицы (без after)
            if (!Schema::hasColumn('clients', 'jur_country_id')) {
                $table->unsignedInteger('jur_country_id')->nullable();
            }
            if (!Schema::hasColumn('clients', 'jur_city_id')) {
                $table->unsignedInteger('jur_city_id')->nullable();
            }
            if (!Schema::hasColumn('clients', 'fiz_country_id')) {
                $table->unsignedInteger('fiz_country_id')->nullable();
            }
            if (!Schema::hasColumn('clients', 'fiz_city_id')) {
                $table->unsignedInteger('fiz_city_id')->nullable();
            }
        });

        // ✅ Заполняем значениями 16 и 51
        DB::table('clients')->update([
            'jur_country_id' => 16,
            'jur_city_id' => 51,
            'fiz_country_id' => 16,
            'fiz_city_id' => 51,
        ]);
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'jur_country_id')) {
                $table->dropColumn('jur_country_id');
            }
            if (Schema::hasColumn('clients', 'jur_city_id')) {
                $table->dropColumn('jur_city_id');
            }
            if (Schema::hasColumn('clients', 'fiz_country_id')) {
                $table->dropColumn('fiz_country_id');
            }
            if (Schema::hasColumn('clients', 'fiz_city_id')) {
                $table->dropColumn('fiz_city_id');
            }
        });
    }
};
