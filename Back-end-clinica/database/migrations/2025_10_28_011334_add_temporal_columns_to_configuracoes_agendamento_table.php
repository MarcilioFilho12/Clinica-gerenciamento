<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('configuracoes_agendamento', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Adicionar colunas como nullable primeiro
            $table->date('data_inicio_vigencia')->nullable()->after('user_id');
            $table->date('data_fim_vigencia')->nullable()->after('data_inicio_vigencia');
            $table->tinyInteger('padrao')->default(0)->after('data_fim_vigencia')->comment('1 = padrão, 0 = personalizada');
        });

        // Atualizar registros existentes com data padrão
        DB::table('configuracoes_agendamento')
            ->whereNull('data_inicio_vigencia')
            ->update([
                'data_inicio_vigencia' => '2024-01-01',
                'padrao' => 0
            ]);

        // Agora tornar a coluna NOT NULL
        Schema::table('configuracoes_agendamento', function (Blueprint $table) {
            $table->date('data_inicio_vigencia')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuracoes_agendamento', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'data_inicio_vigencia', 'data_fim_vigencia', 'ativa', 'padrao']);
        });
    }
};
