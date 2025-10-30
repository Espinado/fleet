<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Support\MigrationHelper as MH;

return new class extends Migration
{
    public function up(): void
    {
        if (!MH::hasTable('drivers')) {
            Schema::create('drivers', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company')->nullable();

                // 🧍 Personal info
                $table->string('first_name');
                $table->string('last_name');
                $table->string('pers_code')->nullable();

                // 🌍 Citizenship & geo
                $table->unsignedSmallInteger('citizenship_id')->nullable();
                $table->unsignedSmallInteger('declared_country_id')->nullable();
                $table->unsignedSmallInteger('declared_city_id')->nullable();
                $table->unsignedSmallInteger('actual_country_id')->nullable();
                $table->unsignedSmallInteger('actual_city_id')->nullable();

                // 🏠 Address (раздельно)
                $table->string('declared_street')->nullable();
                $table->string('declared_building')->nullable();
                $table->string('declared_room')->nullable();
                $table->string('declared_postcode')->nullable();

                $table->string('actual_street')->nullable();
                $table->string('actual_building')->nullable();
                $table->string('actual_room')->nullable();
                $table->string('actual_postcode')->nullable();

                // 📞 Contacts
                $table->string('phone')->nullable();
                $table->string('email')->nullable();

                // 🚛 Docs
                $table->string('license_number');
                $table->date('license_issued');
                $table->date('license_end');
                $table->string('code95_issued')->nullable();
                $table->string('code95_end')->nullable();

                $table->date('permit_issued')->nullable();
                $table->date('permit_expired')->nullable();
                $table->date('medical_issued')->nullable();
                $table->date('medical_expired')->nullable();
                $table->date('declaration_issued')->nullable();
                $table->date('declaration_expired')->nullable();

                // 📷 Files
                $table->string('photo')->nullable();
                $table->string('license_photo')->nullable();
                $table->string('medical_certificate_photo')->nullable();

                // 🧾 Exams
                $table->date('medical_exam_passed')->nullable();
                $table->date('medical_exam_expired')->nullable();

                // 🔄 Status
                $table->tinyInteger('status')->default(1);
                $table->boolean('is_active')->default(true);

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (MH::hasTable('drivers')) {
            Schema::dropIfExists('drivers');
        }
    }
};
