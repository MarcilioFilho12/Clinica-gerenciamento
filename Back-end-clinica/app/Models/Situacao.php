<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Situacao extends Model
{
    protected $table = 'situacoes';

    protected $fillable = [
        'nome'
    ];

    // Relacionamento com parceiros
    public function parceiros(): HasMany
    {
        return $this->hasMany(Parceiro::class, 'situacao_id');
    }
}
