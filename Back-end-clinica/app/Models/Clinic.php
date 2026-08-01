<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    protected $connection = 'central';

    protected $table = 'clinics';

    protected $fillable = [
        'nome',
        'slug',
        'database_name',
        'logo_url',
        'cor_primaria',
        'cor_secundaria',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function branding(): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'slug' => $this->slug,
            'logo_url' => $this->logo_url,
            'cor_primaria' => $this->cor_primaria,
            'cor_secundaria' => $this->cor_secundaria,
        ];
    }
}
