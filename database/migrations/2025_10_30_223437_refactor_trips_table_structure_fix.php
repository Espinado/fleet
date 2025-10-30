<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // === 1️⃣ Проверка наличия таблицы ===
        if (!Schema::hasTable('trips')) return;

        // === 2️⃣ Попытка удалить foreign key безопасно ===
        try {
            Schema::table('trips', function (Blueprint $table) {
                $table->dropForeign(['client_id']);
            });
        } catch (\Throwable $e) {
            // Foreign key уже отсутствует — игнорируем
        }

        // === 3️⃣ Попытка удалить колонку, если вдруг осталась ===
        if (Schema::hasColumn('trips', 'client_id')) {
            try {
                Schema::table('trips', function (Blueprint $table) {
                    $table->dropColumn('client_id');
                });
            } catch (\Throwable $e) {
                // Колонка уже удалена — игнорируем
            }
        }
    }

    public function down(): void
    {
        // === 🔄 Откат (если нужно восстановить client_id) ===
        if (!Schema::hasTable('trips')) return;

        Schema::table('trips', function (Blueprint $table) {
            if (!Schema::hasColumn('trips', 'client_id')) {
                $table->foreignId('client_id')
                      ->nullable()
                      ->constrained('clients')
                      ->nullOnDelete();
            }
        });
    }
};
