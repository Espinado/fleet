<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('trip_steps', function (Blueprint $table) {
            if (!Schema::hasColumn('trip_steps', 'order')) {
                $table->unsignedInteger('order')
                      ->nullable()
                      ->after('date')
                      ->comment('Порядок шага в маршруте, задаваемый админом');
            }

            // очень полезно — оптимизирует сортировки
            $table->index('order');
        });
    }

    public function down()
    {
        Schema::table('trip_steps', function (Blueprint $table) {
            if (Schema::hasColumn('trip_steps', 'order')) {
                $table->dropColumn('order');
            }
        });
    }
};
