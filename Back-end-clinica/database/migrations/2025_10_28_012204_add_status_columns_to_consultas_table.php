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
        Schema::table('consultas', function (Blueprint $table) {
            $table->unsignedBigInteger('situacao_id')->after('parceiro_id');
            $table->foreign('situacao_id')->references('id')->on('situacoes')->onDelete('restrict');

            $table->unsignedBigInteger('configuracao_id')->nullable()->after('situacao_id');
            $table->foreign('configuracao_id')->references('id')->on('configuracoes_agendamento')->onDelete('set null');

            $table->text('motivo_cancelamento')->nullable()->after('configuracao_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->dropForeign(['situacao_id']);
            $table->dropForeign(['configuracao_id']);
            $table->dropColumn(['situacao_id', 'configuracao_id', 'motivo_cancelamento']);
        });
    }
};
