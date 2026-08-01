<?php

namespace App\Events;

use App\Support\TenantContext;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PacienteChamado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $consulta;

    public function __construct($consulta)
    {
        // PRG 1.4: sem PII nos logs — só IDs
        Log::info('PacienteChamado.dispatch', [
            'consulta_id' => $consulta->id ?? null,
            'clinic_slug' => TenantContext::slug(),
        ]);
        $this->consulta = $consulta;
    }

    public function broadcastOn(): Channel
    {
        $slug = TenantContext::slug() ?: 'default';

        return new Channel('chamadas.pacientes.'.$slug);
    }

    public function broadcastAs(): string
    {
        return 'paciente.chamado';
    }

    /**
     * Payload do telão (mínimo necessário na sala). Não logar este array.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->consulta->id,
            'paciente' => [
                'id' => $this->consulta->paciente_id,
                'nome' => $this->consulta->paciente->nome ?? 'N/A',
            ],
            'profissional' => [
                'id' => $this->consulta->user_id,
                'nome' => $this->consulta->user->name ?? 'N/A',
            ],
            'codigo_chegada' => $this->consulta->codigo_chegada ?? 'N/A',
            'horario_chamada' => now()->format('H:i'),
            'data_chamada' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
