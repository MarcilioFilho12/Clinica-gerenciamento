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
        Schema::create('configuracoes_agendamento', function (Blueprint $table) {
            $table->id();

            $table->tinyInteger('seg')->default(0)->comment('Segunda-feira');
            $table->tinyInteger('ter')->default(0)->comment('Terça-feira');
            $table->tinyInteger('qua')->default(0)->comment('Quarta-feira');
            $table->tinyInteger('qui')->default(0)->comment('Quinta-feira');
            $table->tinyInteger('sex')->default(0)->comment('Sexta-feira');
            $table->tinyInteger('sab')->default(0)->comment('Sábado');
            $table->tinyInteger('dom')->default(0)->comment('Domingo');

            $table->time('horario_inicio')->comment('Horário de início do atendimento');
            $table->time('horario_fim')->comment('Horário de fim do atendimento');

            $table->integer('duracao_consulta')->comment('Duração da consulta em minutos');
            $table->integer('intervalo_consulta')->comment('Intervalo entre consultas em minutos');

            // Estrutura: [{"nome": "Almoço", "inicio": "12:00", "fim": "13:00"}, ...]
            $table->json('pausas')->nullable()->comment('JSON das pausas no horário de funcionamento');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracoes_agendamento');
    }
};
