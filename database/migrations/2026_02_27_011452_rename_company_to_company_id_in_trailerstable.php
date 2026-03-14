<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('trailers', 'company') && !Schema::hasColumn('trailers', 'company_id')) {
            Schema::table('trailers', function (Blueprint $table) {
                $table->renameColumn('company', 'company_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('trailers', 'company_id') && !Schema::hasColumn('trailers', 'company')) {
            Schema::table('trailers', function (Blueprint $table) {
                $table->renameColumn('company_id', 'company');
            });
        }
    }
};
