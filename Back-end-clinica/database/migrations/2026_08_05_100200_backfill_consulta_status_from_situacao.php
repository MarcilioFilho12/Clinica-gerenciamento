<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill não destrutivo: popula `status` (novo) a partir do `situacao_id` (legado)
 * + `chegada_em` para as consultas já existentes. Não apaga nem sobrescreve nenhum
 * dado legado — só preenche a coluna nova.
 *
 * Mapa (ver App\Support\ConsultaStatus::legacySituacaoId para o caminho inverso):
 *   situacao_id 4 (encerrado)     -> REALIZADA
 *   situacao_id 5 (cancelado)     -> CANCELADA
 *   situacao_id 6 (em_atendimento)-> EM_ATENDIMENTO
 *   situacao_id 1 (ativo) + chegada_em preenchida -> CONFIRMADA
 *   situacao_id 1 (ativo) sem chegada_em          -> PENDENTE (ou VENCIDA se já passou da hora)
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('consultas')->where('situacao_id', 4)->update(['status' => 'REALIZADA']);
        DB::table('consultas')->where('situacao_id', 5)->update(['status' => 'CANCELADA']);
        DB::table('consultas')->where('situacao_id', 6)->update(['status' => 'EM_ATENDIMENTO']);

        DB::table('consultas')
            ->where('situacao_id', 1)
            ->whereNotNull('chegada_em')
            ->update(['status' => 'CONFIRMADA']);

        // Pendentes cuja data/hora já passou nascem como VENCIDA (coerente com a regra
        // "status=PENDENTE AND data_hora<NOW()"), evitando recalcular tudo via job no primeiro run.
        // Comparação portátil (sem função de banco específica, ex.: TIMESTAMP() do MySQL),
        // funciona igual em MySQL e SQLite (testes).
        $hoje = now()->format('Y-m-d');
        $agora = now()->format('H:i:s');

        DB::table('consultas')
            ->where('situacao_id', 1)
            ->whereNull('chegada_em')
            ->where(function ($q) use ($hoje, $agora) {
                $q->where('data', '<', $hoje)
                    ->orWhere(function ($q2) use ($hoje, $agora) {
                        $q2->where('data', '=', $hoje)->where('horario_inicio', '<', $agora);
                    });
            })
            ->update(['status' => 'VENCIDA']);

        DB::table('consultas')
            ->where('situacao_id', 1)
            ->whereNull('chegada_em')
            ->where(function ($q) use ($hoje, $agora) {
                $q->where('data', '>', $hoje)
                    ->orWhere(function ($q2) use ($hoje, $agora) {
                        $q2->where('data', '=', $hoje)->where('horario_inicio', '>=', $agora);
                    });
            })
            ->update(['status' => 'PENDENTE']);
    }

    public function down(): void
    {
        // Reversão segura: volta todas para o default (não há perda, situacao_id é a fonte antiga).
        DB::table('consultas')->update(['status' => 'PENDENTE']);
    }
};
