<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Histórico imutável de mudanças de status da consulta — auditoria completa (nunca apagado/editado).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consulta_historico', function (Blueprint $table) {
            $table->id();

            $table->foreignId('consulta_id')->constrained('consultas')->cascadeOnDelete();
            $table->string('status_anterior', 20)->nullable();
            $table->string('status_novo', 20);
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('acao', 40)->nullable()->comment('Ex.: cancelar, transferir, reagendar, confirmar, no_show, job_vencidas');
            $table->text('motivo')->nullable();
            $table->text('observacao')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['consulta_id', 'created_at'], 'consulta_historico_consulta_idx');
            $table->index('status_novo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consulta_historico');
    }
};
