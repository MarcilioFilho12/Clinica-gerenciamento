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

    /**
     * Create a new event instance.
     */
    public function __construct($consulta)
    {
        Log::info('[DEBUG] PacienteChamado - construtor chamado', [
            'consulta_id' => $consulta->id ?? 'N/A',
        ]);
        $this->consulta = $consulta;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        $slug = TenantContext::slug() ?: 'default';
        $canal = 'chamadas.pacientes.'.$slug;
        Log::info('[DEBUG] PacienteChamado - broadcastOn() chamado', ['canal' => $canal]);

        return new Channel($canal);
    }

    /**
     * Nome do evento que será ouvido no frontend
     */
    public function broadcastAs(): string
    {
        return 'paciente.chamado';
    }

    /**
     * Dados que serão transmitidos
     */
    public function broadcastWith(): array
    {
        $dados = [
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

        Log::info('[DEBUG] Evento PacienteChamado - dados para broadcast', [
            'canal' => 'chamadas.pacientes.'.(TenantContext::slug() ?: 'default'),
            'evento' => 'paciente.chamado',
            'dados' => $dados,
        ]);

        return $dados;
    }
}

