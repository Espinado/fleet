<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * ⚠️ У тебя таблица MyISAM:
         * - нет FK и транзакций
         * - хуже целостность и надёжность
         *
         * Переводим в InnoDB сразу, пока таблица маленькая.
         */
        DB::statement("ALTER TABLE `truck_odometer_events` ENGINE=InnoDB");

        Schema::table('truck_odometer_events', function (Blueprint $table) {
            // Links
            $table->unsignedBigInteger('trip_id')->nullable()->after('driver_id');
            $table->unsignedBigInteger('trip_step_id')->nullable()->after('trip_id');
            $table->unsignedBigInteger('trip_expense_id')->nullable()->after('trip_step_id');

            // Expense snapshot (если событие = расход)
            $table->string('expense_category', 50)->nullable()->after('trip_expense_id');
            $table->decimal('expense_amount', 10, 2)->nullable()->after('expense_category');

            // Step status snapshot (если событие = смена статуса шага)
            $table->unsignedTinyInteger('step_status')->nullable()->after('expense_amount');

            // Indexes for stats
            $table->index(['trip_id', 'occurred_at'], 'toe_trip_occurred_idx');
            $table->index(['trip_step_id', 'occurred_at'], 'toe_step_occurred_idx');
            $table->index(['trip_expense_id', 'occurred_at'], 'toe_expense_occurred_idx');

            $table->index(['expense_category', 'occurred_at'], 'toe_exp_cat_occurred_idx');
            $table->index(['step_status', 'occurred_at'], 'toe_step_status_occurred_idx');
        });

        /**
         * FK можно добавить позже (или сразу), но иногда на проде выстреливает,
         * если есть “грязные” данные. Поэтому сейчас делаем мягко:
         * связи через колонки + индексы, без FK.
         */
    }

    public function down(): void
    {
        Schema::table('truck_odometer_events', function (Blueprint $table) {
            $table->dropIndex('toe_trip_occurred_idx');
            $table->dropIndex('toe_step_occurred_idx');
            $table->dropIndex('toe_expense_occurred_idx');
            $table->dropIndex('toe_exp_cat_occurred_idx');
            $table->dropIndex('toe_step_status_occurred_idx');

            $table->dropColumn([
                'trip_id',
                'trip_step_id',
                'trip_expense_id',
                'expense_category',
                'expense_amount',
                'step_status',
            ]);
        });

        // Возвращать обратно в MyISAM смысла нет — но если очень нужно:
        // DB::statement("ALTER TABLE `truck_odometer_events` ENGINE=MyISAM");
    }
};
