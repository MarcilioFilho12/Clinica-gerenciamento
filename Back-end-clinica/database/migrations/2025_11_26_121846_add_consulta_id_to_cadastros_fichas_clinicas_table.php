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
        Schema::table('cadastros_fichas_clinicas', function (Blueprint $table) {
            // Adicionar campo consulta_id (opcional/nullable)
            $table->foreignId('consulta_id')
                ->nullable()
                ->after('user_id')
                ->constrained('consultas')
                ->onDelete('set null')
                ->comment('ID da consulta vinculada (opcional)');

            // Adicionar índice para melhor performance nas consultas
            $table->index('consulta_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cadastros_fichas_clinicas', function (Blueprint $table) {
            // Remover índice primeiro
            $table->dropIndex(['consulta_id']);

            // Remover foreign key
            $table->dropForeign(['consulta_id']);

            // Remover coluna
            $table->dropColumn('consulta_id');
        });
    }
};
