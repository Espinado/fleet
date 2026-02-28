<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'company_id')) {
                $table->foreignId('company_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('companies')
                    ->nullOnDelete()
                    ->index();
            }

            // если у тебя уже есть role — НЕ добавляй
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('manager')->after('email')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'company_id')) {
                $table->dropConstrainedForeignId('company_id');
            }
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
