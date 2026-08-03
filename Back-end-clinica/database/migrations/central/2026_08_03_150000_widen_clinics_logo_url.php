<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * logo_url precisa caber data-URI (base64) — disco do Railway é efêmero.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection('central')->hasTable('clinics')) {
            return;
        }

        DB::connection('central')->statement(
            'ALTER TABLE clinics MODIFY logo_url MEDIUMTEXT NULL'
        );
    }

    public function down(): void
    {
        if (! Schema::connection('central')->hasTable('clinics')) {
            return;
        }

        DB::connection('central')->statement(
            'ALTER TABLE clinics MODIFY logo_url VARCHAR(2048) NULL'
        );
    }
};
