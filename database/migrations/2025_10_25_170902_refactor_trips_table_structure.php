<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ Таблица trips уже актуальна, никаких изменений не требуется
        if (!Schema::hasTable('trips')) return;

        Schema::table('trips', function (Blueprint $table) {
            // оставлено пустым — фиктивная миграция, чтобы Laravel отметил как DONE
        });
    }

    public function down(): void
    {
        // ничего не откатываем
    }
};
