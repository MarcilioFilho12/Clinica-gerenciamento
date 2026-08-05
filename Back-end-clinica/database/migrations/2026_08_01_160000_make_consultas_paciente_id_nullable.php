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

        // `ALTER ... MODIFY` é sintaxe exclusiva do MySQL. Em SQLite (usado nos
        // testes automatizados — ver phpunit.xml) o Schema Builder do Laravel 11+
        // já sabe recriar a tabela para alterar a coluna, sem precisar de doctrine/dbal.
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE consultas MODIFY paciente_id BIGINT UNSIGNED NULL');
        } else {
            Schema::table('consultas', function (Blueprint $table) {
                $table->unsignedBigInteger('paciente_id')->nullable()->change();
            });
        }

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

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE consultas MODIFY paciente_id BIGINT UNSIGNED NOT NULL');
        } else {
            Schema::table('consultas', function (Blueprint $table) {
                $table->unsignedBigInteger('paciente_id')->nullable(false)->change();
            });
        }

        Schema::table('consultas', function (Blueprint $table) {
            $table->foreign('paciente_id')
                ->references('id')
                ->on('cadastros');
        });
    }
};
