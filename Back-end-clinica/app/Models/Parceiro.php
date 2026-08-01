<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Parceiro extends Model
{
    use SoftDeletes;

    protected $table = 'parceiros';

    protected $fillable = [
        'nome',
        'tipo_parceiro_id',
        'situacao_id',
        'cnpj',
        'telefone',
        'email',
        'site',
        'responsavel',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'observacoes'
    ];

    protected $casts = [
        // Adicionar casts se necessário no futuro
    ];

    // Relacionamentos
    public function tipoParceiro(): BelongsTo
    {
        return $this->belongsTo(TipoParceiro::class, 'tipo_parceiro_id');
    }

    public function situacao(): BelongsTo
    {
        return $this->belongsTo(Situacao::class, 'situacao_id');
    }
}
