<?php
// app/Http/Controllers/ConfiguracaoAgendamentoController.php

namespace App\Http\Controllers;

use App\Models\ConfiguracoesAgendamento;
use App\Models\Consulta;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ConfiguracaoAgendamentoController extends Controller
{
    public function index()
    {
        $configuracoes = ConfiguracoesAgendamento::with('user')
            ->orderBy('data_inicio_vigencia', 'desc')
            ->paginate(15);

        return response()->json($configuracoes);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'seg' => 'required|in:0,1',
            'ter' => 'required|in:0,1',
            'qua' => 'required|in:0,1',
            'qui' => 'required|in:0,1',
            'sex' => 'required|in:0,1',
            'sab' => 'required|in:0,1',
            'dom' => 'required|in:0,1',
            'horario_inicio' => 'required|date_format:H:i',
            'horario_fim' => 'required|date_format:H:i|after:horario_inicio',
            'duracao_consulta' => 'required|integer|min:15|max:120',
            'intervalo_consulta' => 'required|integer|min:0|max:60',
            'pausas' => 'nullable|array',
        ]);

        $userId = $request->user_id;
        $dataAtual = now();

        // Se é configuração padrão, aplicar regras específicas
        if ($userId === null) {
            return $this->processarConfiguracaoPadrao($request, $dataAtual);
        }

        // Para configurações personalizadas, usar lógica existente
        $dataInicio = $this->calcularDataInicioNovaConfiguracao($userId, $dataAtual);

        // Se há consultas futuras, precisa de confirmação
        if ($dataInicio > $dataAtual) {
            return response()->json([
                'precisa_confirmacao' => true,
                'data_inicio_sugerida' => $dataInicio->format('Y-m-d'),
                'consultas_afetadas' => $this->contarConsultasAfetadas($userId, $dataInicio),
                'mensagem' => "Você possui consultas agendadas até {$dataInicio->subDay()->format('d/m/Y')}. A nova configuração só poderá entrar em vigor a partir de {$dataInicio->format('d/m/Y')}."
            ], 200);
        }

        return $this->criarConfiguracao($request, $dataInicio);
    }

    public function confirmarCriacao(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'data_inicio_vigencia' => 'required|date|after_or_equal:today',
            'seg' => 'required|in:0,1',
            'ter' => 'required|in:0,1',
            'qua' => 'required|in:0,1',
            'qui' => 'required|in:0,1',
            'sex' => 'required|in:0,1',
            'sab' => 'required|in:0,1',
            'dom' => 'required|in:0,1',
            'horario_inicio' => 'required|date_format:H:i',
            'horario_fim' => 'required|date_format:H:i|after:horario_inicio',
            'duracao_consulta' => 'required|integer|min:15|max:120',
            'intervalo_consulta' => 'required|integer|min:0|max:60',
            'pausas' => 'nullable|array',
        ]);

        $dataInicio = Carbon::parse($request->data_inicio_vigencia);

        return $this->criarConfiguracao($request, $dataInicio);
    }

    /**
     * Processa criação de configuração padrão com regras específicas
     */
    private function processarConfiguracaoPadrao(Request $request, Carbon $dataAtual)
    {
        // 1. Verificar se já existe configuração padrão ativa
        $configuracaoPadraoAtiva = ConfiguracoesAgendamento::where('user_id', null)
            ->where('padrao', true)
            ->where(function($query) use ($dataAtual) {
                $query->whereNull('data_fim_vigencia')
                      ->orWhere('data_fim_vigencia', '>', $dataAtual);
            })
            ->first();

        if (!$configuracaoPadraoAtiva) {
            // Não há configuração padrão ativa, criar imediatamente
            return $this->criarConfiguracao($request, $dataAtual);
        }

        // 2. Verificar se a configuração padrão atual tem consultas
        $consultasPadrao = Consulta::where('configuracao_id', $configuracaoPadraoAtiva->id)
            ->where('data', '>=', $dataAtual)
            ->orderBy('data', 'desc')
            ->get();

        if ($consultasPadrao->isEmpty()) {
            // 2.1. Sem consultas: finalizar a atual e criar nova imediatamente
            // A nova configuração começará hoje, então a atual termina ontem
            $dataInicioNova = $dataAtual;
            $dataFimAtual = $dataAtual->copy()->subDay();

            $configuracaoPadraoAtiva->update([
                'data_fim_vigencia' => $dataFimAtual
            ]);

            return $this->criarConfiguracao($request, $dataInicioNova);
        }

        // 2.2. Com consultas: calcular data baseada na última consulta
        $ultimaConsulta = $consultasPadrao->first();
        $dataFimAtual = Carbon::parse($ultimaConsulta->data);
        $dataInicioNova = $dataFimAtual->copy()->addDay();

        // Retornar confirmação com período de transição
        return response()->json([
            'precisa_confirmacao' => true,
            'data_inicio_sugerida' => $dataInicioNova->format('Y-m-d'),
            'consultas_afetadas' => $consultasPadrao->count(),
            'mensagem' => "A configuração padrão atual possui consultas agendadas até {$dataFimAtual->format('d/m/Y')}. A nova configuração só poderá entrar em vigor a partir de {$dataInicioNova->format('d/m/Y')}.",
            'configuracao_atual_id' => $configuracaoPadraoAtiva->id,
            'data_fim_atual' => $dataFimAtual->format('Y-m-d')
        ], 200);
    }

    private function calcularDataInicioNovaConfiguracao($userId, $dataAtual)
    {
        $ultimaConsulta = Consulta::where('user_id', $userId)
            ->where('data', '>=', $dataAtual)
            ->orderBy('data', 'desc')
            ->first();

        if ($ultimaConsulta) {
            return Carbon::parse($ultimaConsulta->data)->addDay();
        }

        return $dataAtual;
    }

    private function contarConsultasAfetadas($userId, $dataInicio)
    {
        return Consulta::where('user_id', $userId)
            ->where('data', '<', $dataInicio)
            ->count();
    }

    private function criarConfiguracao(Request $request, Carbon $dataInicio)
    {
        DB::beginTransaction();

        try {
            // Desativar configuração anterior se existir
            if ($request->user_id) {
                // Para configurações personalizadas
                ConfiguracoesAgendamento::where('user_id', $request->user_id)
                    ->where(function($query) use ($dataInicio) {
                        $query->whereNull('data_fim_vigencia')
                              ->orWhere('data_fim_vigencia', '>', $dataInicio);
                    })
                    ->update([
                        'data_fim_vigencia' => $dataInicio->copy()->subDay()
                    ]);
            } else {
                // Para configurações padrão - desativar todas as padrão ativas
                ConfiguracoesAgendamento::where('user_id', null)
                    ->where('padrao', true)
                    ->where(function($query) use ($dataInicio) {
                        $query->whereNull('data_fim_vigencia')
                              ->orWhere('data_fim_vigencia', '>', $dataInicio);
                    })
                    ->update([
                        'data_fim_vigencia' => $dataInicio->copy()->subDay()
                    ]);
            }

            // Criar nova configuração
            // Converter valores para integer (0 ou 1) caso venham como string
            $configuracao = ConfiguracoesAgendamento::create([
                'user_id' => $request->user_id,
                'seg' => (int) $request->seg,
                'ter' => (int) $request->ter,
                'qua' => (int) $request->qua,
                'qui' => (int) $request->qui,
                'sex' => (int) $request->sex,
                'sab' => (int) $request->sab,
                'dom' => (int) $request->dom,
                'horario_inicio' => $request->horario_inicio,
                'horario_fim' => $request->horario_fim,
                'duracao_consulta' => $request->duracao_consulta,
                'intervalo_consulta' => $request->intervalo_consulta,
                'pausas' => $request->pausas,
                'data_inicio_vigencia' => $dataInicio,
                'data_fim_vigencia' => null,
                'padrao' => $request->user_id ? 0 : 1,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'configuracao' => $configuracao,
                'mensagem' => 'Configuração criada com sucesso!'
            ], 201);

        } catch (\Exception $e) {
            report($e);

            DB::rollback();
            return response()->json([
                'success' => false,
                'mensagem' => 'Erro ao criar configuração'
            ], 500);
        }
    }

    public function show($id)
    {
        $configuracao = ConfiguracoesAgendamento::with('user', 'consultas')->findOrFail($id);

        return response()->json([
            'id' => $configuracao->id,
            'user_id' => $configuracao->user_id,
            'user' => $configuracao->user,
            'seg' => (int) $configuracao->seg,
            'ter' => (int) $configuracao->ter,
            'qua' => (int) $configuracao->qua,
            'qui' => (int) $configuracao->qui,
            'sex' => (int) $configuracao->sex,
            'sab' => (int) $configuracao->sab,
            'dom' => (int) $configuracao->dom,
            'horario_inicio' => $configuracao->horario_inicio?->format('H:i'),
            'horario_fim' => $configuracao->horario_fim?->format('H:i'),
            'duracao_consulta' => $configuracao->duracao_consulta,
            'intervalo_consulta' => $configuracao->intervalo_consulta,
            'pausas' => $configuracao->pausas,
            'data_inicio_vigencia' => $configuracao->data_inicio_vigencia?->format('Y-m-d'),
            'data_fim_vigencia' => $configuracao->data_fim_vigencia?->format('Y-m-d'),
            'padrao' => (bool) $configuracao->padrao,
        ]);
    }

    public function update(Request $request, $id)
    {
        $configuracaoAtual = ConfiguracoesAgendamento::findOrFail($id);

        // Verificar se pode editar
        if ($configuracaoAtual->data_fim_vigencia !== null) {
            return response()->json([
                'erro' => true,
                'mensagem' => 'Esta configuração não pode ser editada pois já foi substituída por uma nova.',
                'data_fim_vigencia' => $configuracaoAtual->data_fim_vigencia->format('d/m/Y')
            ], 400);
        }

        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'seg' => 'required|in:0,1',
            'ter' => 'required|in:0,1',
            'qua' => 'required|in:0,1',
            'qui' => 'required|in:0,1',
            'sex' => 'required|in:0,1',
            'sab' => 'required|in:0,1',
            'dom' => 'required|in:0,1',
            'horario_inicio' => 'required|date_format:H:i',
            'horario_fim' => 'required|date_format:H:i|after:horario_inicio',
            'duracao_consulta' => 'required|integer|min:15|max:120',
            'intervalo_consulta' => 'required|integer|min:0|max:60',
            'pausas' => 'nullable|array',
        ]);

        $userId = $request->user_id ?? $configuracaoAtual->user_id;
        $dataAtual = now();

        // Para edição, sempre criar uma nova configuração e desativar a atual
        // Calcular data de início da nova configuração
        $dataInicio = $this->calcularDataInicioNovaConfiguracao($userId, $dataAtual);

        // Se há consultas futuras, precisa de confirmação
        if ($dataInicio > $dataAtual) {
            return response()->json([
                'precisa_confirmacao' => true,
                'data_inicio_sugerida' => $dataInicio->format('Y-m-d'),
                'consultas_afetadas' => $this->contarConsultasAfetadas($userId, $dataInicio),
                'mensagem' => "Você possui consultas agendadas até {$dataInicio->copy()->subDay()->format('d/m/Y')}. A nova configuração só poderá entrar em vigor a partir de {$dataInicio->format('d/m/Y')}."
            ], 200);
        }

        // Criar nova configuração e desativar a atual
        DB::beginTransaction();

        try {
            // Desativar configuração atual
            $configuracaoAtual->update([
                'data_fim_vigencia' => $dataInicio->copy()->subDay()
            ]);

            // Desativar outras configurações ativas do mesmo tipo (se houver)
            if ($userId) {
                ConfiguracoesAgendamento::where('user_id', $userId)
                    ->where('id', '!=', $configuracaoAtual->id)
                    ->where(function($query) use ($dataInicio) {
                        $query->whereNull('data_fim_vigencia')
                              ->orWhere('data_fim_vigencia', '>', $dataInicio);
                    })
                    ->update([
                        'data_fim_vigencia' => $dataInicio->copy()->subDay()
                    ]);
            } else {
                ConfiguracoesAgendamento::where('user_id', null)
                    ->where('padrao', true)
                    ->where('id', '!=', $configuracaoAtual->id)
                    ->where(function($query) use ($dataInicio) {
                        $query->whereNull('data_fim_vigencia')
                              ->orWhere('data_fim_vigencia', '>', $dataInicio);
                    })
                    ->update([
                        'data_fim_vigencia' => $dataInicio->copy()->subDay()
                    ]);
            }

            // Criar nova configuração com os dados atualizados
            // Converter valores para integer (0 ou 1) caso venham como string
            $novaConfiguracao = ConfiguracoesAgendamento::create([
                'user_id' => $userId,
                'seg' => (int) $request->seg,
                'ter' => (int) $request->ter,
                'qua' => (int) $request->qua,
                'qui' => (int) $request->qui,
                'sex' => (int) $request->sex,
                'sab' => (int) $request->sab,
                'dom' => (int) $request->dom,
                'horario_inicio' => $request->horario_inicio,
                'horario_fim' => $request->horario_fim,
                'duracao_consulta' => $request->duracao_consulta,
                'intervalo_consulta' => $request->intervalo_consulta,
                'pausas' => $request->pausas,
                'data_inicio_vigencia' => $dataInicio,
                'data_fim_vigencia' => null,
                'padrao' => $userId ? 0 : 1,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'configuracao' => $novaConfiguracao,
                'mensagem' => 'Configuração atualizada com sucesso! Uma nova configuração foi criada e a anterior foi desativada.'
            ], 200);

        } catch (\Exception $e) {
            report($e);

            DB::rollback();
            return response()->json([
                'success' => false,
                'mensagem' => 'Erro ao atualizar configuração'
            ], 500);
        }
    }

    public function destroy($id)
    {
        $configuracao = ConfiguracoesAgendamento::findOrFail($id);

        // Só bloqueia a configuração padrão que ainda está vigente (sem fim)
        $padraoVigente = $configuracao->isPadrao() && $configuracao->data_fim_vigencia === null;
        if ($padraoVigente) {
            return response()->json([
                'erro' => true,
                'mensagem' => 'A configuração padrão vigente não pode ser excluída. Crie uma nova configuração para substituí-la; depois você pode apagar as antigas.',
            ], 400);
        }

        // Verificar se há consultas ativas (situacao_id = 1) com data hoje ou futura
        $consultasAtivas = Consulta::where('configuracao_id', $configuracao->id)
            ->where('situacao_id', 1)
            ->where('data', '>=', now()->format('Y-m-d'))
            ->get();

        if ($consultasAtivas->count() > 0) {
            $dataConsultaMaisFutura = $consultasAtivas->max('data');

            $configuracao->update([
                'data_fim_vigencia' => $dataConsultaMaisFutura,
            ]);

            return response()->json([
                'success' => true,
                'mensagem' => "Configuração desativada com sucesso! Há {$consultasAtivas->count()} consulta(s) ativa(s) futura(s). A configuração será válida até {$dataConsultaMaisFutura}.",
                'tipo_acao' => 'desativada',
                'data_fim_vigencia' => $dataConsultaMaisFutura,
            ]);
        }

        $configuracao->delete();

        return response()->json([
            'success' => true,
            'mensagem' => 'Configuração excluída com sucesso!',
            'tipo_acao' => 'excluida',
        ]);
    }
}
