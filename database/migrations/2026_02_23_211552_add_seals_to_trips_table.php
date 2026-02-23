<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->string('cont_nr', 50)->nullable()->after('trailer_id');
            $table->string('seal_nr', 50)->nullable()->after('cont_nr');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['cont_nr', 'seal_nr']);
        });
    }
};
