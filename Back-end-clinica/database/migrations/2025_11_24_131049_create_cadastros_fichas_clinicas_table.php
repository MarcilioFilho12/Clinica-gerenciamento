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
        Schema::create('cadastros_fichas_clinicas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cadastro_id')->constrained('cadastros')->onDelete('cascade')->comment('ID do paciente');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict')->comment('ID do profissional que atendeu');

            $table->date('data_consulta')->comment('Data da consulta/atendimento');
            $table->text('observacoes')->nullable()->comment('Observações gerais da ficha clínica');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cadastros_fichas_clinicas');
    }
};
