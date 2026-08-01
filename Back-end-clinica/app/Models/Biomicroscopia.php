<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Biomicroscopia extends Model
{
    use SoftDeletes;

    protected $table = 'biomicroscopias';

    protected $fillable = [
        'ficha_clinica_id',
        'olho',
        'cornea',
        'iris',
        'conjuntiva',
        'cristalino',
        'pupilas'
    ];

    // Relacionamentos
    public function fichaClinica(): BelongsTo
    {
        return $this->belongsTo(CadastroFichaClinica::class, 'ficha_clinica_id');
    }
}
