<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'contact_person')) {
                $table->string('contact_person')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('companies', 'rating')) {
                $table->unsignedTinyInteger('rating')->nullable()->after('contact_person')->comment('1-5 for external carriers');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'rating')) {
                $table->dropColumn('rating');
            }
            if (Schema::hasColumn('companies', 'contact_person')) {
                $table->dropColumn('contact_person');
            }
        });
    }
};
