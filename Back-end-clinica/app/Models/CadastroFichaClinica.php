<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class CadastroFichaClinica extends Model
{
    use SoftDeletes;

    protected $table = 'cadastros_fichas_clinicas';

    protected $fillable = [
        'cadastro_id',
        'user_id',
        'consulta_id',
        'data_consulta',
        'observacoes'
    ];

    protected $casts = [
        'data_consulta' => 'date'
    ];

    /**
     * Relacionamento com Cadastro (paciente)
     */
    public function cadastro(): BelongsTo
    {
        return $this->belongsTo(Cadastro::class);
    }

    /**
     * Relacionamento com User (profissional que atendeu)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com Consulta (opcional)
     */
    public function consulta(): BelongsTo
    {
        return $this->belongsTo(Consulta::class, 'consulta_id');
    }

    /**
     * Relacionamento com Anamnese
     */
    public function anamnese(): HasOne
    {
        return $this->hasOne(Anamnese::class, 'ficha_clinica_id');
    }

    /**
     * Relacionamento com Acuidades Visuais
     */
    public function acuidadesVisuais(): HasMany
    {
        return $this->hasMany(AcuidadeVisual::class, 'ficha_clinica_id');
    }

    /**
     * Relacionamento com Refrações
     */
    public function refracoes(): HasMany
    {
        return $this->hasMany(Refracao::class, 'ficha_clinica_id');
    }

    /**
     * Relacionamento com Biomicroscopias
     */
    public function biomicroscopias(): HasMany
    {
        return $this->hasMany(Biomicroscopia::class, 'ficha_clinica_id');
    }

    /**
     * Relacionamento com Prescrição
     */
    public function prescricao(): HasOne
    {
        return $this->hasOne(Prescricao::class, 'ficha_clinica_id');
    }
}

