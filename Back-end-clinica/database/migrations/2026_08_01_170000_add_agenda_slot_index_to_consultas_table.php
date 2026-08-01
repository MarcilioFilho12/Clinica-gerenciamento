<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Índice para agenda (profissional + data + início) — acelera lock anti double-book.
 * Unique parcial por situação não é portátil no MySQL; a serialização fica na transação.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->index(
                ['user_id', 'data', 'horario_inicio'],
                'consultas_user_data_inicio_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->dropIndex('consultas_user_data_inicio_idx');
        });
    }
};
