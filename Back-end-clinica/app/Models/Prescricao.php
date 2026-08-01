<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescricao extends Model
{
    use SoftDeletes;

    protected $table = 'prescricoes';

    protected $fillable = [
        'ficha_clinica_id',
        'material',
        'tipo_lente',
        'filtro',
        'diagnostico',
        'conduta',
        'encaminhamento',
        'proximo_controle'
    ];

    protected $casts = [
        'proximo_controle' => 'date'
    ];

    // Relacionamentos
    public function fichaClinica(): BelongsTo
    {
        return $this->belongsTo(CadastroFichaClinica::class, 'ficha_clinica_id');
    }
}
