<?php
// app/Http/Controllers/ConsultaController.php

namespace App\Http\Controllers;

use App\Models\Consulta;
use App\Models\ConfiguracoesAgendamento;
use App\Models\User;
use App\Events\PacienteChamado;
use App\Services\ConsultaStatusService;
use App\Support\ConsultaStatus;
use App\Support\Profiles;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ConsultaController extends Controller
{
    public function __construct(private readonly ConsultaStatusService $consultaStatusService)
    {
    }

    /**
     * Retorna a agenda completa com profissionais e horários disponíveis
     * Compatível com a estrutura esperada pelo frontend
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Data padrão é hoje se não especificada
            $data = $request->input('data', now()->format('Y-m-d'));

            // Buscar profissionais ativos (profile_id = 3 e situacao_id = 1)
            $profissionais = User::with(['profile', 'situacao'])
                ->where('profile_id', 3)
                ->where('situacao_id', 1)
                ->orderBy('name')
                ->get();

            // Configuração padrão ativa na data (independente de haver profissionais)
            $configuracaoPadrao = ConfiguracoesAgendamento::obterConfiguracaoAtiva(null, $data);
            $configuracaoPayload = $configuracaoPadrao ? [
                'horario_inicio' => $configuracaoPadrao->horario_inicio->format('H:i'),
                'horario_fim' => $configuracaoPadrao->horario_fim->format('H:i'),
                'duracao_consulta' => $configuracaoPadrao->duracao_consulta,
                'intervalo_consulta' => $configuracaoPadrao->intervalo_consulta,
                'dia_funcionamento' => true,
            ] : null;

            if ($profissionais->isEmpty()) {
                $diaSemana = Carbon::parse($data)->dayOfWeek;
                $dias = ['dom', 'seg', 'ter', 'qua', 'qui', 'sex', 'sab'];
                $campoDia = $dias[$diaSemana];
                if ($configuracaoPayload && $configuracaoPadrao) {
                    $configuracaoPayload['dia_funcionamento'] = (bool) $configuracaoPadrao->$campoDia;
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Nenhum profissional encontrado',
                    'data' => [
                        'data' => $data,
                        'profissionais' => [],
                        'configuracao' => $configuracaoPayload,
                        'tem_configuracao' => $configuracaoPadrao !== null
                            || ConfiguracoesAgendamento::query()->exists(),
                    ],
                ], 200);
            }

            $diaSemana = Carbon::parse($data)->dayOfWeek;
            $dias = ['dom', 'seg', 'ter', 'qua', 'qui', 'sex', 'sab'];
            $campoDia = $dias[$diaSemana];

            // Para cada profissional, verificar se trabalha naquele dia e buscar consultas/horários
            $profissionaisComHorarios = $profissionais->map(function ($profissional) use ($data, $campoDia) {
                // Obter configuração ativa para este profissional nesta data
                $configuracao = ConfiguracoesAgendamento::obterConfiguracaoAtiva($profissional->id, $data);

                // Verificar se este profissional trabalha naquele dia
                $trabalhaNoDia = $configuracao && $configuracao->$campoDia;

                // Se não trabalha, retornar null (será filtrado depois)
                if (!$trabalhaNoDia || !$configuracao) {
                    return null;
                }

                // Buscar consultas já agendadas para este profissional nesta data
                // Excluir apenas consultas canceladas (5)
                // Consultas encerradas (4) e em atendimento (6) devem aparecer na agenda
                $consultasAgendadas = Consulta::with(['paciente', 'parceiro', 'situacao'])
                    ->where('user_id', $profissional->id)
                    ->where('data', $data)
                    ->whereNotIn('situacao_id', [5]) // Excluir apenas: 5 = Cancelada
                    ->orderBy('horario_inicio')
                    ->get();

                // Gerar horários disponíveis
                $horariosDisponiveis = $this->gerarHorariosDisponiveis($configuracao, $data, $profissional->id);

                return [
                    'id' => $profissional->id,
                    'name' => $profissional->name,
                    'email' => $profissional->email,
                    'especialidade' => $profissional->especialidade,
                    'crm' => $profissional->crm,
                    'profile' => $profissional->profile,
                    'situacao' => $profissional->situacao,
                    'consultas_agendadas' => $consultasAgendadas,
                    'horarios_disponiveis' => $horariosDisponiveis
                ];
            })->filter()->values(); // Remove nulls e reindexa array

            // Se nenhum profissional trabalha naquele dia, mostrar aviso
            if ($profissionaisComHorarios->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Clínica não funciona neste dia',
                    'data' => [
                        'data' => $data,
                        'profissionais' => [],
                        'configuracao' => $configuracaoPadrao ? [
                            'horario_inicio' => $configuracaoPadrao->horario_inicio->format('H:i'),
                            'horario_fim' => $configuracaoPadrao->horario_fim->format('H:i'),
                            'duracao_consulta' => $configuracaoPadrao->duracao_consulta,
                            'intervalo_consulta' => $configuracaoPadrao->intervalo_consulta,
                            'dia_funcionamento' => false,
                        ] : null,
                        'tem_configuracao' => $configuracaoPadrao !== null
                            || ConfiguracoesAgendamento::query()->exists(),
                    ],
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Agenda listada com sucesso',
                'data' => [
                    'data' => $data,
                    'profissionais' => $profissionaisComHorarios,
                    'configuracao' => $configuracaoPayload
                        ? array_merge($configuracaoPayload, ['dia_funcionamento' => true])
                        : null,
                    'tem_configuracao' => $configuracaoPadrao !== null
                        || ConfiguracoesAgendamento::query()->exists(),
                ],
            ], 200);

        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
            ], 500);
        }
    }

    /**
     * Lista profissionais ativos (para selects clínicos / histórico).
     */
    public function profissionais(): JsonResponse
    {
        $profissionais = User::query()
            ->where('profile_id', 3)
            ->where('situacao_id', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'especialidade', 'crm', 'email']);

        return response()->json([
            'success' => true,
            'message' => 'Profissionais listados com sucesso',
            'data' => $profissionais,
        ]);
    }

    /**
     * Lista horários disponíveis para um profissional em uma data específica
     */
    public function horariosDisponiveis(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'data' => 'required|date',
            ]);

            if ($erro = $this->motivoProfissionalInvalido((int) $request->user_id)) {
                return response()->json([
                    'success' => false,
                    'message' => $erro,
                ], 422);
            }

            $configuracao = ConfiguracoesAgendamento::obterConfiguracaoAtiva(
                $request->user_id,
                $request->data
            );

            if (!$configuracao) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma configuração de agendamento encontrada para esta data'
                ], 404);
            }

            $horariosDisponiveis = $this->gerarHorariosDisponiveis(
                $configuracao,
                $request->data,
                $request->user_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Horários disponíveis listados com sucesso',
                'data' => [
                    'data' => $request->data,
                    'user_id' => $request->user_id,
                    'horarios_disponiveis' => $horariosDisponiveis,
                    'configuracao' => [
                        'horario_inicio' => $configuracao->horario_inicio->format('H:i'),
                        'horario_fim' => $configuracao->horario_fim->format('H:i'),
                        'duracao_consulta' => $configuracao->duracao_consulta,
                        'intervalo_consulta' => $configuracao->intervalo_consulta
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $historico = $request->boolean('historico');
            $this->normalizarHorariosRequest($request);

            $request->validate([
                'user_id' => 'required|exists:users,id',
                'paciente_id' => ($historico ? 'required' : 'nullable').'|exists:cadastros,id',
                'procedimento' => 'required|string|max:255',
                'data' => $historico
                    ? 'required|date|before_or_equal:today'
                    : 'required|date|after_or_equal:today',
                'horario_inicio' => 'required|date_format:H:i',
                'horario_fim' => 'required|date_format:H:i|after:horario_inicio',
                'prioridade' => 'required|in:normal,alta,baixa',
                'parceiro_id' => 'nullable|exists:parceiros,id',
                'observacoes' => 'nullable|string',
                'pago' => 'sometimes|boolean',
                'forma_pagamento' => 'nullable|required_if:pago,true,1|in:dinheiro,pix,cartao_credito,cartao_debito,convenio,transferencia,outro',
                'valor' => 'nullable|numeric|min:0',
                'historico' => 'sometimes|boolean',
            ]);

            $configuracaoId = null;

            try {
                $consulta = DB::transaction(function () use ($request, $historico, &$configuracaoId) {
                    if ($erroProf = $this->motivoProfissionalInvalido((int) $request->user_id)) {
                        throw new RuntimeException('PROF_INVALIDO|'.$erroProf);
                    }

                    if (! $historico) {
                        $configuracao = ConfiguracoesAgendamento::obterConfiguracaoAtiva(
                            $request->user_id,
                            $request->data
                        );

                        if (! $configuracao) {
                            throw new RuntimeException('SEM_CONFIG');
                        }

                        $motivoHorario = $this->motivoHorarioInvalido(
                            (string) $request->data,
                            (string) $request->horario_inicio,
                            (string) $request->horario_fim,
                            $configuracao
                        );
                        if ($motivoHorario) {
                            throw new RuntimeException('HORARIO_INVALIDO|'.$motivoHorario);
                        }

                        if ($this->verificarConflitoHorario(
                            $request->user_id,
                            $request->data,
                            $request->horario_inicio,
                            $request->horario_fim,
                            null,
                            true
                        )) {
                            throw new RuntimeException('HORARIO_OCUPADO');
                        }

                        $configuracaoId = $configuracao->id;
                    } else {
                        $configuracao = ConfiguracoesAgendamento::obterConfiguracaoAtiva(
                            $request->user_id,
                            $request->data
                        );
                        $configuracaoId = $configuracao?->id;
                    }

                    return Consulta::create([
                        'user_id' => $request->user_id,
                        'paciente_id' => $request->paciente_id ?: null,
                        'procedimento' => $request->procedimento,
                        'data' => $request->data,
                        'horario_inicio' => $request->horario_inicio,
                        'horario_fim' => $request->horario_fim,
                        'prioridade' => $request->prioridade ?? 'normal',
                        'parceiro_id' => $request->parceiro_id,
                        'observacoes' => $request->observacoes,
                        'pago' => (bool) $request->boolean('pago'),
                        'forma_pagamento' => $request->boolean('pago') ? $request->forma_pagamento : null,
                        'valor' => $request->valor,
                        'situacao_id' => $historico ? 4 : 1,
                        'status' => $historico ? ConsultaStatus::REALIZADA : ConsultaStatus::PENDENTE,
                        'configuracao_id' => $configuracaoId,
                    ]);
                });
            } catch (RuntimeException $e) {
                return $this->respostaExcecaoAgendamento($e);
            }

            return response()->json([
                'success' => true,
                'message' => $historico
                    ? 'Consulta histórica cadastrada com sucesso!'
                    : 'Consulta agendada com sucesso!',
                'data' => $consulta->load('paciente', 'parceiro', 'situacao', 'configuracao'),
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
            ], 500);
        }
    }

    private function validarHorario(Request $request, $configuracao)
    {
        return $this->motivoHorarioInvalido(
            (string) $request->data,
            (string) ($request->horario_inicio ?? ''),
            (string) ($request->horario_fim ?? ''),
            $configuracao
        ) === null;
    }

    /**
     * Aceita H:i ou H:i:s (alguns browsers mobile enviam segundos).
     */
    private function normalizarHorariosRequest(Request $request): void
    {
        $merge = [];
        foreach (['horario_inicio', 'horario_fim'] as $campo) {
            $valor = $request->input($campo);
            if (! is_string($valor) || $valor === '') {
                continue;
            }
            if (preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?$/', trim($valor), $m)) {
                $merge[$campo] = sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
            }
        }
        if ($merge !== []) {
            $request->merge($merge);
        }
    }

    /**
     * Compara só o relógio (H:i), sem instante/timezone do cast datetime:H:i.
     * Cast de TIME como datetime misturava datas/fusos e gerava 400 falso em slots válidos.
     */
    private function parseHorarioHm(string $horario): ?Carbon
    {
        $hm = substr(trim($horario), 0, 5);
        if (! preg_match('/^\d{2}:\d{2}$/', $hm)) {
            if (preg_match('/^(\d{1,2}):(\d{2})/', $hm, $m)) {
                $hm = sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
            } else {
                return null;
            }
        }

        try {
            return Carbon::createFromFormat('H:i', $hm);
        } catch (\Throwable) {
            return null;
        }
    }

    private function horarioModeloHm(mixed $value): string
    {
        if ($value instanceof Carbon) {
            return $value->format('H:i');
        }
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('H:i');
        }

        return substr(trim((string) $value), 0, 5);
    }

    /**
     * null = horário aceito (qualquer HH:mm dentro do expediente; não precisa bater na grade).
     */
    private function motivoHorarioInvalido(string $data, string $horarioInicio, string $horarioFim, $configuracao): ?string
    {
        if ($horarioInicio === '' || $horarioFim === '') {
            return 'Informe horário de início e fim.';
        }

        $dataCarbon = Carbon::parse($data)->timezone(config('app.timezone'));
        $diaSemana = strtolower($dataCarbon->format('l'));

        $diasMap = [
            'monday' => 'seg',
            'tuesday' => 'ter',
            'wednesday' => 'qua',
            'thursday' => 'qui',
            'friday' => 'sex',
            'saturday' => 'sab',
            'sunday' => 'dom',
        ];

        $diaConfig = $diasMap[$diaSemana] ?? null;
        if (! $diaConfig || ! $configuracao->$diaConfig) {
            return 'O profissional não atende neste dia da semana. Ajuste a configuração de agendamentos ou escolha outro dia.';
        }

        $inicio = $this->parseHorarioHm($horarioInicio);
        $fim = $this->parseHorarioHm($horarioFim);
        $configInicio = $this->parseHorarioHm($this->horarioModeloHm($configuracao->horario_inicio));
        $configFim = $this->parseHorarioHm($this->horarioModeloHm($configuracao->horario_fim));

        if (! $inicio || ! $fim || ! $configInicio || ! $configFim) {
            return 'Horário em formato inválido. Use HH:mm.';
        }

        if ($fim->lte($inicio)) {
            return 'O horário de fim deve ser depois do início.';
        }

        if ($inicio->lt($configInicio) || $fim->gt($configFim)) {
            return sprintf(
                'Horário fora do expediente (%s–%s). A recepção pode marcar qualquer horário dentro dessa janela, inclusive fora da grade de slots.',
                $configInicio->format('H:i'),
                $configFim->format('H:i')
            );
        }

        foreach ($configuracao->pausas ?? [] as $pausa) {
            $pausaInicio = $this->parseHorarioHm((string) ($pausa['inicio'] ?? ''));
            $pausaFim = $this->parseHorarioHm((string) ($pausa['fim'] ?? ''));
            if (! $pausaInicio || ! $pausaFim) {
                continue;
            }

            if ($inicio->lt($pausaFim) && $fim->gt($pausaInicio)) {
                return sprintf(
                    'Horário conflita com pausa configurada (%s–%s).',
                    $pausaInicio->format('H:i'),
                    $pausaFim->format('H:i')
                );
            }
        }

        return null;
    }

    private function motivoProfissionalInvalido(int $userId): ?string
    {
        $user = User::query()->find($userId);
        if (! $user) {
            return 'Profissional não encontrado.';
        }
        if ((int) $user->profile_id !== Profiles::PROFISSIONAL) {
            return 'Selecione um usuário com perfil Profissional para a agenda.';
        }
        if ((int) $user->situacao_id !== 1) {
            return 'Este profissional está inativo e não pode receber agendamentos.';
        }

        return null;
    }

    private function respostaExcecaoAgendamento(RuntimeException $e): JsonResponse
    {
        $msg = $e->getMessage();

        if (str_starts_with($msg, 'HORARIO_INVALIDO|')) {
            return response()->json([
                'success' => false,
                'message' => substr($msg, strlen('HORARIO_INVALIDO|')),
            ], 400);
        }

        if (str_starts_with($msg, 'PROF_INVALIDO|')) {
            return response()->json([
                'success' => false,
                'message' => substr($msg, strlen('PROF_INVALIDO|')),
            ], 422);
        }

        return match ($msg) {
            'SEM_CONFIG' => response()->json([
                'success' => false,
                'message' => 'Nenhuma configuração de agendamento encontrada para este profissional nesta data. Cadastre em Configurações → Agendamentos.',
            ], 400),
            'HORARIO_INVALIDO' => response()->json([
                'success' => false,
                'message' => 'Horário não disponível conforme configuração de agendamento.',
            ], 400),
            'HORARIO_OCUPADO' => response()->json([
                'success' => false,
                'message' => 'Horário já ocupado para este profissional. Escolha outro horário ou use um encaixe livre em horário vago.',
            ], 422),
            default => throw $e,
        };
    }

    public function show($id): JsonResponse
    {
        try {
            $consulta = Consulta::with(['user', 'paciente', 'parceiro', 'situacao', 'configuracao'])
                               ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $consulta
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Consulta não encontrada',
            ], 404);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $consulta = Consulta::findOrFail($id);
            $this->normalizarHorariosRequest($request);

            $request->validate([
                'user_id' => 'sometimes|required|exists:users,id',
                'paciente_id' => 'nullable|exists:cadastros,id',
                'procedimento' => 'sometimes|required|string|max:255',
                'data' => 'sometimes|required|date',
                'horario_inicio' => 'sometimes|required|date_format:H:i',
                'horario_fim' => 'sometimes|required|date_format:H:i|after:horario_inicio',
                'prioridade' => 'sometimes|required|in:normal,alta,baixa',
                'parceiro_id' => 'nullable|exists:parceiros,id',
                'observacoes' => 'nullable|string',
                'pago' => 'sometimes|boolean',
                'forma_pagamento' => 'nullable|in:dinheiro,pix,cartao_credito,cartao_debito,convenio,transferencia,outro',
                'valor' => 'nullable|numeric|min:0',
                'situacao_id' => 'sometimes|required|exists:situacoes,id',
                'motivo_cancelamento' => 'nullable|string',
            ]);

            if ($request->has('pago') && $request->boolean('pago') && ! $request->filled('forma_pagamento') && ! $consulta->forma_pagamento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Informe a forma de pagamento quando a consulta estiver paga.',
                ], 422);
            }

            $userId = $request->user_id ?? $consulta->user_id;
            $data = $request->data ?? $consulta->data;

            // Verificar prioridade (pode vir no request ou já estar na consulta)
            $prioridade = $request->prioridade ?? $consulta->prioridade;

            // Verificar se realmente está alterando data/horário/profissional (não apenas se o campo está presente)
            $dataAlterada = $request->has('data') && $request->data !== $consulta->data->format('Y-m-d');
            $horarioInicioAlterado = $request->has('horario_inicio') && $request->horario_inicio !== $consulta->horario_inicio->format('H:i');
            $horarioFimAlterado = $request->has('horario_fim') && $request->horario_fim !== $consulta->horario_fim->format('H:i');
            $profissionalAlterado = $request->has('user_id') && $request->user_id != $consulta->user_id;

            $camposHorarioAlterados = $dataAlterada || $horarioInicioAlterado || $horarioFimAlterado || $profissionalAlterado;

            try {
                $consulta = DB::transaction(function () use (
                    $request,
                    $consulta,
                    $userId,
                    $data,
                    $prioridade,
                    $camposHorarioAlterados,
                    $id
                ) {
                    // Lock da linha atual evita corrida em update concorrente
                    $consulta = Consulta::whereKey($consulta->id)->lockForUpdate()->firstOrFail();

                    if ($camposHorarioAlterados && $prioridade !== 'alta') {
                        if ($erroProf = $this->motivoProfissionalInvalido((int) $userId)) {
                            throw new RuntimeException('PROF_INVALIDO|'.$erroProf);
                        }

                        $configuracao = ConfiguracoesAgendamento::obterConfiguracaoAtiva($userId, $data);

                        if (! $configuracao) {
                            throw new RuntimeException('SEM_CONFIG');
                        }

                        $inicio = (string) ($request->horario_inicio ?? $consulta->horario_inicio->format('H:i'));
                        $fim = (string) ($request->horario_fim ?? $consulta->horario_fim->format('H:i'));
                        $dataStr = is_string($data) ? $data : Carbon::parse($data)->format('Y-m-d');

                        $motivoHorario = $this->motivoHorarioInvalido($dataStr, $inicio, $fim, $configuracao);
                        if ($motivoHorario) {
                            throw new RuntimeException('HORARIO_INVALIDO|'.$motivoHorario);
                        }

                        if ($this->verificarConflitoHorario(
                            $userId,
                            $data,
                            $inicio,
                            $fim,
                            $id,
                            true
                        )) {
                            throw new RuntimeException('HORARIO_OCUPADO');
                        }

                        $request->merge(['configuracao_id' => $configuracao->id]);
                    } elseif ($prioridade === 'alta') {
                        if ($request->has('data') || $request->has('user_id')) {
                            $configuracao = ConfiguracoesAgendamento::obterConfiguracaoAtiva($userId, $data);
                            if ($configuracao) {
                                $request->merge(['configuracao_id' => $configuracao->id]);
                            }
                        }
                    }

                    $dados = $request->only([
                        'user_id', 'paciente_id', 'procedimento', 'data',
                        'horario_inicio', 'horario_fim', 'prioridade', 'parceiro_id',
                        'observacoes', 'pago', 'forma_pagamento', 'valor',
                        'situacao_id', 'motivo_cancelamento', 'configuracao_id',
                    ]);

                    if (array_key_exists('paciente_id', $dados) && ($dados['paciente_id'] === '' || $dados['paciente_id'] === null)) {
                        $dados['paciente_id'] = null;
                    }

                    if (array_key_exists('pago', $dados)) {
                        $dados['pago'] = (bool) $request->boolean('pago');
                        if (! $dados['pago']) {
                            $dados['forma_pagamento'] = null;
                        }
                    }

                    $consulta->update($dados);

                    return $consulta;
                });
            } catch (RuntimeException $e) {
                return $this->respostaExcecaoAgendamento($e);
            }

            return response()->json([
                'success' => true,
                'message' => 'Consulta atualizada com sucesso!',
                'data' => $consulta->load('paciente', 'parceiro', 'situacao', 'configuracao')
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
            ], 500);
        }
    }

    /**
     * Agenda por período (semana/mês) — lista consultas sem grade de horários do dia.
     */
    public function agendaPeriodo(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'data_inicio' => 'required|date',
                'data_fim' => 'required|date|after_or_equal:data_inicio',
                'user_id' => 'nullable|exists:users,id',
            ]);

            $query = Consulta::with(['paciente', 'parceiro', 'situacao', 'user'])
                ->whereBetween('data', [$request->data_inicio, $request->data_fim])
                ->whereNotIn('situacao_id', [5])
                ->orderBy('data')
                ->orderBy('horario_inicio');

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            $consultas = $query->get();

            $porDia = $consultas->groupBy(function ($consulta) {
                return $consulta->data->format('Y-m-d');
            })->map(function ($itens, $dia) {
                return [
                    'data' => $dia,
                    'total' => $itens->count(),
                    'consultas' => $itens->values(),
                ];
            })->values();

            return response()->json([
                'success' => true,
                'message' => 'Agenda do período listada com sucesso',
                'data' => [
                    'data_inicio' => $request->data_inicio,
                    'data_fim' => $request->data_fim,
                    'total' => $consultas->count(),
                    'dias' => $porDia,
                    'consultas' => $consultas,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar agenda do período',
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $consulta = Consulta::findOrFail($id);

            // Soft delete
            $consulta->delete();

            return response()->json([
                'success' => true,
                'message' => 'Consulta excluída com sucesso!'
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir consulta',
            ], 500);
        }
    }

    public function cancelar(Request $request, $id): JsonResponse
    {
        try {
            $consulta = Consulta::findOrFail($id);

            $request->validate([
                'motivo_cancelamento' => 'required|string|max:500',
            ]);

            try {
                $consulta = $this->consultaStatusService->transicionar(
                    $consulta,
                    ConsultaStatus::CANCELADA,
                    $request,
                    acao: 'cancelar',
                    motivo: $request->motivo_cancelamento,
                    extra: [
                        'motivo_cancelamento' => $request->motivo_cancelamento,
                        'cancelado_por_id' => $request->user()?->id,
                        'cancelado_em' => now(),
                    ],
                );
            } catch (RuntimeException $e) {
                return $this->respostaExcecaoStatus($e);
            }

            return response()->json([
                'success' => true,
                'message' => 'Consulta cancelada com sucesso!',
                'data' => $consulta->load('paciente', 'parceiro', 'situacao')
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar consulta',
            ], 500);
        }
    }

    public function confirmar(Request $request, $id): JsonResponse
    {
        try {
            $consulta = Consulta::findOrFail($id);

            try {
                $consulta = $this->consultaStatusService->transicionar(
                    $consulta,
                    ConsultaStatus::CONFIRMADA,
                    $request,
                    acao: 'confirmar',
                );
            } catch (RuntimeException $e) {
                return $this->respostaExcecaoStatus($e);
            }

            return response()->json([
                'success' => true,
                'message' => 'Consulta confirmada com sucesso!',
                'data' => $consulta->load('paciente', 'parceiro', 'situacao')
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao confirmar consulta',
            ], 500);
        }
    }

    public function finalizar(Request $request, $id): JsonResponse
    {
        try {
            $consulta = Consulta::findOrFail($id);

            $request->validate([
                'observacoes' => 'nullable|string',
            ]);

            try {
                $consulta = $this->consultaStatusService->transicionar(
                    $consulta,
                    ConsultaStatus::REALIZADA,
                    $request,
                    acao: 'finalizar',
                    extra: [
                        'observacoes' => $request->observacoes ?? $consulta->observacoes,
                    ],
                );
            } catch (RuntimeException $e) {
                return $this->respostaExcecaoStatus($e);
            }

            return response()->json([
                'success' => true,
                'message' => 'Consulta finalizada com sucesso!',
                'data' => $consulta->load('paciente', 'parceiro', 'situacao')
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao finalizar consulta',
            ], 500);
        }
    }

    private function respostaExcecaoStatus(RuntimeException $e): JsonResponse
    {
        $msg = $e->getMessage();

        if (str_starts_with($msg, 'TRANSICAO_INVALIDA|')) {
            return response()->json([
                'success' => false,
                'message' => substr($msg, strlen('TRANSICAO_INVALIDA|')),
            ], 422);
        }

        if ($msg === 'STATUS_INVALIDO') {
            return response()->json([
                'success' => false,
                'message' => 'Status informado é inválido.',
            ], 422);
        }

        throw $e;
    }

    /**
     * Gera lista de horários disponíveis baseado na configuração
     * Agora retorna TODOS os horários (ocupados e disponíveis)
     */
    private function gerarHorariosDisponiveis($configuracao, $data, $userId): array
    {
        $horariosDisponiveis = [];

        if (!$configuracao) {
            return $horariosDisponiveis;
        }

        // Verificar se o dia da semana está disponível
        if (!$this->validarDiaSemana($configuracao, $data)) {
            return $horariosDisponiveis;
        }

        $inicio = Carbon::createFromFormat('H:i', $configuracao->horario_inicio->format('H:i'));
        $fim = Carbon::createFromFormat('H:i', $configuracao->horario_fim->format('H:i'));
        $duracao = $configuracao->duracao_consulta; // em minutos
        $intervalo = $configuracao->intervalo_consulta; // em minutos

        // Buscar consultas já agendadas para este profissional nesta data
        // Excluir apenas consultas canceladas (5)
        // Consultas encerradas (4) e em atendimento (6) devem aparecer na agenda
        $consultasAgendadas = Consulta::with('paciente')
            ->where('user_id', $userId)
            ->where('data', $data)
            ->whereNotIn('situacao_id', [5]) // Excluir apenas: 5 = Cancelada
            ->orderBy('horario_inicio')
            ->get();

        $horarioAtual = $inicio->copy();

        while ($horarioAtual->copy()->addMinutes($duracao)->lte($fim)) {
            $horarioFim = $horarioAtual->copy()->addMinutes($duracao);

            // Verificar se este horário está ocupado
            $consultaNesteHorario = $this->buscarConsultaNoHorario($horarioAtual, $horarioFim, $consultasAgendadas);

            // Verificar se está disponível (não conflita com consultas nem pausas)
            $disponivel = !$consultaNesteHorario && $this->horarioEstaDisponivel($horarioAtual, $horarioFim, $consultasAgendadas, $configuracao);

            $horariosDisponiveis[] = [
                'horario_inicio' => $horarioAtual->format('H:i'),
                'horario_fim' => $horarioFim->format('H:i'),
                'disponivel' => $disponivel,
                'ocupado' => (bool) $consultaNesteHorario,
                'consulta' => $consultaNesteHorario ? [
                    'id' => $consultaNesteHorario->id,
                    'paciente_id' => $consultaNesteHorario->paciente_id,
                    'paciente' => $consultaNesteHorario->paciente ? [
                        'id' => $consultaNesteHorario->paciente->id,
                        'nome' => $consultaNesteHorario->paciente->nome,
                        'contato' => $consultaNesteHorario->paciente->contato ?? '',
                        'data_nascimento' => $consultaNesteHorario->paciente->data_nascimento
                            ? $consultaNesteHorario->paciente->data_nascimento->format('Y-m-d')
                            : null,
                    ] : null,
                    'situacao_id' => $consultaNesteHorario->situacao_id,
                    'observacoes' => $consultaNesteHorario->observacoes,
                    'prioridade' => $consultaNesteHorario->prioridade ?? 'normal',
                    'horario_inicio' => $consultaNesteHorario->horario_inicio ? $consultaNesteHorario->horario_inicio->format('H:i') : null,
                    'horario_fim' => $consultaNesteHorario->horario_fim ? $consultaNesteHorario->horario_fim->format('H:i') : null,
                    'chegada_em' => $consultaNesteHorario->chegada_em ? $consultaNesteHorario->chegada_em->format('Y-m-d H:i:s') : null,
                    'codigo_chegada' => $consultaNesteHorario->codigo_chegada ?? null
                ] : null
            ];

            // Pular para o próximo horário considerando intervalo
            $horarioAtual->addMinutes($duracao + $intervalo);
        }

        // Adicionar consultas de prioridade alta que não correspondem aos slots padrão
        // Essas são consultas "enfiadas" pela fila de espera
        $consultasPrioridadeAlta = $consultasAgendadas->filter(function ($consulta) use ($horariosDisponiveis) {
            // Verificar se a consulta é de prioridade alta
            if (($consulta->prioridade ?? 'normal') !== 'alta') {
                return false;
            }

            // Verificar se a consulta já está incluída nos horários padrão
            $horarioInicioConsulta = $consulta->horario_inicio ? $consulta->horario_inicio->format('H:i') : null;
            if (!$horarioInicioConsulta) {
                return false;
            }

            // Verificar se já existe um slot padrão que contém esta consulta
            $jaIncluida = false;
            foreach ($horariosDisponiveis as $horario) {
                if ($horario['ocupado'] && $horario['consulta'] && $horario['consulta']['id'] === $consulta->id) {
                    $jaIncluida = true;
                    break;
                }
            }

            return !$jaIncluida;
        });

        // Adicionar essas consultas aos horários disponíveis
        foreach ($consultasPrioridadeAlta as $consulta) {
            $horarioInicio = $consulta->horario_inicio ? $consulta->horario_inicio->format('H:i') : null;
            $horarioFim = $consulta->horario_fim ? $consulta->horario_fim->format('H:i') : null;

            if ($horarioInicio && $horarioFim) {
                $horariosDisponiveis[] = [
                    'horario_inicio' => $horarioInicio,
                    'horario_fim' => $horarioFim,
                    'disponivel' => false,
                    'ocupado' => true,
                    'consulta' => [
                        'id' => $consulta->id,
                        'paciente_id' => $consulta->paciente_id,
                        'paciente' => $consulta->paciente ? [
                            'id' => $consulta->paciente->id,
                            'nome' => $consulta->paciente->nome,
                            'contato' => $consulta->paciente->contato ?? '',
                            'data_nascimento' => $consulta->paciente->data_nascimento
                                ? $consulta->paciente->data_nascimento->format('Y-m-d')
                                : null,
                        ] : null,
                        'situacao_id' => $consulta->situacao_id,
                        'observacoes' => $consulta->observacoes,
                        'prioridade' => $consulta->prioridade ?? 'normal',
                        'horario_inicio' => $horarioInicio,
                        'horario_fim' => $horarioFim,
                        'chegada_em' => $consulta->chegada_em ? $consulta->chegada_em->format('Y-m-d H:i:s') : null,
                        'codigo_chegada' => $consulta->codigo_chegada ?? null
                    ],
                    'is_emergencial' => true // Flag para identificar consultas emergenciais
                ];
            }
        }

        // Ordenar todos os horários por horario_inicio
        usort($horariosDisponiveis, function ($a, $b) {
            return strcmp($a['horario_inicio'], $b['horario_inicio']);
        });

        return $horariosDisponiveis;
    }

    /**
     * Busca consulta que está ocupando um horário específico
     * ⚠️ IMPORTANTE: Consultas de prioridade alta (urgência) ocupam slots normais APENAS se o horário de início for exatamente o mesmo
     */
    private function buscarConsultaNoHorario($inicio, $fim, $consultasAgendadas)
    {
        foreach ($consultasAgendadas as $consulta) {
            $inicioConsulta = Carbon::createFromFormat('H:i', $consulta->horario_inicio->format('H:i'));
            $fimConsulta = Carbon::createFromFormat('H:i', $consulta->horario_fim->format('H:i'));
            $prioridadeAlta = ($consulta->prioridade ?? 'normal') === 'alta';

            // Se for consulta de prioridade alta, só ocupa se o horário de início for EXATAMENTE o mesmo do slot
            if ($prioridadeAlta) {
                // Verificar se o horário de início da consulta é exatamente o mesmo do slot
                if ($inicio->format('H:i') === $inicioConsulta->format('H:i')) {
                    return $consulta;
                }
                // Se não for exatamente o mesmo horário, não ocupa o slot normal
                continue;
            }

            // Para consultas normais, verificar sobreposição normalmente
            if (($inicio->gte($inicioConsulta) && $inicio->lt($fimConsulta)) ||
                ($fim->gt($inicioConsulta) && $fim->lte($fimConsulta)) ||
                ($inicio->lt($inicioConsulta) && $fim->gt($fimConsulta))) {
                return $consulta;
            }
        }

        return null;
    }

    /**
     * Verifica se um horário específico está disponível
     * ⚠️ IMPORTANTE: Consultas de prioridade alta (urgência) bloqueiam slots normais APENAS se o horário de início for exatamente o mesmo
     */
    private function horarioEstaDisponivel($inicio, $fim, $consultasAgendadas, $configuracao): bool
    {
        // Verificar se não conflita com consultas já agendadas
        foreach ($consultasAgendadas as $consulta) {
            $inicioConsulta = Carbon::createFromFormat('H:i', $consulta->horario_inicio->format('H:i'));
            $fimConsulta = Carbon::createFromFormat('H:i', $consulta->horario_fim->format('H:i'));
            $prioridadeAlta = ($consulta->prioridade ?? 'normal') === 'alta';

            // Se for consulta de prioridade alta, só bloqueia se o horário de início for EXATAMENTE o mesmo do slot
            if ($prioridadeAlta) {
                // Verificar se o horário de início da consulta é exatamente o mesmo do slot
                if ($inicio->format('H:i') === $inicioConsulta->format('H:i')) {
                    return false;
                }
                // Se não for exatamente o mesmo horário, não bloqueia o slot normal
                continue;
            }

            // Para consultas normais, verificar sobreposição normalmente
            if (($inicio->lt($fimConsulta) && $fim->gt($inicioConsulta))) {
                return false;
            }
        }

        // Verificar se não conflita com pausas configuradas
        if ($configuracao->pausas) {
            foreach ($configuracao->pausas as $pausa) {
                $inicioPausa = Carbon::createFromFormat('H:i', $pausa['inicio']);
                $fimPausa = Carbon::createFromFormat('H:i', $pausa['fim']);

                // Verificar sobreposição com pausa
                if (($inicio->lt($fimPausa) && $fim->gt($inicioPausa))) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Valida se o dia da semana está disponível
     */
    private function validarDiaSemana($configuracao, $data): bool
    {
        if (!$configuracao) {
            return false;
        }

        $diaSemana = Carbon::parse($data)->dayOfWeek;
        $dias = ['dom', 'seg', 'ter', 'qua', 'qui', 'sex', 'sab'];
        $campoDia = $dias[$diaSemana];

        return (bool) $configuracao->$campoDia;
    }

    /**
     * Verifica se há conflito de horário (exclui encerrada/cancelada).
     * Com $forUpdate=true, usa lock dentro de transação para evitar double-book.
     */
    private function verificarConflitoHorario(
        $userId,
        $data,
        $horarioInicio,
        $horarioFim,
        $excludeId = null,
        bool $forUpdate = false
    ): bool {
        $query = Consulta::where('user_id', $userId)
                        ->where('data', $data)
                        ->whereNotIn('situacao_id', [4, 5]) // encerrada, cancelada
                        ->where(function ($q) use ($horarioInicio, $horarioFim) {
                            $q->whereBetween('horario_inicio', [$horarioInicio, $horarioFim])
                              ->orWhereBetween('horario_fim', [$horarioInicio, $horarioFim])
                              ->orWhere(function ($q2) use ($horarioInicio, $horarioFim) {
                                  $q2->where('horario_inicio', '<=', $horarioInicio)
                                     ->where('horario_fim', '>=', $horarioFim);
                              });
                        });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($forUpdate) {
            $query->lockForUpdate();
        }

        return $query->exists();
    }

    /**
     * Retorna a fila de espera do dia (TODAS as consultas agendadas para hoje)
     * Inclui consultas vindas da Agenda E consultas criadas diretamente na Fila
     * Ordenadas por prioridade (alta primeiro) e depois por horário
     */
    public function filaEspera(Request $request): JsonResponse
    {
        try {
            $data = $request->input('data', now()->format('Y-m-d'));

            // Buscar TODAS as consultas agendadas para hoje
            // Isso inclui consultas criadas na Agenda E consultas criadas na Fila
            // Considerar apenas consultas que ainda não foram finalizadas ou canceladas
            // situacao_id: 1 = Agendada, 2 = Inativo, 6 = Em Atendimento
            // Excluir: 2 = Inativo, 3 = Suspenso, 4 = Encerrada, 5 = Cancelada, 6 = Em Atendimento
            $consultas = Consulta::with(['paciente', 'user', 'parceiro', 'situacao'])
                ->where('data', $data)
                ->whereNotIn('situacao_id', [2, 3, 4, 5, 6]) // Excluir inativos (2) e suspensos (3) e encerradas (4) e canceladas (5) e em atendimento (6)
                ->where(function ($query) {
                    $query->whereNotNull('chegada_em')  // Chegou ao consultório (chegada_em não é null)
                          ->orWhere('prioridade', 'alta');   // OU é urgência (prioridade alta)
                })
                ->orderByRaw("CASE
                    WHEN prioridade = 'alta' THEN 1
                    WHEN prioridade = 'normal' THEN 2
                    WHEN prioridade = 'baixa' THEN 3
                    ELSE 4
                END")
                ->orderBy('horario_inicio') // Ordenar por horário dentro de cada prioridade
                ->get();

            // Formatar dados para o frontend
            $filaFormatada = $consultas->map(function ($consulta) {
                // Formatar horário de início
                // Como está no cast como 'datetime:H:i', vem como Carbon quando acessado via Eloquent
                $horaChegada = Carbon::now()->format('H:i');
                if ($consulta->horario_inicio) {
                    try {
                        // Se for Carbon, usar format diretamente
                        if ($consulta->horario_inicio instanceof Carbon) {
                            $horaChegada = $consulta->horario_inicio->format('H:i');
                        } else {
                            // Se for string, fazer parse
                            $horaChegada = Carbon::parse($consulta->horario_inicio)->format('H:i');
                        }
                    } catch (\Exception $e) {
            report($e);

            // Em caso de erro, usar horário atual
                        $horaChegada = Carbon::now()->format('H:i');
                    }
                }

                // Usar horário de chegada se existir, senão usar horário agendado
                $horaChegadaFormatada = $consulta->chegada_em
                    ? Carbon::parse($consulta->chegada_em)->format('H:i')
                    : $horaChegada;

                return [
                    'id' => $consulta->id,
                    'idPaciente' => $consulta->paciente_id,
                    'nomePaciente' => $consulta->paciente->nome ?? 'N/A',
                    'telefone' => $consulta->paciente->contato ?? 'N/A', // Campo é 'contato' no modelo Cadastro
                    'profissional' => $consulta->user->name ?? 'N/A',
                    'tipoConsulta' => $consulta->procedimento ?? 'Consulta',
                    'prioridade' => $consulta->prioridade ?? 'normal',
                    'horaChegada' => $horaChegadaFormatada,
                    'codigoChegada' => $consulta->codigo_chegada ?? null, // NOVO CAMPO
                    'observacoes' => $consulta->observacoes ?? null,
                    'data' => $consulta->data->format('Y-m-d'),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Fila de espera carregada com sucesso',
                'data' => $filaFormatada
            ], 200);

        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar fila de espera',
            ], 500);
        }
    }

    /**
     * Marca uma consulta como "em atendimento"
     */
    public function chamarPaciente(Request $request, $id): JsonResponse
    {
        try {
            $consulta = Consulta::findOrFail($id);

            try {
                $consulta = $this->consultaStatusService->transicionar(
                    $consulta,
                    ConsultaStatus::EM_ATENDIMENTO,
                    $request,
                    acao: 'chamar',
                );
            } catch (RuntimeException $e) {
                return $this->respostaExcecaoStatus($e);
            }

            // Disparar evento de broadcast
            $consultaCarregada = $consulta->load(['paciente', 'user']);
            Log::info('PacienteChamado disparado', [
                'consulta_id' => $consultaCarregada->id,
                'clinic_slug' => TenantContext::slug(),
                'profissional_id' => $consultaCarregada->user_id,
                'tem_codigo_chegada' => filled($consultaCarregada->codigo_chegada),
            ]);
            event(new PacienteChamado($consultaCarregada));

            return response()->json([
                'success' => true,
                'message' => 'Paciente chamado para atendimento',
                'data' => $consulta->load(['paciente', 'user', 'situacao'])
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Consulta não encontrada',
            ], 404);

        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao chamar paciente',
            ], 500);
        }
    }

    /**
     * Confirma a chegada do paciente ao consultório
     */
    public function confirmarChegada(Request $request, $id): JsonResponse
    {
        try {
            $consulta = Consulta::findOrFail($id);

            // Verificar se já não foi confirmada
            if ($consulta->chegada_em) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chegada já foi confirmada anteriormente'
                ], 400);
            }

            // Gerar código de chegada (ex: sequencial do dia ou timestamp)
            $codigoChegada = $this->gerarCodigoChegada($consulta->data);

            try {
                $consulta = $this->consultaStatusService->transicionar(
                    $consulta,
                    ConsultaStatus::CHEGOU,
                    $request,
                    acao: 'confirmar_chegada',
                    extra: [
                        'chegada_em' => now(),
                        'codigo_chegada' => $codigoChegada,
                    ],
                );
            } catch (RuntimeException $e) {
                return $this->respostaExcecaoStatus($e);
            }

            return response()->json([
                'success' => true,
                'message' => 'Chegada do paciente confirmada com sucesso',
                'data' => $consulta->load(['paciente', 'user', 'situacao'])
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Consulta não encontrada',
            ], 404);

        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao confirmar chegada',
            ], 500);
        }
    }

    /**
     * Gera um código único de chegada para o dia
     */
    private function gerarCodigoChegada($data): string
    {
        // Contar quantas chegadas já existem no dia
        $contador = Consulta::whereDate('data', $data)
            ->whereNotNull('codigo_chegada')
            ->count();

        // Gerar código: data formatada + número sequencial
        // Exemplo: "2025-01-15-001" ou "A001" (mais curto)
        $numeroSequencial = str_pad($contador + 1, 3, '0', STR_PAD_LEFT);
        $codigo = 'A' . $numeroSequencial; // A001, A002, etc.

        return $codigo;
    }

    /**
     * Adiciona um paciente à fila de espera (cria consulta para hoje)
     * ⚠️ IMPORTANTE: Consultas criadas diretamente na fila têm prioridade "alta" automaticamente
     */
    public function adicionarFilaEspera(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'paciente_id' => 'required|exists:cadastros,id',
                'user_id' => 'required|exists:users,id',
                // prioridade NÃO deve vir do request - sempre será "alta"
                'procedimento' => 'nullable|string|max:255',
                'observacoes' => 'nullable|string',
            ]);

            // Gerar código de chegada (mesma lógica da confirmação de chegada)
            $dataConsulta = now()->format('Y-m-d');
            $codigoChegada = $this->gerarCodigoChegada($dataConsulta);

            $consulta = Consulta::create([
                'paciente_id' => $request->paciente_id,
                'user_id' => $request->user_id,
                'data' => $dataConsulta,
                'horario_inicio' => now()->format('H:i'), // Horário atual (chegada imediata)
                'horario_fim' => now()->addMinutes(30)->format('H:i'), // Adicionar 30 minutos como padrão
                'prioridade' => 'alta', // ⚠️ SEMPRE alta para consultas criadas na fila
                'procedimento' => $request->procedimento ?? 'Consulta de Rotina',
                'observacoes' => $request->observacoes,
                'situacao_id' => 1, // Agendada (aguardando atendimento)
                'status' => ConsultaStatus::CHEGOU, // já nasce com chegada confirmada (fila direta)
                'chegada_em' => now(), // Paciente já chegou ao ser adicionado na fila
                'codigo_chegada' => $codigoChegada, // Código gerado automaticamente
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Paciente adicionado à fila com sucesso',
                'data' => $consulta->load(['paciente', 'user', 'situacao'])
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao adicionar paciente à fila',
            ], 500);
        }
    }

    /**
     * Retorna estatísticas da fila de espera
     */
    public function estatisticasFilaEspera(Request $request): JsonResponse
    {
        try {
            $data = $request->input('data', now()->format('Y-m-d'));

            // Total de pacientes na fila (excluindo encerradas e canceladas)
            $totalNaFila = Consulta::where('data', $data)
                ->whereNotIn('situacao_id', [2, 3, 4, 5, 6]) // Excluir inativos (2) e suspensos (3) e encerradas (4) e canceladas (5) e em atendimento (6)
                ->where(function ($query) {
                    $query->whereNotNull('chegada_em')  // Chegou ao consultório
                          ->orWhere('prioridade', 'alta');   // OU é urgência
                })
                ->count();

            // Pacientes com prioridade alta na fila
            $prioridadeAlta = Consulta::where('data', $data)
                ->where('prioridade', 'alta')
                ->whereNotIn('situacao_id', [2, 3, 4, 5, 6])
                ->where(function ($query) {
                    $query->whereNotNull('chegada_em')  // Chegou ao consultório
                          ->orWhere('prioridade', 'alta');   // OU é urgência
                })
                ->count();

            // Pacientes atendidos hoje (encerradas)
            $atendidosHoje = Consulta::where('data', $data)
                ->where('situacao_id', 4) // Encerradas
                ->count();

            // Calcular tempo médio de espera
            $consultasAguardando = Consulta::where('data', $data)
                ->whereNotIn('situacao_id', [2, 3, 4, 5, 6])
                ->where(function ($query) {
                    $query->whereNotNull('chegada_em')  // Chegou ao consultório
                          ->orWhere('prioridade', 'alta');   // OU é urgência
                })
                ->get();

            $tempoMedio = 0;
            if ($consultasAguardando->count() > 0) {
                $tempos = $consultasAguardando->map(function ($consulta) {
                    try {
                        // Formatar data
                        $dataFormatada = $consulta->data instanceof Carbon
                            ? $consulta->data->format('Y-m-d')
                            : (is_string($consulta->data) ? $consulta->data : $consulta->data->format('Y-m-d'));

                        // Formatar horário
                        $horaFormatada = '';
                        if ($consulta->horario_inicio) {
                            if ($consulta->horario_inicio instanceof Carbon) {
                                $horaFormatada = $consulta->horario_inicio->format('H:i');
                            } elseif (is_string($consulta->horario_inicio)) {
                                // Se for string, pode vir como "H:i" ou "Y-m-d H:i:s"
                                if (strlen($consulta->horario_inicio) <= 5) {
                                    $horaFormatada = $consulta->horario_inicio;
                                } else {
                                    $horaFormatada = Carbon::parse($consulta->horario_inicio)->format('H:i');
                                }
                            }
                        }

                        // Criar data/hora de chegada
                        $chegada = Carbon::parse($dataFormatada . ' ' . $horaFormatada);
                        // Calcular diferença em minutos entre agora e a chegada
                        return now()->diffInMinutes($chegada);
                    } catch (\Exception $e) {
            report($e);

            // Se houver erro no parse, retornar 0 (não afeta a média significativamente)
                        return 0;
                    }
                });
                $tempoMedio = round($tempos->avg());
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'pacientesNaFila' => $totalNaFila,
                    'pacientesPrioridade' => $prioridadeAlta,
                    'atendidosHoje' => $atendidosHoje,
                    'tempoMedioEspera' => $tempoMedio,
                ]
            ], 200);

        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar estatísticas',
            ], 500);
        }
    }

    /**
     * Lista pacientes em atendimento (situacao_id = 6)
     */
    public function pacientesEmAtendimento(Request $request): JsonResponse
    {
        try {
            // Buscar consultas com situacao_id = 6 (Em Atendimento)
            $consultas = Consulta::with(['paciente', 'user', 'situacao'])
                ->where('situacao_id', 6)
                ->orderBy('horario_inicio')
                ->get();

            // Formatar dados para o frontend
            $pacientesFormatados = $consultas->map(function ($consulta) {
                // Calcular tempo em atendimento (diferença entre agora e horario_inicio)
                $tempoEmAtendimento = 0;
                $tempoFormatado = '0min';

                if ($consulta->horario_inicio) {
                    try {
                        $horarioInicio = null;

                        // Se for Carbon, usar diretamente
                        if ($consulta->horario_inicio instanceof Carbon) {
                            $horarioInicio = $consulta->horario_inicio;
                        } else {
                            // Se for string, fazer parse
                            $horarioInicio = Carbon::parse($consulta->horario_inicio);
                        }

                        // Calcular diferença em minutos
                        $tempoEmAtendimento = now()->diffInMinutes($horarioInicio);

                        // Formatar tempo: "Xh Ymin" ou "Xmin"
                        if ($tempoEmAtendimento >= 60) {
                            $horas = floor($tempoEmAtendimento / 60);
                            $minutos = $tempoEmAtendimento % 60;
                            $tempoFormatado = $horas . 'h';
                            if ($minutos > 0) {
                                $tempoFormatado .= ' ' . $minutos . 'min';
                            }
                        } else {
                            $tempoFormatado = $tempoEmAtendimento . 'min';
                        }
                    } catch (\Exception $e) {
            report($e);

            // Em caso de erro, manter valores padrão
                        $tempoFormatado = '0min';
                    }
                }

                // Formatar horário de início
                $horarioFormatado = 'N/A';
                if ($consulta->horario_inicio) {
                    try {
                        if ($consulta->horario_inicio instanceof Carbon) {
                            $horarioFormatado = $consulta->horario_inicio->format('H:i');
                        } else {
                            $horarioFormatado = Carbon::parse($consulta->horario_inicio)->format('H:i');
                        }
                    } catch (\Exception $e) {
            report($e);

            $horarioFormatado = 'N/A';
                    }
                }

                return [
                    'id' => $consulta->id,
                    'paciente_id' => $consulta->paciente_id,
                    'nome_paciente' => $consulta->paciente->nome ?? 'N/A',
                    'contato' => $consulta->paciente->contato ?? 'N/A',
                    'medico_id' => $consulta->user_id,
                    'medico_nome' => $consulta->user->name ?? 'N/A',
                    'horario_inicio' => $horarioFormatado,
                    'tempo_em_atendimento' => $tempoEmAtendimento,
                    'tempo_em_atendimento_formatado' => $tempoFormatado,
                    'data' => $consulta->data ? $consulta->data->format('Y-m-d') : null,
                    'prioridade' => $consulta->prioridade ?? 'normal',
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Pacientes em atendimento carregados com sucesso',
                'data' => $pacientesFormatados
            ], 200);

        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar pacientes em atendimento',
            ], 500);
        }
    }

    /**
     * Lista todas as consultas de um paciente
     */
    public function consultasPorPaciente($pacienteId): JsonResponse
    {
        try {
            // Buscar todas as consultas do paciente (independente de status)
            $consultas = Consulta::with(['user', 'fichaClinica', 'situacao'])
                ->where('paciente_id', $pacienteId)
                ->orderBy('data', 'desc')
                ->orderBy('horario_inicio', 'desc')
                ->get();

            // Formatar dados para o frontend
            $consultasFormatadas = $consultas->map(function ($consulta) {
                // Formatar data
                $dataFormatada = $consulta->data ? $consulta->data->format('Y-m-d') : null;

                // Formatar horário de início
                $horarioFormatado = 'N/A';
                if ($consulta->horario_inicio) {
                    try {
                        if ($consulta->horario_inicio instanceof Carbon) {
                            $horarioFormatado = $consulta->horario_inicio->format('H:i');
                        } else {
                            $horarioFormatado = Carbon::parse($consulta->horario_inicio)->format('H:i');
                        }
                    } catch (\Exception $e) {
            report($e);

            $horarioFormatado = 'N/A';
                    }
                }

                // Formatar horário de fim
                $horarioFimFormatado = 'N/A';
                if ($consulta->horario_fim) {
                    try {
                        if ($consulta->horario_fim instanceof Carbon) {
                            $horarioFimFormatado = $consulta->horario_fim->format('H:i');
                        } else {
                            $horarioFimFormatado = Carbon::parse($consulta->horario_fim)->format('H:i');
                        }
                    } catch (\Exception $e) {
            report($e);

            $horarioFimFormatado = 'N/A';
                    }
                }

                // Verificar se tem ficha clínica vinculada
                $temFichaClinica = $consulta->fichaClinica !== null;
                $fichaClinicaId = $consulta->fichaClinica ? $consulta->fichaClinica->id : null;

                // Obter nome do status
                $statusNome = $consulta->situacao ? $consulta->situacao->nome : 'N/A';

                return [
                    'id' => $consulta->id,
                    'data' => $dataFormatada,
                    'horario_inicio' => $horarioFormatado,
                    'horario_fim' => $horarioFimFormatado,
                    'medico_id' => $consulta->user_id,
                    'medico_nome' => $consulta->user->name ?? 'N/A',
                    'procedimento' => $consulta->procedimento ?? 'Consulta',
                    'status_id' => $consulta->situacao_id,
                    'status_nome' => $statusNome,
                    'prioridade' => $consulta->prioridade ?? 'normal',
                    'tem_ficha_clinica' => $temFichaClinica,
                    'ficha_clinica_id' => $fichaClinicaId,
                    'observacoes' => $consulta->observacoes ?? null,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Consultas do paciente carregadas com sucesso',
                'data' => $consultasFormatadas
            ], 200);

        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar consultas do paciente',
            ], 500);
        }
    }

    /**
     * Retorna detalhes completos de uma consulta
     */
    public function detalhesConsulta($id): JsonResponse
    {
        try {
            // Buscar consulta com todos os relacionamentos
            $consulta = Consulta::with([
                'paciente',
                'user',
                'parceiro',
                'fichaClinica',
                'situacao',
                'configuracao',
                'historico.usuario:id,name',
                'consultaOrigem:id,data,horario_inicio,user_id',
                'profissionalAnterior:id,name',
                'profissionalNovo:id,name',
            ])->findOrFail($id);

            // Formatar data
            $dataFormatada = $consulta->data ? $consulta->data->format('Y-m-d') : null;

            // Formatar horário de início
            $horarioInicioFormatado = 'N/A';
            if ($consulta->horario_inicio) {
                try {
                    if ($consulta->horario_inicio instanceof Carbon) {
                        $horarioInicioFormatado = $consulta->horario_inicio->format('H:i');
                    } else {
                        $horarioInicioFormatado = Carbon::parse($consulta->horario_inicio)->format('H:i');
                    }
                } catch (\Exception $e) {
            report($e);

            $horarioInicioFormatado = 'N/A';
                }
            }

            // Formatar horário de fim
            $horarioFimFormatado = 'N/A';
            if ($consulta->horario_fim) {
                try {
                    if ($consulta->horario_fim instanceof Carbon) {
                        $horarioFimFormatado = $consulta->horario_fim->format('H:i');
                    } else {
                        $horarioFimFormatado = Carbon::parse($consulta->horario_fim)->format('H:i');
                    }
                } catch (\Exception $e) {
            report($e);

            $horarioFimFormatado = 'N/A';
                }
            }

            // Dados do paciente
            $paciente = null;
            if ($consulta->paciente) {
                $paciente = [
                    'id' => $consulta->paciente->id,
                    'nome' => $consulta->paciente->nome,
                    'data_nascimento' => $consulta->paciente->data_nascimento ? $consulta->paciente->data_nascimento->format('Y-m-d') : null,
                    'sexo' => $consulta->paciente->sexo,
                    'contato' => $consulta->paciente->contato,
                    'email' => $consulta->paciente->email,
                    'cpf' => $consulta->paciente->cpf,
                ];
            }

            // Dados do médico
            $medico = null;
            if ($consulta->user) {
                $medico = [
                    'id' => $consulta->user->id,
                    'name' => $consulta->user->name,
                    'email' => $consulta->user->email,
                    'especialidade' => $consulta->user->especialidade,
                    'crm' => $consulta->user->crm,
                ];
            }

            // Dados do parceiro (convênio)
            $parceiro = null;
            if ($consulta->parceiro) {
                $parceiro = [
                    'id' => $consulta->parceiro->id,
                    'nome' => $consulta->parceiro->nome,
                    'tipo' => $consulta->parceiro->tipo,
                ];
            }

            // Dados da situação
            $situacao = null;
            if ($consulta->situacao) {
                $situacao = [
                    'id' => $consulta->situacao->id,
                    'nome' => $consulta->situacao->nome,
                ];
            }

            // Dados da ficha clínica (se houver)
            $fichaClinica = null;
            if ($consulta->fichaClinica) {
                $fichaClinica = [
                    'id' => $consulta->fichaClinica->id,
                    'data_consulta' => $consulta->fichaClinica->data_consulta ? $consulta->fichaClinica->data_consulta->format('Y-m-d') : null,
                    'observacoes' => $consulta->fichaClinica->observacoes,
                    'created_at' => $consulta->fichaClinica->created_at ? $consulta->fichaClinica->created_at->format('Y-m-d H:i:s') : null,
                ];
            }

            $ultimoHistorico = $consulta->historico->first();

            // Montar resposta completa
            $detalhes = [
                'id' => $consulta->id,
                'data' => $dataFormatada,
                'horario_inicio' => $horarioInicioFormatado,
                'horario_fim' => $horarioFimFormatado,
                'procedimento' => $consulta->procedimento ?? 'Consulta',
                'prioridade' => $consulta->prioridade ?? 'normal',
                'observacoes' => $consulta->observacoes,
                'motivo_cancelamento' => $consulta->motivo_cancelamento,
                'situacao' => $situacao,
                'paciente' => $paciente,
                'medico' => $medico,
                'parceiro' => $parceiro,
                'ficha_clinica' => $fichaClinica,
                'tem_ficha_clinica' => $fichaClinica !== null,
                'created_at' => $consulta->created_at ? $consulta->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $consulta->updated_at ? $consulta->updated_at->format('Y-m-d H:i:s') : null,

                // Ciclo de vida (aditivo — não substitui os campos acima)
                'status_atual' => $consulta->status,
                'status_label' => ConsultaStatus::label($consulta->status ?? ConsultaStatus::PENDENTE),
                'tempo_em_status_minutos' => $consulta->tempo_em_status_minutos,
                'motivo' => $consulta->motivo_cancelamento ?? $consulta->motivo_transferencia ?? $consulta->motivo_reagendamento,
                'ultima_alteracao' => $ultimoHistorico ? [
                    'status_anterior' => $ultimoHistorico->status_anterior,
                    'status_novo' => $ultimoHistorico->status_novo,
                    'acao' => $ultimoHistorico->acao,
                    'motivo' => $ultimoHistorico->motivo,
                    'data' => $ultimoHistorico->created_at?->format('Y-m-d H:i:s'),
                ] : null,
                'responsavel' => $ultimoHistorico?->usuario ? [
                    'id' => $ultimoHistorico->usuario->id,
                    'nome' => $ultimoHistorico->usuario->name,
                ] : null,
                'transferencia' => $consulta->consulta_origem_id || $consulta->profissional_novo_id ? [
                    'consulta_origem_id' => $consulta->consulta_origem_id,
                    'origem' => $consulta->consultaOrigem ? [
                        'id' => $consulta->consultaOrigem->id,
                        'data' => $consulta->consultaOrigem->data?->format('Y-m-d'),
                    ] : null,
                    'profissional_anterior' => $consulta->profissionalAnterior?->name,
                    'profissional_novo' => $consulta->profissionalNovo?->name,
                    'motivo' => $consulta->motivo_transferencia,
                ] : null,
                'reagendamento' => $consulta->data_anterior ? [
                    'data_anterior' => $consulta->data_anterior->format('Y-m-d'),
                    'horario_anterior_inicio' => $consulta->horario_anterior_inicio?->format('H:i'),
                    'horario_anterior_fim' => $consulta->horario_anterior_fim?->format('H:i'),
                    'motivo' => $consulta->motivo_reagendamento,
                ] : null,
                'historico' => $consulta->historico->map(fn ($h) => [
                    'id' => $h->id,
                    'status_anterior' => $h->status_anterior,
                    'status_novo' => $h->status_novo,
                    'acao' => $h->acao,
                    'motivo' => $h->motivo,
                    'observacao' => $h->observacao,
                    'usuario' => $h->usuario?->name,
                    'data' => $h->created_at?->format('Y-m-d H:i:s'),
                ])->values(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Detalhes da consulta carregados com sucesso',
                'data' => $detalhes
            ], 200);

        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar detalhes da consulta',
            ], 404);
        }
    }

    /**
     * Encerra uma consulta manualmente
     */
    public function encerrarConsulta(Request $request, $id): JsonResponse
    {
        try {
            $consulta = Consulta::findOrFail($id);

            // Validar observações finais (opcional)
            $request->validate([
                'observacoes_finais' => 'nullable|string|max:1000',
            ]);

            // Preparar dados para atualização
            $dadosAtualizacao = [];

            // Se houver observações finais, concatenar às observações existentes
            if ($request->observacoes_finais) {
                $observacoesExistentes = $consulta->observacoes ?? '';
                $observacoesFinais = trim($request->observacoes_finais);

                if (!empty($observacoesExistentes)) {
                    // Concatenar com quebra de linha se já houver observações
                    $dadosAtualizacao['observacoes'] = $observacoesExistentes . "\n\n[Encerramento] " . $observacoesFinais;
                } else {
                    // Se não houver observações existentes, apenas adicionar as finais
                    $dadosAtualizacao['observacoes'] = "[Encerramento] " . $observacoesFinais;
                }
            }

            try {
                $consulta = $this->consultaStatusService->transicionar(
                    $consulta,
                    ConsultaStatus::REALIZADA,
                    $request,
                    acao: 'encerrar',
                    observacao: $request->observacoes_finais,
                    extra: $dadosAtualizacao,
                );
            } catch (RuntimeException $e) {
                return $this->respostaExcecaoStatus($e);
            }

            // Carregar relacionamentos
            $consulta->load(['paciente', 'user', 'parceiro', 'situacao', 'fichaClinica']);

            return response()->json([
                'success' => true,
                'message' => 'Consulta encerrada com sucesso',
                'data' => $consulta
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao encerrar consulta',
            ], 500);
        }
    }

    /**
     * Transfere a consulta para outro profissional/horário.
     * NÃO duplica: a origem fecha como TRANSFERIDA e uma nova consulta nasce
     * encadeada por consulta_origem_id (auditável, rastreável).
     */
    public function transferir(Request $request, $id): JsonResponse
    {
        try {
            $consulta = Consulta::findOrFail($id);

            $request->validate([
                'user_id' => 'required|exists:users,id',
                'data' => 'required|date',
                'horario_inicio' => 'required|date_format:H:i',
                'horario_fim' => 'required|date_format:H:i|after:horario_inicio',
                'motivo' => 'nullable|string|max:500',
            ]);

            if ($erro = $this->motivoProfissionalInvalido((int) $request->user_id)) {
                return response()->json(['success' => false, 'message' => $erro], 422);
            }

            try {
                $nova = $this->consultaStatusService->transferir(
                    $consulta,
                    (int) $request->user_id,
                    $request->data,
                    $request->horario_inicio,
                    $request->horario_fim,
                    $request->motivo,
                    $request,
                );
            } catch (RuntimeException $e) {
                return $this->respostaExcecaoStatus($e);
            }

            return response()->json([
                'success' => true,
                'message' => 'Consulta transferida com sucesso!',
                'data' => $nova->load('paciente', 'parceiro', 'situacao', 'consultaOrigem'),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao transferir consulta',
            ], 500);
        }
    }

    /**
     * Reagenda a consulta (mesma linha, sem duplicar). Guarda data/horário
     * anteriores e retorna o status para PENDENTE automaticamente.
     */
    public function reagendar(Request $request, $id): JsonResponse
    {
        try {
            $consulta = Consulta::findOrFail($id);

            $request->validate([
                'data' => 'required|date',
                'horario_inicio' => 'required|date_format:H:i',
                'horario_fim' => 'required|date_format:H:i|after:horario_inicio',
                'motivo' => 'nullable|string|max:500',
            ]);

            if ($this->verificarConflitoHorario(
                $consulta->user_id,
                $request->data,
                $request->horario_inicio,
                $request->horario_fim,
                $consulta->id,
                true,
            )) {
                return response()->json([
                    'success' => false,
                    'message' => 'Horário já ocupado para este profissional.',
                ], 422);
            }

            try {
                $consulta = $this->consultaStatusService->reagendar(
                    $consulta,
                    $request->data,
                    $request->horario_inicio,
                    $request->horario_fim,
                    $request->motivo,
                    $request,
                );
            } catch (RuntimeException $e) {
                return $this->respostaExcecaoStatus($e);
            }

            return response()->json([
                'success' => true,
                'message' => 'Consulta reagendada com sucesso!',
                'data' => $consulta->load('paciente', 'parceiro', 'situacao'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao reagendar consulta',
            ], 500);
        }
    }

    /**
     * Marca manualmente uma consulta como NO_SHOW (não compareceu).
     * O marcador automático por tolerância roda via Scheduler (ver Commands\MarcarConsultasNoShowCommand).
     */
    public function marcarNoShow(Request $request, $id): JsonResponse
    {
        try {
            $consulta = Consulta::findOrFail($id);

            $request->validate([
                'observacao' => 'nullable|string|max:500',
            ]);

            try {
                $consulta = $this->consultaStatusService->transicionar(
                    $consulta,
                    ConsultaStatus::NO_SHOW,
                    $request,
                    acao: 'no_show_manual',
                    observacao: $request->observacao,
                    extra: ['no_show_em' => now()],
                );
            } catch (RuntimeException $e) {
                return $this->respostaExcecaoStatus($e);
            }

            return response()->json([
                'success' => true,
                'message' => 'Consulta marcada como não comparecimento.',
                'data' => $consulta->load('paciente', 'parceiro', 'situacao'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao marcar não comparecimento',
            ], 500);
        }
    }

    /**
     * Consultas Vencidas — regra oficial: status PENDENTE/VENCIDA com data_hora < agora.
     * Exclui automaticamente REALIZADA, CANCELADA, TRANSFERIDA, NO_SHOW, REAGENDADA e CONFIRMADA.
     * Filtro e paginação feitos no banco (não no front).
     */
    public function vencidas(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'periodo' => 'nullable|in:hoje,semana,mes,trimestre',
                'user_id' => 'nullable|exists:users,id',
                'paciente_id' => 'nullable|exists:cadastros,id',
                'parceiro_id' => 'nullable|exists:parceiros,id',
                'procedimento' => 'nullable|string|max:255',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            // Job periódico garante o marcador; aqui também promovemos on-the-fly
            // qualquer PENDENTE vencida que o job ainda não tenha alcançado (zero espera para o usuário).
            $this->promoverPendentesVencidas();

            $query = Consulta::with(['paciente', 'user', 'parceiro'])->vencidas();

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            if ($request->filled('paciente_id')) {
                $query->where('paciente_id', $request->paciente_id);
            }
            if ($request->filled('parceiro_id')) {
                $query->where('parceiro_id', $request->parceiro_id);
            }
            if ($request->filled('procedimento')) {
                $query->where('procedimento', 'like', '%'.$request->procedimento.'%');
            }

            if ($request->filled('periodo')) {
                $limite = match ($request->periodo) {
                    'hoje' => now()->startOfDay(),
                    'semana' => now()->subDays(7)->startOfDay(),
                    'mes' => now()->subDays(30)->startOfDay(),
                    'trimestre' => now()->subDays(90)->startOfDay(),
                    default => null,
                };
                if ($limite) {
                    $query->aPartirDoMomento($limite);
                }
            }

            $consultas = $query
                ->orderBy('data')
                ->orderBy('horario_inicio')
                ->paginate($request->integer('per_page', 20));

            $consultas->getCollection()->transform(function (Consulta $consulta) {
                $vencimento = Carbon::parse($consulta->data->format('Y-m-d').' '.$consulta->horario_inicio->format('H:i'));

                return [
                    'id' => $consulta->id,
                    'paciente_id' => $consulta->paciente_id,
                    'paciente' => $consulta->paciente?->nome ?? 'Paciente',
                    'telefone' => $consulta->paciente?->contato ?? '—',
                    'profissional_id' => $consulta->user_id,
                    'profissional' => $consulta->user?->name ?? '—',
                    'procedimento' => $consulta->procedimento ?? 'Consulta',
                    'parceiro' => $consulta->parceiro?->nome,
                    'status' => $consulta->status,
                    'data_vencimento' => $vencimento->format('Y-m-d'),
                    'horario' => $vencimento->format('H:i'),
                    'dias_vencida' => now()->startOfDay()->diffInDays($vencimento->copy()->startOfDay()),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Consultas vencidas listadas com sucesso',
                'data' => $consultas->items(),
                'meta' => [
                    'current_page' => $consultas->currentPage(),
                    'last_page' => $consultas->lastPage(),
                    'per_page' => $consultas->perPage(),
                    'total' => $consultas->total(),
                ],
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar consultas vencidas',
            ], 500);
        }
    }

    /**
     * Promove PENDENTE -> VENCIDA imediatamente para quem consulta a tela (evita
     * esperar até 5 minutos do job agendado). Reaproveita o mesmo Service do
     * Scheduler, então gera histórico normalmente (nada de UPDATE silencioso).
     */
    private function promoverPendentesVencidas(): void
    {
        $this->consultaStatusService->marcarVencidasAutomaticamente();
    }

    /**
     * Histórico completo (auditoria) de uma consulta.
     */
    public function historicoConsulta($id): JsonResponse
    {
        try {
            $consulta = Consulta::findOrFail($id);

            $historico = $consulta->historico()
                ->with('usuario:id,name')
                ->paginate(50);

            return response()->json([
                'success' => true,
                'data' => $historico->getCollection()->map(fn ($h) => [
                    'id' => $h->id,
                    'status_anterior' => $h->status_anterior,
                    'status_novo' => $h->status_novo,
                    'acao' => $h->acao,
                    'motivo' => $h->motivo,
                    'observacao' => $h->observacao,
                    'usuario' => $h->usuario?->name,
                    'ip' => $h->ip,
                    'data' => $h->created_at?->format('Y-m-d H:i:s'),
                ]),
                'meta' => [
                    'current_page' => $historico->currentPage(),
                    'last_page' => $historico->lastPage(),
                    'total' => $historico->total(),
                ],
            ], 200);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar histórico da consulta',
            ], 404);
        }
    }

    /**
     * Indicadores rápidos do módulo de Consultas (dashboard).
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            $data = $request->input('data', now()->format('Y-m-d'));

            $porStatusHoje = Consulta::where('data', $data)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $contagem = fn (string $status) => (int) ($porStatusHoje[$status] ?? 0);

            $pendentes = $contagem(ConsultaStatus::PENDENTE);
            $confirmadas = $contagem(ConsultaStatus::CONFIRMADA);
            $emAtendimento = $contagem(ConsultaStatus::EM_ATENDIMENTO) + $contagem(ConsultaStatus::CHEGOU);
            $realizadas = $contagem(ConsultaStatus::REALIZADA);
            $canceladas = $contagem(ConsultaStatus::CANCELADA);
            $transferidas = $contagem(ConsultaStatus::TRANSFERIDA);
            $noShow = $contagem(ConsultaStatus::NO_SHOW);
            $vencidasHoje = $contagem(ConsultaStatus::VENCIDA);

            $totalHoje = array_sum([$pendentes, $confirmadas, $emAtendimento, $realizadas, $canceladas, $transferidas, $noShow, $vencidasHoje]);
            $baseComparecimento = $realizadas + $noShow;

            $vencidasTotal = Consulta::vencidas()->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $data,
                    'hoje' => [
                        'total' => $totalHoje,
                        'pendentes' => $pendentes,
                        'confirmadas' => $confirmadas,
                        'em_atendimento' => $emAtendimento,
                        'realizadas' => $realizadas,
                        'canceladas' => $canceladas,
                        'transferidas' => $transferidas,
                        'no_show' => $noShow,
                    ],
                    'vencidas' => $vencidasTotal,
                    'taxas' => [
                        'comparecimento' => $baseComparecimento > 0 ? round(($realizadas / $baseComparecimento) * 100, 1) : null,
                        'cancelamento' => $totalHoje > 0 ? round(($canceladas / $totalHoje) * 100, 1) : null,
                        'faltas' => $totalHoje > 0 ? round(($noShow / $totalHoje) * 100, 1) : null,
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dashboard de consultas',
            ], 500);
        }
    }
}
