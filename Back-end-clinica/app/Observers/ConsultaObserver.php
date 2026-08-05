<?php

namespace App\Observers;

use App\Models\Consulta;
use App\Models\ConsultaHistorico;

/**
 * Rede de segurança de auditoria: garante que NENHUMA mudança de status fique sem
 * histórico, mesmo que algum código futuro altere `status` sem passar pelo
 * ConsultaStatusService. O fluxo "oficial" (via Service) desativa estes eventos
 * durante sua própria transação (Consulta::withoutEvents) porque já grava um
 * histórico mais rico (ação, motivo, usuário, IP).
 */
class ConsultaObserver
{
    public function created(Consulta $consulta): void
    {
        if (! $consulta->status) {
            return;
        }

        ConsultaHistorico::create([
            'consulta_id' => $consulta->id,
            'status_anterior' => null,
            'status_novo' => $consulta->status,
            'usuario_id' => request()?->user()?->id,
            'acao' => 'criar',
            'observacao' => 'Consulta criada.',
            'ip' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    public function updated(Consulta $consulta): void
    {
        if (! $consulta->wasChanged('status')) {
            return;
        }

        ConsultaHistorico::create([
            'consulta_id' => $consulta->id,
            'status_anterior' => $consulta->getOriginal('status'),
            'status_novo' => $consulta->status,
            'usuario_id' => request()?->user()?->id,
            'acao' => 'alteracao_direta',
            'observacao' => 'Registrado automaticamente (fora do ConsultaStatusService).',
            'ip' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }
}
