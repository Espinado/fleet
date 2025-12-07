<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\TripStepStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_steps', function (Blueprint $table) {
            // tinyint, потому что enum int-based
            $table->unsignedTinyInteger('status')
                ->default(TripStepStatus::NOT_STARTED->value)
                ->after('order');

            $table->timestamp('started_at')->nullable()->after('status');
            $table->timestamp('completed_at')->nullable()->after('started_at');
        });
    }

    public function down(): void
    {
        Schema::table('trip_steps', function (Blueprint $table) {
            $table->dropColumn(['status', 'started_at', 'completed_at']);
        });
    }
};
