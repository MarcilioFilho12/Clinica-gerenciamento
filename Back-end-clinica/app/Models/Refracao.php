<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Refracao extends Model
{
    use SoftDeletes;

    protected $table = 'refracoes';

    protected $fillable = [
        'ficha_clinica_id',
        'tipo',
        'olho',
        'esf',
        'cil',
        'eixo',
        'add',
        'av'
    ];

    // Relacionamentos
    public function fichaClinica(): BelongsTo
    {
        return $this->belongsTo(CadastroFichaClinica::class, 'ficha_clinica_id');
    }
}
