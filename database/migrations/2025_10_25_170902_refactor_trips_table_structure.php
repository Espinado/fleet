<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            // 1️⃣ Удаляем старое поле client_id, если оно осталось
            if (Schema::hasColumn('trips', 'client_id')) {
                $table->dropForeign(['client_id']);
                $table->dropColumn('client_id');
            }

            // 2️⃣ Удаляем старое поле cargo (оно теперь заменено на cargo_description и т.д.)
            if (Schema::hasColumn('trips', 'cargo')) {
                $table->dropColumn('cargo');
            }

            // 3️⃣ Удаляем старое payer_type (теперь используется payer_type_id)
            if (Schema::hasColumn('trips', 'payer_type')) {
                $table->dropColumn('payer_type');
            }

            // 4️⃣ Добавляем shipper_id и consignee_id
            if (!Schema::hasColumn('trips', 'shipper_id')) {
                $table->unsignedBigInteger('shipper_id')->nullable()->after('trailer_id');
            }

            if (!Schema::hasColumn('trips', 'consignee_id')) {
                $table->unsignedBigInteger('consignee_id')->nullable()->after('shipper_id');
            }

            // 5️⃣ Добавляем payer_type_id (например, 1 — отправитель, 2 — получатель, 3 — третье лицо)
            if (!Schema::hasColumn('trips', 'payer_type_id')) {
                $table->unsignedTinyInteger('payer_type_id')->nullable()->after('payment_terms');
            }

            // 6️⃣ Обновляем тип payment_terms (если нужно хранить дату)
            if (Schema::hasColumn('trips', 'payment_terms')) {
                $table->date('payment_terms')->nullable()->change();
            }

            // 7️⃣ Создаём связи, если таблица clients существует
            if (Schema::hasTable('clients')) {
                $table->foreign('shipper_id')->references('id')->on('clients')->onDelete('set null');
                $table->foreign('consignee_id')->references('id')->on('clients')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            // Откат: возвращаем client_id и payer_type
            $table->unsignedBigInteger('client_id')->nullable()->after('trailer_id');
            $table->string('payer_type')->nullable()->after('payment_terms');

            // Удаляем новые поля
            $table->dropForeign(['shipper_id']);
            $table->dropForeign(['consignee_id']);
            $table->dropColumn(['shipper_id', 'consignee_id', 'payer_type_id']);
        });
    }
};
