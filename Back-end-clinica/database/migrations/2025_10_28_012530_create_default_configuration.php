<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Criar configuração padrão
        DB::table('configuracoes_agendamento')->insert([
            'user_id' => null,
            'seg' => 1,
            'ter' => 1,
            'qua' => 1,
            'qui' => 1,
            'sex' => 1,
            'sab' => 0,
            'dom' => 0,
            'horario_inicio' => '08:00',
            'horario_fim' => '18:00',
            'duracao_consulta' => 30,
            'intervalo_consulta' => 15,
            'pausas' => json_encode([
                ['nome' => 'Almoço', 'inicio' => '12:00', 'fim' => '13:00']
            ]),
            'data_inicio_vigencia' => '2024-01-01',
            'data_fim_vigencia' => null,
            'padrao' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('configuracoes_agendamento')->where('padrao', 1)->delete();
    }
};
