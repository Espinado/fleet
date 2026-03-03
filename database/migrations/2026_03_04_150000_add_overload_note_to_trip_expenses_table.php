<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('trip_expenses', 'overload_note')) {
                $table->string('overload_note', 500)->nullable()->after('description')
                    ->comment('Перегрузка (текст от водителя, необязательно)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trip_expenses', function (Blueprint $table) {
            if (Schema::hasColumn('trip_expenses', 'overload_note')) {
                $table->dropColumn('overload_note');
            }
        });
    }
};
