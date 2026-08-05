<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consulta extends Model
{
    use SoftDeletes;
    protected $table = 'consultas';

    protected $fillable = [
        'user_id', 'paciente_id', 'parceiro_id', 'procedimento',
        'data', 'horario_inicio', 'horario_fim', 'prioridade',
        'observacoes', 'pago', 'forma_pagamento', 'valor',
        'situacao_id', 'configuracao_id', 'motivo_cancelamento',
        'chegada_em', 'codigo_chegada',
        // Ciclo de vida (aditivo)
        'status', 'consulta_origem_id', 'profissional_anterior_id', 'profissional_novo_id',
        'motivo_transferencia', 'data_anterior', 'horario_anterior_inicio', 'horario_anterior_fim',
        'motivo_reagendamento', 'cancelado_por_id', 'cancelado_em', 'no_show_em',
    ];

    protected $casts = [
        // Formato explícito evita que o cast 'date' plano dependa do
        // Carbon::__toString() (Y-m-d H:i:s) na hora de persistir — em bancos
        // sem tipo DATE nativo (SQLite/testes) isso gravava datetime completo
        // e quebrava comparações "data = ?". No MySQL o comportamento não muda
        // (a coluna DATE já truncava a hora de qualquer forma).
        'data' => 'date:Y-m-d',
        'horario_inicio' => 'datetime:H:i',
        'horario_fim' => 'datetime:H:i',
        'chegada_em' => 'datetime',
        'pago' => 'boolean',
        'valor' => 'decimal:2',
        'data_anterior' => 'date:Y-m-d',
        'horario_anterior_inicio' => 'datetime:H:i',
        'horario_anterior_fim' => 'datetime:H:i',
        'cancelado_em' => 'datetime',
        'no_show_em' => 'datetime',
    ];

    /**
     * Relacionamento com User (profissional/médico)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relacionamento com Cadastro (paciente)
     */
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Cadastro::class, 'paciente_id');
    }

    /**
     * Relacionamento com Parceiro (convênio)
     */
    public function parceiro(): BelongsTo
    {
        return $this->belongsTo(Parceiro::class, 'parceiro_id');
    }

    public function situacao(): BelongsTo
    {
        return $this->belongsTo(Situacao::class);
    }

    public function configuracao(): BelongsTo
    {
        return $this->belongsTo(ConfiguracoesAgendamento::class, 'configuracao_id');
    }

    /**
     * Relacionamento com Ficha Clínica (opcional)
     */
    public function fichaClinica(): HasOne
    {
        return $this->hasOne(CadastroFichaClinica::class, 'consulta_id');
    }

    /**
     * Histórico de mudanças de status (auditoria completa, imutável).
     */
    public function historico(): HasMany
    {
        return $this->hasMany(ConsultaHistorico::class, 'consulta_id')->latest('created_at');
    }

    /**
     * Consulta de origem (quando esta consulta nasceu de uma transferência).
     */
    public function consultaOrigem(): BelongsTo
    {
        return $this->belongsTo(Consulta::class, 'consulta_origem_id');
    }

    /**
     * Consultas que nasceram desta (transferências para frente).
     */
    public function consultasDerivadas(): HasMany
    {
        return $this->hasMany(Consulta::class, 'consulta_origem_id');
    }

    public function profissionalAnterior(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profissional_anterior_id');
    }

    public function profissionalNovo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profissional_novo_id');
    }

    public function canceladoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelado_por_id');
    }

    // Métodos auxiliares
    public function isPassada(): bool
    {
        return Carbon::parse($this->data)->isPast();
    }

    public function isHoje(): bool
    {
        return Carbon::parse($this->data)->isToday();
    }

    public function isFutura(): bool
    {
        return Carbon::parse($this->data)->isFuture();
    }

    /**
     * Scope para filtrar por data
     */
    public function scopePorData($query, $data)
    {
        return $query->where('data', $data);
    }

    /**
     * Scope para filtrar por profissional
     */
    public function scopePorProfissional($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para filtrar por data e profissional
     */
    public function scopePorDataEProfissional($query, $data, $userId)
    {
        return $query->where('data', $data)->where('user_id', $userId);
    }

    /**
     * Regra oficial de "Consultas Vencidas": PENDENTE (ou VENCIDA já marcada)
     * com data_hora < agora, sem atendimento iniciado. Exclui automaticamente
     * REALIZADA, CANCELADA, TRANSFERIDA, NO_SHOW, REAGENDADA e CONFIRMADA.
     */
    public function scopeVencidas($query)
    {
        return $query
            ->whereIn('status', [\App\Support\ConsultaStatus::PENDENTE, \App\Support\ConsultaStatus::VENCIDA])
            ->antesDoMomento(now());
    }

    /**
     * Compara `data` + `horario_inicio` (colunas separadas) contra um instante,
     * sem depender de funções específicas de banco (ex.: `TIMESTAMP()` do MySQL).
     * Além de portátil (MySQL/SQLite), é sargable — permite usar índice em `data`,
     * diferente de envolver a coluna numa função no WHERE.
     */
    public function scopeAntesDoMomento($query, Carbon $momento, string $colunaData = 'data', string $colunaHorario = 'horario_inicio')
    {
        $data = $momento->format('Y-m-d');
        $hora = $momento->format('H:i:s');

        return $query->where(function ($q) use ($colunaData, $colunaHorario, $data, $hora) {
            $q->where($colunaData, '<', $data)
                ->orWhere(function ($q2) use ($colunaData, $colunaHorario, $data, $hora) {
                    $q2->where($colunaData, '=', $data)
                        ->whereRaw("SUBSTR({$colunaHorario}, -8) < ?", [$hora]);
                });
        });
    }

    /**
     * Igual à anterior, mas para "a partir de" (>=) — usado nos filtros de período.
     */
    public function scopeAPartirDoMomento($query, Carbon $momento, string $colunaData = 'data', string $colunaHorario = 'horario_inicio')
    {
        $data = $momento->format('Y-m-d');
        $hora = $momento->format('H:i:s');

        return $query->where(function ($q) use ($colunaData, $colunaHorario, $data, $hora) {
            $q->where($colunaData, '>', $data)
                ->orWhere(function ($q2) use ($colunaData, $colunaHorario, $data, $hora) {
                    $q2->where($colunaData, '=', $data)
                        ->whereRaw("SUBSTR({$colunaHorario}, -8) >= ?", [$hora]);
                });
        });
    }

    /**
     * Tempo (em minutos) desde a última mudança de status (ou desde a criação, se nunca mudou).
     */
    public function getTempoEmStatusMinutosAttribute(): int
    {
        $referencia = $this->historico()->first()?->created_at ?? $this->created_at;

        return $referencia ? now()->diffInMinutes($referencia) : 0;
    }
}
