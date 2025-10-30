<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class MigrationHelper
{
    public static function hasTable(string $table): bool
    {
        return Schema::hasTable($table);
    }

    public static function hasColumn(string $table, string $column): bool
    {
        return Schema::hasColumn($table, $column);
    }

    public static function listIndexes(string $table): array
    {
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        return $sm->listTableIndexes($table);
    }

    public static function hasIndex(string $table, string $indexName): bool
    {
        $indexes = self::listIndexes($table);
        return array_key_exists($indexName, $indexes);
    }

    public static function dropForeignIfExists(string $table, string $columnOrIndex): void
    {
        if (!self::hasTable($table)) return;
        try {
            Schema::table($table, function (Blueprint $tbl) use ($columnOrIndex) {
                $tbl->dropForeign(is_array($columnOrIndex) ? $columnOrIndex : [$columnOrIndex]);
            });
        } catch (\Throwable $e) {}
    }

    public static function dropUniqueIfExists(string $table, string $indexName): void
    {
        if (!self::hasTable($table)) return;
        if (!self::hasIndex($table, $indexName)) return;

        Schema::table($table, function (Blueprint $tbl) use ($indexName) {
            $tbl->dropUnique($indexName);
        });
    }

    public static function addUniqueIfMissing(string $table, string $column, string $indexName): void
    {
        if (!self::hasTable($table)) return;
        if (self::hasIndex($table, $indexName)) return;

        Schema::table($table, function (Blueprint $tbl) use ($column, $indexName) {
            $tbl->unique($column, $indexName);
        });
    }
}
