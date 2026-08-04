<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConfiguracoesAgendamento extends Model
{
    use SoftDeletes;

    protected $table = 'configuracoes_agendamento';

    protected $fillable = [
        'user_id', 'seg', 'ter', 'qua', 'qui', 'sex', 'sab', 'dom',
        'horario_inicio', 'horario_fim', 'duracao_consulta', 'intervalo_consulta',
        'pausas', 'data_inicio_vigencia', 'data_fim_vigencia', 'padrao'
    ];

    protected $casts = [
        'pausas' => 'array',
        'data_inicio_vigencia' => 'date',
        'data_fim_vigencia' => 'date',
        'padrao' => 'boolean',
        'horario_inicio' => 'datetime:H:i',
        'horario_fim' => 'datetime:H:i',
        'seg' => 'integer',
        'ter' => 'integer',
        'qua' => 'integer',
        'qui' => 'integer',
        'sex' => 'integer',
        'sab' => 'integer',
        'dom' => 'integer',
        'duracao_consulta' => 'integer',
        'intervalo_consulta' => 'integer'
    ];


    // Relacionamentos
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function consultas(): HasMany
    {
        return $this->hasMany(Consulta::class, 'configuracao_id');
    }

    // Métodos auxiliares
    public function isAtiva($data = null): bool
    {
        $data = $data ? Carbon::parse($data) : now();

        return $this->data_inicio_vigencia <= $data &&
               ($this->data_fim_vigencia === null || $this->data_fim_vigencia > $data);
    }

    public function isPadrao(): bool
    {
        return $this->padrao && $this->user_id === null;
    }

    public static function obterConfiguracaoAtiva($userId, $data = null)
    {
        $data = $data ? Carbon::parse($data)->startOfDay() : now()->startOfDay();

        $vigente = function ($query) use ($data) {
            $query->where('data_inicio_vigencia', '<=', $data)
                ->where(function ($q) use ($data) {
                    // Inclui o dia final de vigência (antes era ">" e excluía o último dia)
                    $q->whereNull('data_fim_vigencia')
                        ->orWhereDate('data_fim_vigencia', '>=', $data);
                });
        };

        // 1) Configuração específica do profissional
        if ($userId !== null && $userId !== '') {
            $config = self::query()
                ->where('user_id', $userId)
                ->where($vigente)
                ->orderByDesc('data_inicio_vigencia')
                ->first();

            if ($config) {
                return $config;
            }
        }

        // 2) Clínica: preferir padrao=true; senão qualquer user_id null vigente
        return self::query()
            ->whereNull('user_id')
            ->where($vigente)
            ->orderByDesc('padrao')
            ->orderByDesc('data_inicio_vigencia')
            ->first();
    }

    /**
     * Método para converter os campos de dias da semana em array
     */
    public function getDiasDisponiveisAttribute()
    {
        $dias = [];

        if ($this->dom) $dias[] = 0; // Domingo
        if ($this->seg) $dias[] = 1; // Segunda
        if ($this->ter) $dias[] = 2; // Terça
        if ($this->qua) $dias[] = 3; // Quarta
        if ($this->qui) $dias[] = 4; // Quinta
        if ($this->sex) $dias[] = 5; // Sexta
        if ($this->sab) $dias[] = 6; // Sábado

        return $dias;
    }

    /**
     * Método para definir os dias disponíveis a partir de um array
     */
    public function setDiasDisponiveis($dias)
    {
        // Reset todos os dias para 0
        $this->dom = 0;
        $this->seg = 0;
        $this->ter = 0;
        $this->qua = 0;
        $this->qui = 0;
        $this->sex = 0;
        $this->sab = 0;

        // Define os dias selecionados
        foreach ($dias as $dia) {
            switch ($dia) {
                case 0: $this->dom = 1; break;
                case 1: $this->seg = 1; break;
                case 2: $this->ter = 1; break;
                case 3: $this->qua = 1; break;
                case 4: $this->qui = 1; break;
                case 5: $this->sex = 1; break;
                case 6: $this->sab = 1; break;
            }
        }
    }
}
