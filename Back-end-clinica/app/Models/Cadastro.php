<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cadastro extends Model
{
    use SoftDeletes;

    protected $table= 'cadastros';

    protected $fillable=[
        'nome',
        'data_nascimento',
        'sexo',
        'contato',
        'email',
        'ocupacao',
        'cpf',
        'rg',
        'nome_responsavel',
        'cpf_responsavel',
        'observacoes',
        'endereco'
    ];

    protected $casts = [
        'data_nascimento' => 'date'
    ];

    // Relacionamentos
    public function fichasClinicas()
    {
        return $this->hasMany(CadastroFichaClinica::class);
    }
}
