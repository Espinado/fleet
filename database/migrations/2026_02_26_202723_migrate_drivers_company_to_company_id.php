<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            // 1) добавляем новую колонку company_id (если ещё нет)
            if (!Schema::hasColumn('drivers', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('company');
                $table->index('company_id');
            }
        });

        // 2) переносим данные company -> company_id (если старая колонка существует)
        if (Schema::hasColumn('drivers', 'company')) {
            DB::statement('UPDATE drivers SET company_id = company WHERE company_id IS NULL');
        }

        // 3) добавляем FK (после переноса)
        Schema::table('drivers', function (Blueprint $table) {
            // FK добавляем только если ещё не добавлен
            // Laravel не умеет "if fk exists", поэтому делаем через try/catch в рантайме обычно.
            // Здесь просто добавим — если упадёт, значит у тебя уже есть FK/или несовпадают типы.
            try {
                $table->foreign('company_id')
                    ->references('id')
                    ->on('companies')
                    ->nullOnDelete();
            } catch (\Throwable $e) {
                // ignore
            }
        });

        // 4) удаляем старую колонку company
        Schema::table('drivers', function (Blueprint $table) {
            if (Schema::hasColumn('drivers', 'company')) {
                $table->dropColumn('company');
            }
        });
    }

    public function down(): void
    {
        // Возврат в обратную сторону
        Schema::table('drivers', function (Blueprint $table) {
            if (!Schema::hasColumn('drivers', 'company')) {
                $table->unsignedBigInteger('company')->nullable()->after('id');
                $table->index('company');
            }
        });

        if (Schema::hasColumn('drivers', 'company_id')) {
            DB::statement('UPDATE drivers SET company = company_id WHERE company IS NULL');
        }

        Schema::table('drivers', function (Blueprint $table) {
            // снять FK и колонку company_id
            if (Schema::hasColumn('drivers', 'company_id')) {
                try { $table->dropForeign(['company_id']); } catch (\Throwable $e) {}
                try { $table->dropIndex(['company_id']); } catch (\Throwable $e) {}
                $table->dropColumn('company_id');
            }
        });
    }
};
