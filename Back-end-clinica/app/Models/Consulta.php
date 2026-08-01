<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'chegada_em', 'codigo_chegada'
    ];

    protected $casts = [
        'data' => 'date',
        'horario_inicio' => 'datetime:H:i',
        'horario_fim' => 'datetime:H:i',
        'chegada_em' => 'datetime',
        'pago' => 'boolean',
        'valor' => 'decimal:2',
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
}
