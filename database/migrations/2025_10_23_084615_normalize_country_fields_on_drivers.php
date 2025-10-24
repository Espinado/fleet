<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
                // 1) Добавляем новые числовые колонки (временно с суффиксом _id)
            $table->unsignedSmallInteger('citizenship_id')->nullable()->after('pers_code');
            $table->unsignedSmallInteger('declared_country_id')->nullable()->after('citizenship_id');
            $table->unsignedSmallInteger('actual_country_id')->nullable()->after('declared_country_id');
        });

        // 2) Готовим мапу: name -> id, iso -> id, id -> id
        $countries = config('countries', []);
        $map = [];

        foreach ($countries as $id => $c) {
            $name = strtolower(trim($c['name'] ?? ''));
            $iso  = strtolower(trim($c['iso'] ?? ''));
            if ($name !== '') $map[$name] = (int) $id;
            if ($iso  !== '') $map[$iso]  = (int) $id;
            // Также принимаем сам ID как строку
            $map[(string) $id] = (int) $id;
        }

        // 3) Хелпер для конвертации значения в ID
        $toId = function ($value) use ($map) {
            if (is_null($value)) return null;

            // Уже число?
            if (is_int($value)) return $value;
            if (is_numeric($value)) return (int) $value;

            // Строка: пробуем по имени/ISO
            $key = strtolower(trim((string) $value));
            return $map[$key] ?? null; // если нет в справочнике, оставим null
        };

        // 4) Бэкофилл новыми ID (обрабатываем построчно)
        DB::table('drivers')->orderBy('id')->chunkById(500, function ($rows) use ($toId) {
            foreach ($rows as $row) {
                DB::table('drivers')
                  ->where('id', $row->id)
                  ->update([
                      'citizenship_id'      => $toId($row->citizenship ?? null),
                      'declared_country_id' => $toId($row->declared_country ?? null),
                      'actual_country_id'   => $toId($row->actual_country ?? null),
                  ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('citizenship')->nullable()->after('pers_code');
            $table->string('declared_country')->nullable()->after('citizenship');
            $table->string('actual_country')->nullable()->after('declared_country');

            // Удалим числовые поля, если они существуют (после апа их уже нет, но на всякий случай)
            if (Schema::hasColumn('drivers', 'citizenship_id'))      $table->dropColumn('citizenship_id');
            if (Schema::hasColumn('drivers', 'declared_country_id')) $table->dropColumn('declared_country_id');
            if (Schema::hasColumn('drivers', 'actual_country_id'))   $table->dropColumn('actual_country_id');
        });
    }
};
