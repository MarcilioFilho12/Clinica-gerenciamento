<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoParceiro extends Model
{
    protected $table = 'tipos_parceiros';

    protected $fillable = [
        'nome'
    ];

    // Relacionamento com parceiros
    public function parceiros(): HasMany
    {
        return $this->hasMany(Parceiro::class, 'tipo_parceiro_id');
    }
}
