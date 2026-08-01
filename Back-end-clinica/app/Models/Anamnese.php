<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Anamnese extends Model
{
    use SoftDeletes;

    protected $table = 'anamneses';

    protected $fillable = [
        'ficha_clinica_id',
        'motivo_consulta',
        'ultimo_controle',
        'antecedentes_pessoais',
        'antecedentes_familiares'
    ];

    protected $casts = [
        'ultimo_controle' => 'date'
    ];

    // Relacionamentos
    public function fichaClinica(): BelongsTo
    {
        return $this->belongsTo(CadastroFichaClinica::class, 'ficha_clinica_id');
    }
}
