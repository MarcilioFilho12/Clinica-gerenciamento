<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('consultas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->comment('ID do profissional/médico');
            $table->foreignId('paciente_id')->constrained('cadastros')->comment('ID do paciente');
            $table->foreignId('parceiro_id')->nullable()->constrained('parceiros')->comment('ID do parceiro/convênio');

            $table->string('procedimento')->comment('Tipo: Consulta, Retorno, Exame, Cirurgia');
            $table->date('data')->comment('Data da consulta');
            $table->time('horario_inicio')->comment('Horário de início');
            $table->time('horario_fim')->comment('Horário de fim');
            $table->enum('prioridade', ['normal', 'alta', 'baixa'])->default('normal')->comment('Prioridade da consulta');
            $table->text('observacoes')->nullable()->comment('Observações da consulta');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultas');
    }
};
