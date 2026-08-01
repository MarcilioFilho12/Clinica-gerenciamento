<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Despesa extends Model
{
    use SoftDeletes;

    protected $table = 'despesas';

    protected $fillable = [
        'descricao',
        'valor',
        'data',
        'categoria',
        'forma_pagamento',
        'observacoes',
    ];

    protected $casts = [
        'data' => 'date',
        'valor' => 'decimal:2',
    ];
}
