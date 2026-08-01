<?php

namespace App\Http\Controllers;

use App\Models\Consulta;
use App\Models\Despesa;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinanceiroController extends Controller
{
    private const FORMAS = [
        'dinheiro',
        'pix',
        'cartao_credito',
        'cartao_debito',
        'convenio',
        'transferencia',
        'outro',
    ];

    private const LABELS_FORMA = [
        'dinheiro' => 'Dinheiro',
        'pix' => 'PIX',
        'cartao_credito' => 'Cartão de Crédito',
        'cartao_debito' => 'Cartão de Débito',
        'convenio' => 'Convênio',
        'transferencia' => 'Transferência',
        'outro' => 'Outro',
    ];

    private const CORES_TIPO = ['#3B82F6', '#10B981', '#8B5CF6', '#F59E0B', '#EF4444', '#06B6D4', '#84CC16'];

    /**
     * Resumo financeiro do período (entradas de consultas pagas − despesas).
     */
    public function resumo(Request $request): JsonResponse
    {
        try {
            $periodo = $request->query('periodo', 'mes');
            [$inicio, $fim] = $this->resolverPeriodo($periodo);
            [$inicioAnt, $fimAnt] = $this->periodoAnterior($inicio, $fim);

            $atual = $this->agregarPeriodo($inicio, $fim);
            $anterior = $this->agregarPeriodo($inicioAnt, $fimAnt);

            $crescimentoReceita = $this->variacaoPercentual($atual['receita_total'], $anterior['receita_total']);
            $margem = $atual['receita_total'] > 0
                ? round((($atual['receita_total'] - $atual['despesa_total']) / $atual['receita_total']) * 100, 1)
                : 0;

            return response()->json([
                'success' => true,
                'message' => 'Resumo financeiro carregado',
                'data' => [
                    'periodo' => $periodo,
                    'data_inicio' => $inicio->toDateString(),
                    'data_fim' => $fim->toDateString(),
                    'receita_total' => $atual['receita_total'],
                    'despesa_total' => $atual['despesa_total'],
                    'saldo' => round($atual['receita_total'] - $atual['despesa_total'], 2),
                    'ticket_medio' => $atual['ticket_medio'],
                    'consultas_pagas' => $atual['consultas_pagas'],
                    'a_receber' => $atual['a_receber'],
                    'contas_a_receber' => $atual['contas_a_receber'],
                    'pacientes_atendidos' => $atual['pacientes_atendidos'],
                    'crescimento_receita' => $crescimentoReceita,
                    'margem' => $margem,
                    'por_forma_pagamento' => $atual['por_forma_pagamento'],
                    'por_procedimento' => $atual['por_procedimento'],
                    'por_convenio' => $atual['por_convenio'],
                    'comparativo' => [
                        'receita_atual' => $atual['receita_total'],
                        'receita_anterior' => $anterior['receita_total'],
                        'despesa_atual' => $atual['despesa_total'],
                        'despesa_anterior' => $anterior['despesa_total'],
                        'saldo_atual' => round($atual['receita_total'] - $atual['despesa_total'], 2),
                        'saldo_anterior' => round($anterior['receita_total'] - $anterior['despesa_total'], 2),
                        'crescimento_receita' => $crescimentoReceita,
                    ],
                    'despesas_recentes' => $atual['despesas_recentes'],
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar resumo financeiro',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Relatório detalhado com série temporal e histórico.
     */
    public function relatorio(Request $request): JsonResponse
    {
        try {
            $periodo = $request->query('periodo', 'mes');
            [$inicio, $fim] = $this->resolverPeriodo($periodo);
            [$inicioAnt, $fimAnt] = $this->periodoAnterior($inicio, $fim);

            $atual = $this->agregarPeriodo($inicio, $fim);
            $anterior = $this->agregarPeriodo($inicioAnt, $fimAnt);
            $crescimento = $this->variacaoPercentual($atual['receita_total'], $anterior['receita_total']);

            $baseCobranca = $atual['receita_total'] + $atual['a_receber'];
            $taxaRecebimento = $baseCobranca > 0
                ? round(($atual['receita_total'] / $baseCobranca) * 100, 1)
                : 100;

            $serie = $this->serieReceita($inicio, $fim, $periodo);
            $historico = $this->historicoComparativo($inicio, $fim, $periodo);

            return response()->json([
                'success' => true,
                'message' => 'Relatório financeiro carregado',
                'data' => [
                    'periodo' => $periodo,
                    'data_inicio' => $inicio->toDateString(),
                    'data_fim' => $fim->toDateString(),
                    'resumo' => [
                        'receita_total' => $atual['receita_total'],
                        'despesa_total' => $atual['despesa_total'],
                        'saldo' => round($atual['receita_total'] - $atual['despesa_total'], 2),
                        'crescimento' => $crescimento,
                    ],
                    'metricas' => [
                        'pacientes_atendidos' => $atual['pacientes_atendidos'],
                        'ticket_medio' => $atual['ticket_medio'],
                        'consultas_pagas' => $atual['consultas_pagas'],
                        'a_receber' => $atual['a_receber'],
                        'contas_a_receber' => $atual['contas_a_receber'],
                        'taxa_recebimento' => $taxaRecebimento,
                    ],
                    'receita_por_periodo' => $serie,
                    'por_procedimento' => $atual['por_procedimento'],
                    'por_forma_pagamento' => $atual['por_forma_pagamento'],
                    'por_convenio' => $atual['por_convenio'],
                    'historico_comparativo' => $historico,
                    'comparativo' => [
                        'receita_atual' => $atual['receita_total'],
                        'receita_anterior' => $anterior['receita_total'],
                        'despesa_atual' => $atual['despesa_total'],
                        'despesa_anterior' => $anterior['despesa_total'],
                    ],
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar relatório financeiro',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function listarDespesas(Request $request): JsonResponse
    {
        try {
            $periodo = $request->query('periodo');
            $query = Despesa::query()->orderByDesc('data')->orderByDesc('id');

            if ($periodo) {
                [$inicio, $fim] = $this->resolverPeriodo($periodo);
                $query->whereBetween('data', [$inicio->toDateString(), $fim->toDateString()]);
            } elseif ($request->filled('data_inicio') && $request->filled('data_fim')) {
                $query->whereBetween('data', [$request->data_inicio, $request->data_fim]);
            }

            $despesas = $query->limit(100)->get();

            return response()->json([
                'success' => true,
                'message' => 'Despesas listadas',
                'data' => $despesas,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar despesas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function criarDespesa(Request $request): JsonResponse
    {
        try {
            $dados = $this->validarDespesa($request);
            $despesa = Despesa::create($dados);

            return response()->json([
                'success' => true,
                'message' => 'Despesa registrada com sucesso',
                'data' => $despesa,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar despesa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function atualizarDespesa(Request $request, $id): JsonResponse
    {
        try {
            $despesa = Despesa::findOrFail($id);
            $dados = $this->validarDespesa($request);
            $despesa->update($dados);

            return response()->json([
                'success' => true,
                'message' => 'Despesa atualizada com sucesso',
                'data' => $despesa->fresh(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Despesa não encontrada',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar despesa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function excluirDespesa($id): JsonResponse
    {
        try {
            $despesa = Despesa::findOrFail($id);
            $despesa->delete();

            return response()->json([
                'success' => true,
                'message' => 'Despesa excluída com sucesso',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Despesa não encontrada',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir despesa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function validarDespesa(Request $request): array
    {
        return $request->validate([
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0.01',
            'data' => 'required|date',
            'categoria' => 'nullable|string|max:100',
            'forma_pagamento' => 'nullable|in:'.implode(',', self::FORMAS),
            'observacoes' => 'nullable|string|max:2000',
        ]);
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function resolverPeriodo(string $periodo): array
    {
        $hoje = Carbon::today();

        return match ($periodo) {
            'hoje' => [$hoje->copy()->startOfDay(), $hoje->copy()->endOfDay()],
            'semana' => [$hoje->copy()->startOfWeek(), $hoje->copy()->endOfWeek()],
            'mes' => [$hoje->copy()->startOfMonth(), $hoje->copy()->endOfMonth()],
            'trimestre' => [$hoje->copy()->firstOfQuarter(), $hoje->copy()->lastOfQuarter()],
            'semestre' => $hoje->month <= 6
                ? [$hoje->copy()->startOfYear(), $hoje->copy()->startOfYear()->addMonths(5)->endOfMonth()]
                : [$hoje->copy()->startOfYear()->addMonths(6), $hoje->copy()->endOfYear()],
            'ano' => [$hoje->copy()->startOfYear(), $hoje->copy()->endOfYear()],
            default => throw new \InvalidArgumentException('Período inválido. Use: hoje, semana, mes, trimestre, semestre, ano'),
        };
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function periodoAnterior(Carbon $inicio, Carbon $fim): array
    {
        $dias = $inicio->diffInDays($fim) + 1;

        return [
            $inicio->copy()->subDays($dias)->startOfDay(),
            $inicio->copy()->subDay()->endOfDay(),
        ];
    }

    private function agregarPeriodo(Carbon $inicio, Carbon $fim): array
    {
        $inicioStr = $inicio->toDateString();
        $fimStr = $fim->toDateString();

        $consultasPagas = Consulta::query()
            ->with('parceiro')
            ->where('pago', true)
            ->whereNotNull('valor')
            ->where('valor', '>', 0)
            ->whereBetween('data', [$inicioStr, $fimStr])
            ->whereNotIn('situacao_id', [5])
            ->get();

        $receitaTotal = round((float) $consultasPagas->sum('valor'), 2);
        $consultasPagasCount = $consultasPagas->count();
        $ticketMedio = $consultasPagasCount > 0
            ? round($receitaTotal / $consultasPagasCount, 2)
            : 0;

        $pendentes = Consulta::query()
            ->where(function ($q) {
                $q->where('pago', false)->orWhereNull('pago');
            })
            ->whereNotNull('valor')
            ->where('valor', '>', 0)
            ->whereBetween('data', [$inicioStr, $fimStr])
            ->whereNotIn('situacao_id', [5])
            ->get();

        $aReceber = round((float) $pendentes->sum('valor'), 2);

        $despesaTotal = round((float) Despesa::query()
            ->whereBetween('data', [$inicioStr, $fimStr])
            ->sum('valor'), 2);

        $pacientesAtendidos = Consulta::query()
            ->whereBetween('data', [$inicioStr, $fimStr])
            ->whereNotIn('situacao_id', [5])
            ->pluck('paciente_id')
            ->unique()
            ->count();

        return [
            'receita_total' => $receitaTotal,
            'despesa_total' => $despesaTotal,
            'ticket_medio' => $ticketMedio,
            'consultas_pagas' => $consultasPagasCount,
            'a_receber' => $aReceber,
            'contas_a_receber' => $pendentes->count(),
            'pacientes_atendidos' => $pacientesAtendidos,
            'por_forma_pagamento' => $this->agruparFormas($consultasPagas, $receitaTotal),
            'por_procedimento' => $this->agruparProcedimentos($consultasPagas, $receitaTotal),
            'por_convenio' => $this->agruparConvenios($consultasPagas, $receitaTotal),
            'despesas_recentes' => Despesa::query()
                ->whereBetween('data', [$inicioStr, $fimStr])
                ->orderByDesc('data')
                ->limit(8)
                ->get(),
        ];
    }

    private function agruparFormas($consultas, float $receitaTotal): array
    {
        $grupos = $consultas->groupBy(function ($c) {
            return $c->forma_pagamento ?: 'outro';
        });

        return $grupos->map(function ($itens, $forma) use ($receitaTotal) {
            $valor = round((float) $itens->sum('valor'), 2);

            return [
                'chave' => $forma,
                'nome' => self::LABELS_FORMA[$forma] ?? ucfirst(str_replace('_', ' ', $forma)),
                'valor' => $valor,
                'quantidade' => $itens->count(),
                'percentual' => $receitaTotal > 0 ? round(($valor / $receitaTotal) * 100, 1) : 0,
            ];
        })->sortByDesc('valor')->values()->all();
    }

    private function agruparProcedimentos($consultas, float $receitaTotal): array
    {
        $grupos = $consultas->groupBy(function ($c) {
            $nome = trim((string) ($c->procedimento ?: 'Consulta'));

            return $nome !== '' ? $nome : 'Consulta';
        });

        $i = 0;

        return $grupos->map(function ($itens, $nome) use ($receitaTotal, &$i) {
            $valor = round((float) $itens->sum('valor'), 2);
            $cor = self::CORES_TIPO[$i % count(self::CORES_TIPO)];
            $i++;

            return [
                'nome' => $nome,
                'valor' => $valor,
                'quantidade' => $itens->count(),
                'percentual' => $receitaTotal > 0 ? round(($valor / $receitaTotal) * 100, 1) : 0,
                'cor' => $cor,
            ];
        })->sortByDesc('valor')->values()->all();
    }

    private function agruparConvenios($consultas, float $receitaTotal): array
    {
        $comParceiro = $consultas->filter(fn ($c) => $c->parceiro_id);

        $grupos = $comParceiro->groupBy(function ($c) {
            return $c->parceiro?->nome ?: 'Convênio';
        });

        $lista = $grupos->map(function ($itens, $nome) use ($receitaTotal) {
            $valor = round((float) $itens->sum('valor'), 2);
            $qtd = $itens->count();

            return [
                'nome' => $nome,
                'valor' => $valor,
                'consultas' => $qtd,
                'ticket_medio' => $qtd > 0 ? round($valor / $qtd, 2) : 0,
                'percentual' => $receitaTotal > 0 ? round(($valor / $receitaTotal) * 100, 1) : 0,
            ];
        })->sortByDesc('valor')->values()->all();

        $particulares = $consultas->filter(fn ($c) => ! $c->parceiro_id);
        $valorPart = round((float) $particulares->sum('valor'), 2);
        $qtdPart = $particulares->count();

        return [
            'lista' => $lista,
            'particulares' => [
                'consultas' => $qtdPart,
                'valor' => $valorPart,
                'ticket_medio' => $qtdPart > 0 ? round($valorPart / $qtdPart, 2) : 0,
            ],
            'total_convenio_consultas' => $comParceiro->count(),
        ];
    }

    private function serieReceita(Carbon $inicio, Carbon $fim, string $periodo): array
    {
        $rows = Consulta::query()
            ->select(DB::raw('DATE(data) as dia'), DB::raw('SUM(valor) as total'))
            ->where('pago', true)
            ->whereNotNull('valor')
            ->where('valor', '>', 0)
            ->whereBetween('data', [$inicio->toDateString(), $fim->toDateString()])
            ->whereNotIn('situacao_id', [5])
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        if (in_array($periodo, ['mes', 'trimestre', 'semestre', 'ano'], true)) {
            $porSemana = [];
            foreach ($rows as $row) {
                $data = Carbon::parse($row->dia);
                $chave = $data->format('o').'-W'.$data->format('W');
                $label = 'Semana '.$data->weekOfYear;
                if (! isset($porSemana[$chave])) {
                    $porSemana[$chave] = ['periodo' => $label, 'valor' => 0];
                }
                $porSemana[$chave]['valor'] += (float) $row->total;
            }
            $serie = array_values($porSemana);
        } else {
            $serie = $rows->map(fn ($r) => [
                'periodo' => Carbon::parse($r->dia)->format('d/m'),
                'valor' => round((float) $r->total, 2),
            ])->all();
        }

        $max = collect($serie)->max('valor') ?: 1;

        return collect($serie)->map(function ($item) use ($max) {
            $valor = round((float) $item['valor'], 2);

            return [
                'periodo' => $item['periodo'],
                'valor' => $valor,
                'percentual' => round(($valor / $max) * 100, 1),
            ];
        })->values()->all();
    }

    private function historicoComparativo(Carbon $inicio, Carbon $fim, string $periodo): array
    {
        $itens = [];
        $cursorInicio = $inicio->copy();
        $cursorFim = $fim->copy();

        for ($i = 0; $i < 4; $i++) {
            $agg = $this->agregarPeriodo($cursorInicio, $cursorFim);
            $label = match ($periodo) {
                'hoje' => $cursorInicio->format('d/m/Y'),
                'semana' => 'Semana '.$cursorInicio->format('d/m'),
                'mes' => $cursorInicio->translatedFormat('M/Y'),
                'trimestre' => $cursorInicio->quarter.'º tri/'.$cursorInicio->year,
                'semestre' => ($cursorInicio->month <= 6 ? '1º' : '2º').' sem/'.$cursorInicio->year,
                'ano' => (string) $cursorInicio->year,
                default => $cursorInicio->toDateString(),
            };

            $itens[] = [
                'nome' => $i === 0 ? 'Atual' : $label,
                'receita' => $agg['receita_total'],
                'despesa' => $agg['despesa_total'],
                'consultas' => $agg['consultas_pagas'],
                'ticket_medio' => $agg['ticket_medio'],
                'crescimento' => 0,
            ];

            [$cursorInicio, $cursorFim] = $this->periodoAnterior($cursorInicio, $cursorFim);
        }

        for ($i = 0; $i < count($itens) - 1; $i++) {
            $itens[$i]['crescimento'] = $this->variacaoPercentual(
                $itens[$i]['receita'],
                $itens[$i + 1]['receita']
            );
        }

        return $itens;
    }

    private function variacaoPercentual(float $atual, float $anterior): float
    {
        if ($anterior == 0.0) {
            return $atual > 0 ? 100.0 : 0.0;
        }

        return round((($atual - $anterior) / $anterior) * 100, 1);
    }
}
