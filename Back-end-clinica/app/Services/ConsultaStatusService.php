<?php

namespace App\Services;

use App\Models\Consulta;
use App\Models\ConsultaHistorico;
use App\Models\User;
use App\Support\ConsultaStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Única porta de entrada para mudar o status de uma consulta.
 *
 * Responsabilidades (Regra Principal do módulo):
 *  - Nunca duplica a consulta — só o status muda.
 *  - Valida a transição contra a máquina de estados (ConsultaStatus::transicoesPermitidas).
 *  - Sincroniza `situacao_id` (legado) para não quebrar agenda/fila/telão/financeiro.
 *  - Grava SEMPRE uma linha em `consulta_historico` (auditoria completa, nunca perdida).
 *  - Tudo dentro de uma transação com lock de linha (evita corrida em updates concorrentes).
 */
class ConsultaStatusService
{
    /**
     * Transiciona o status de uma consulta, gravando histórico e sincronizando o legado.
     *
     * @param array<string, mixed> $extra Colunas adicionais para atualizar junto (ex.: motivo_cancelamento).
     */
    public function transicionar(
        Consulta $consulta,
        string $statusNovo,
        ?Request $request = null,
        ?string $acao = null,
        ?string $motivo = null,
        ?string $observacao = null,
        array $extra = [],
    ): Consulta {
        if (! in_array($statusNovo, ConsultaStatus::all(), true)) {
            throw new RuntimeException('STATUS_INVALIDO');
        }

        return DB::transaction(function () use ($consulta, $statusNovo, $request, $acao, $motivo, $observacao, $extra) {
            /** @var Consulta $atual */
            $atual = Consulta::whereKey($consulta->id)->lockForUpdate()->firstOrFail();

            $statusAnterior = $atual->status ?? ConsultaStatus::PENDENTE;

            if ($statusAnterior !== $statusNovo && ! ConsultaStatus::podeTransicionar($statusAnterior, $statusNovo)) {
                throw new RuntimeException("TRANSICAO_INVALIDA|Não é possível mudar de {$statusAnterior} para {$statusNovo}.");
            }

            $atual->fill(array_merge($extra, [
                'status' => $statusNovo,
                'situacao_id' => ConsultaStatus::legacySituacaoId($statusNovo),
            ]));
            // withoutEvents: este Service já grava o histórico com contexto completo
            // (ação, motivo, usuário, IP) — evita duplicar com o ConsultaObserver.
            Consulta::withoutEvents(fn () => $atual->save());

            $this->registrarHistorico($atual, $statusAnterior, $statusNovo, $request, $acao, $motivo, $observacao);

            return $atual;
        });
    }

    public function registrarHistorico(
        Consulta $consulta,
        ?string $statusAnterior,
        string $statusNovo,
        ?Request $request = null,
        ?string $acao = null,
        ?string $motivo = null,
        ?string $observacao = null,
    ): ConsultaHistorico {
        $usuario = $request?->user();

        return ConsultaHistorico::create([
            'consulta_id' => $consulta->id,
            'status_anterior' => $statusAnterior,
            'status_novo' => $statusNovo,
            'usuario_id' => $usuario instanceof User ? $usuario->id : null,
            'acao' => $acao,
            'motivo' => $motivo,
            'observacao' => $observacao,
            'ip' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    /**
     * Reagendamento: NÃO duplica a consulta. Atualiza a mesma linha, registra o
     * horário/motivo anterior e passa por REAGENDADA -> PENDENTE na mesma transação
     * (2 linhas de histórico, para o rastro ficar completo).
     */
    public function reagendar(
        Consulta $consulta,
        string $novaData,
        string $novoHorarioInicio,
        string $novoHorarioFim,
        ?string $motivo,
        ?Request $request = null,
    ): Consulta {
        return DB::transaction(function () use ($consulta, $novaData, $novoHorarioInicio, $novoHorarioFim, $motivo, $request) {
            /** @var Consulta $atual */
            $atual = Consulta::whereKey($consulta->id)->lockForUpdate()->firstOrFail();

            $statusAnterior = $atual->status ?? ConsultaStatus::PENDENTE;
            if (! ConsultaStatus::podeTransicionar($statusAnterior, ConsultaStatus::REAGENDADA)) {
                throw new RuntimeException("TRANSICAO_INVALIDA|Consulta em {$statusAnterior} não pode ser reagendada.");
            }

            $dataAnterior = $atual->data;
            $horarioAnteriorInicio = $atual->horario_inicio;
            $horarioAnteriorFim = $atual->horario_fim;

            Consulta::withoutEvents(fn () => $atual->update([
                'status' => ConsultaStatus::REAGENDADA,
                'situacao_id' => ConsultaStatus::legacySituacaoId(ConsultaStatus::REAGENDADA),
                'data_anterior' => $dataAnterior,
                'horario_anterior_inicio' => $horarioAnteriorInicio,
                'horario_anterior_fim' => $horarioAnteriorFim,
                'motivo_reagendamento' => $motivo,
            ]));
            $this->registrarHistorico($atual, $statusAnterior, ConsultaStatus::REAGENDADA, $request, 'reagendar', $motivo);

            Consulta::withoutEvents(fn () => $atual->update([
                'data' => $novaData,
                'horario_inicio' => $novoHorarioInicio,
                'horario_fim' => $novoHorarioFim,
                'status' => ConsultaStatus::PENDENTE,
                'situacao_id' => ConsultaStatus::legacySituacaoId(ConsultaStatus::PENDENTE),
                'chegada_em' => null,
                'codigo_chegada' => null,
            ]));
            $this->registrarHistorico($atual, ConsultaStatus::REAGENDADA, ConsultaStatus::PENDENTE, $request, 'reagendar', $motivo, 'Retorno automático para PENDENTE após reagendamento.');

            return $atual->fresh();
        });
    }

    /**
     * Transferência: a consulta original é FECHADA (status TRANSFERIDA, nunca apagada)
     * e uma NOVA consulta é criada, encadeada via consulta_origem_id. Isso preserva a
     * Regra Principal (nenhuma consulta ativa é duplicada) mantendo rastreabilidade total.
     */
    public function transferir(
        Consulta $origem,
        int $novoProfissionalId,
        string $novaData,
        string $novoHorarioInicio,
        string $novoHorarioFim,
        ?string $motivo,
        ?Request $request = null,
    ): Consulta {
        return DB::transaction(function () use ($origem, $novoProfissionalId, $novaData, $novoHorarioInicio, $novoHorarioFim, $motivo, $request) {
            /** @var Consulta $atual */
            $atual = Consulta::whereKey($origem->id)->lockForUpdate()->firstOrFail();

            $statusAnterior = $atual->status ?? ConsultaStatus::PENDENTE;
            if (! ConsultaStatus::podeTransicionar($statusAnterior, ConsultaStatus::TRANSFERIDA)) {
                throw new RuntimeException("TRANSICAO_INVALIDA|Consulta em {$statusAnterior} não pode ser transferida.");
            }

            $profissionalAnteriorId = $atual->user_id;

            Consulta::withoutEvents(fn () => $atual->update([
                'status' => ConsultaStatus::TRANSFERIDA,
                'situacao_id' => ConsultaStatus::legacySituacaoId(ConsultaStatus::TRANSFERIDA),
                'profissional_anterior_id' => $profissionalAnteriorId,
                'profissional_novo_id' => $novoProfissionalId,
                'motivo_transferencia' => $motivo,
            ]));
            $this->registrarHistorico($atual, $statusAnterior, ConsultaStatus::TRANSFERIDA, $request, 'transferir', $motivo);

            $nova = Consulta::withoutEvents(fn () => Consulta::create([
                'user_id' => $novoProfissionalId,
                'paciente_id' => $atual->paciente_id,
                'parceiro_id' => $atual->parceiro_id,
                'procedimento' => $atual->procedimento,
                'data' => $novaData,
                'horario_inicio' => $novoHorarioInicio,
                'horario_fim' => $novoHorarioFim,
                'prioridade' => $atual->prioridade,
                'observacoes' => $atual->observacoes,
                'situacao_id' => ConsultaStatus::legacySituacaoId(ConsultaStatus::PENDENTE),
                'status' => ConsultaStatus::PENDENTE,
                'consulta_origem_id' => $atual->id,
                'profissional_anterior_id' => $profissionalAnteriorId,
                'profissional_novo_id' => $novoProfissionalId,
            ]));
            $this->registrarHistorico($nova, null, ConsultaStatus::PENDENTE, $request, 'transferir', $motivo, "Criada por transferência da consulta #{$atual->id}.");

            return $nova;
        });
    }

    /**
     * Regra oficial: PENDENTE + data_hora < NOW() + sem atendimento iniciado -> VENCIDA.
     * Chamado pelo Scheduler (a cada 5 min) e também on-the-fly ao abrir a tela de Vencidas.
     * Cada mudança gera histórico individual (nunca é um UPDATE silencioso em massa).
     */
    public function marcarVencidasAutomaticamente(): int
    {
        $candidatas = Consulta::query()
            ->where('status', ConsultaStatus::PENDENTE)
            ->antesDoMomento(now())
            ->get();

        $marcadas = 0;
        foreach ($candidatas as $consulta) {
            try {
                $this->transicionar(
                    $consulta,
                    ConsultaStatus::VENCIDA,
                    null,
                    acao: 'job_vencidas',
                    observacao: 'Marcada automaticamente pelo Scheduler (consultas:marcar-vencidas).',
                );
                $marcadas++;
            } catch (Throwable $e) {
                Log::warning('Falha ao marcar consulta como vencida', ['consulta_id' => $consulta->id, 'erro' => $e->getMessage()]);
            }
        }

        return $marcadas;
    }

    /**
     * Regra de No-show: CONFIRMADA sem chegada registrada, após a tolerância configurada -> NO_SHOW.
     */
    public function marcarNoShowAutomaticamente(int $toleranciaMinutos): int
    {
        $limite = now()->subMinutes($toleranciaMinutos);

        $candidatas = Consulta::query()
            ->where('status', ConsultaStatus::CONFIRMADA)
            ->whereNull('chegada_em')
            ->antesDoMomento($limite)
            ->get();

        $marcadas = 0;
        foreach ($candidatas as $consulta) {
            try {
                $this->transicionar(
                    $consulta,
                    ConsultaStatus::NO_SHOW,
                    null,
                    acao: 'job_no_show',
                    observacao: "Marcada automaticamente pelo Scheduler após {$toleranciaMinutos}min de tolerância sem chegada registrada.",
                    extra: ['no_show_em' => now()],
                );
                $marcadas++;
            } catch (Throwable $e) {
                Log::warning('Falha ao marcar consulta como no-show', ['consulta_id' => $consulta->id, 'erro' => $e->getMessage()]);
            }
        }

        return $marcadas;
    }
}
