<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Histórico imutável de mudanças de status de uma consulta (auditoria).
 * Não possui updated_at: histórico nunca é editado, só inserido.
 */
class ConsultaHistorico extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'consulta_historico';

    protected $fillable = [
        'consulta_id', 'status_anterior', 'status_novo', 'usuario_id',
        'acao', 'motivo', 'observacao', 'ip', 'user_agent',
    ];

    public function consulta(): BelongsTo
    {
        return $this->belongsTo(Consulta::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
