<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove unique constraint on plate so third-party trucks/trailers can repeat
     * the same plate (different companies or same third-party company).
     */
    public function up(): void
    {
        $this->dropUniqueIfExists('trucks', ['trucks_plate_unique', 'trucks_company_id_plate_unique']);
        $this->dropUniqueIfExists('trailers', ['trailers_plate_unique', 'trailers_company_id_plate_unique']);
    }

    public function down(): void
    {
        Schema::table('trucks', function (Blueprint $table) {
            $table->unique('plate', 'trucks_plate_unique');
        });
        Schema::table('trailers', function (Blueprint $table) {
            $table->unique('plate', 'trailers_plate_unique');
        });
    }

    private function dropUniqueIfExists(string $table, array $indexNames): void
    {
        foreach ($indexNames as $name) {
            try {
                Schema::table($table, function (Blueprint $t) use ($name) {
                    $t->dropUnique($name);
                });
            } catch (\Throwable $e) {
                // Index may already be dropped or have different name
            }
        }
    }
};
