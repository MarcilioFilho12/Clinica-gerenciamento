<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->dropForeign(['paciente_id']);
        });

        DB::statement('ALTER TABLE consultas MODIFY paciente_id BIGINT UNSIGNED NULL');

        Schema::table('consultas', function (Blueprint $table) {
            $table->foreign('paciente_id')
                ->references('id')
                ->on('cadastros')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->dropForeign(['paciente_id']);
        });

        DB::statement('ALTER TABLE consultas MODIFY paciente_id BIGINT UNSIGNED NOT NULL');

        Schema::table('consultas', function (Blueprint $table) {
            $table->foreign('paciente_id')
                ->references('id')
                ->on('cadastros');
        });
    }
};
