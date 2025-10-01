<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fleet_tables', function (Blueprint $table) {
           Schema::table('drivers', function (Blueprint $table) {
            $table->string('pers_code')->unique()->nullable()->after('last_name');
            $table->string('photo')->nullable()->after('pers_code'); // фото водителя
            $table->string('license_photo')->nullable()->after('photo'); // фото водительских прав
            $table->string('medical_certificate_photo')->nullable()->after('license_photo'); // фото медсправки
            $table->date('medical_exam_passed')->nullable()->after('medical_certificate_photo');
            $table->date('medical_exam_expired')->nullable()->after('medical_exam_passed');
        });

        Schema::table('trucks', function (Blueprint $table) {
            $table->string('tech_passport_nr')->nullable()->after('vin');
            $table->date('tech_passport_issued')->nullable()->after('tech_passport_nr');
            $table->date('tech_passport_expired')->nullable()->after('tech_passport_issued');
            $table->string('tech_passport_photo')->nullable()->after('tech_passport_expired');
        });

        Schema::table('trailers', function (Blueprint $table) {
            $table->string('tech_passport_nr')->nullable()->after('vin');
            $table->date('tech_passport_issued')->nullable()->after('tech_passport_nr');
            $table->date('tech_passport_expired')->nullable()->after('tech_passport_issued');
            $table->string('tech_passport_photo')->nullable()->after('tech_passport_expired');
        });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fleet_tables', function (Blueprint $table) {
            Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn([
                'pers_code',
                'photo',
                'license_photo',
                'medical_certificate_photo',
                'medical_exam_passed',
                'medical_exam_expired',
            ]);
        });

        Schema::table('trucks', function (Blueprint $table) {
            $table->dropColumn([
                'tech_passport_nr',
                'tech_passport_issued',
                'tech_passport_expired',
                'tech_passport_photo',
            ]);
        });

        Schema::table('trailers', function (Blueprint $table) {
            $table->dropColumn([
                'tech_passport_nr',
                'tech_passport_issued',
                'tech_passport_expired',
                'tech_passport_photo',
            ]);
        });
        });
    }
};
