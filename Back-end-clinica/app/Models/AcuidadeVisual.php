<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcuidadeVisual extends Model
{
    use SoftDeletes;

    protected $table = 'acuidades_visuais';

    protected $fillable = [
        'ficha_clinica_id',
        'olho',
        'vl',
        'vp',
        'ph',
        'observacoes'
    ];

    // Relacionamentos
    public function fichaClinica(): BelongsTo
    {
        return $this->belongsTo(CadastroFichaClinica::class, 'ficha_clinica_id');
    }
}
