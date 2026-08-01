<?php

namespace App\Support;

use App\Models\Clinic;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

final class TenantContext
{
    private static ?Clinic $clinic = null;

    public static function clinic(): ?Clinic
    {
        return self::$clinic;
    }

    public static function slug(): ?string
    {
        return self::$clinic?->slug;
    }

    public static function clear(): void
    {
        self::$clinic = null;
    }

    public static function set(Clinic $clinic): void
    {
        self::$clinic = $clinic;
        self::applyDatabase($clinic->database_name);
    }

    public static function applyDatabase(string $databaseName): void
    {
        Config::set('database.connections.mysql.database', $databaseName);
        DB::purge('mysql');
        DB::reconnect('mysql');
        Config::set('database.default', 'mysql');
    }
}
