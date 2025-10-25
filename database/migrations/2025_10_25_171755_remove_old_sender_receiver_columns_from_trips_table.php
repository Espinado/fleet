<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $columns = [
                'origin_company_name',
                'origin_contact_person',
                'origin_phone',
                'origin_email',
                'destination_company_name',
                'destination_contact_person',
                'destination_phone',
                'destination_email',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('trips', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->string('origin_company_name')->nullable();
            $table->string('origin_contact_person')->nullable();
            $table->string('origin_phone')->nullable();
            $table->string('origin_email')->nullable();
            $table->string('destination_company_name')->nullable();
            $table->string('destination_contact_person')->nullable();
            $table->string('destination_phone')->nullable();
            $table->string('destination_email')->nullable();
        });
    }
};
