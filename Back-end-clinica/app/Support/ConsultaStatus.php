<?php

namespace App\Support;

/**
 * Ciclo de vida da consulta (status novo, aditivo — não substitui `situacao_id`).
 *
 * `situacao_id` (tabela genérica `situacoes`, compartilhada com users/parceiros)
 * continua existindo e é sincronizado automaticamente a partir do status novo,
 * para não quebrar telas/consultas legadas (agenda, fila, telão, financeiro).
 */
final class ConsultaStatus
{
    public const PENDENTE = 'PENDENTE';

    public const CONFIRMADA = 'CONFIRMADA';

    public const CHEGOU = 'CHEGOU';

    public const EM_ATENDIMENTO = 'EM_ATENDIMENTO';

    public const REALIZADA = 'REALIZADA';

    public const CANCELADA = 'CANCELADA';

    public const TRANSFERIDA = 'TRANSFERIDA';

    public const REAGENDADA = 'REAGENDADA';

    public const NO_SHOW = 'NO_SHOW';

    public const VENCIDA = 'VENCIDA';

    /** @return list<string> Todos os status válidos (para CHECK constraint / validação). */
    public static function all(): array
    {
        return [
            self::PENDENTE, self::CONFIRMADA, self::CHEGOU, self::EM_ATENDIMENTO,
            self::REALIZADA, self::CANCELADA, self::TRANSFERIDA, self::REAGENDADA,
            self::NO_SHOW, self::VENCIDA,
        ];
    }

    /** Status considerados "encerrados" (não ocupam agenda/fila ativa). */
    public static function terminais(): array
    {
        return [self::REALIZADA, self::CANCELADA, self::TRANSFERIDA, self::NO_SHOW];
    }

    /**
     * Máquina de estados — transições permitidas a partir de cada status.
     * Espelha o fluxo "oficial" (PENDENTE→CONFIRMADA→CHEGOU→EM_ATENDIMENTO→REALIZADA),
     * mas permanece tolerante a atalhos operacionais reais já usados na recepção hoje
     * (ex.: chamar direto da fila sem o passo formal de "confirmar chegada"), para não
     * travar nenhum fluxo que já funciona em produção.
     *
     * @return array<string, list<string>>
     */
    public static function transicoesPermitidas(): array
    {
        return [
            self::PENDENTE => [
                self::CONFIRMADA, self::CHEGOU, self::EM_ATENDIMENTO,
                self::CANCELADA, self::TRANSFERIDA, self::REAGENDADA, self::VENCIDA, self::NO_SHOW,
            ],
            self::CONFIRMADA => [
                self::CHEGOU, self::EM_ATENDIMENTO,
                self::CANCELADA, self::TRANSFERIDA, self::REAGENDADA, self::NO_SHOW,
            ],
            self::CHEGOU => [self::EM_ATENDIMENTO, self::CANCELADA, self::NO_SHOW],
            self::EM_ATENDIMENTO => [self::REALIZADA, self::CANCELADA],
            // Vencida é uma consulta pendente que passou da hora; pode ainda ser tratada normalmente.
            self::VENCIDA => [
                self::CONFIRMADA, self::CHEGOU, self::EM_ATENDIMENTO,
                self::CANCELADA, self::TRANSFERIDA, self::REAGENDADA, self::NO_SHOW,
            ],
            // Terminais: sem transição de saída (histórico é imutável a partir daqui).
            self::REALIZADA => [],
            self::CANCELADA => [],
            self::TRANSFERIDA => [],
            self::NO_SHOW => [],
            // Transitório: o service sempre encaminha para PENDENTE na mesma transação.
            self::REAGENDADA => [self::PENDENTE],
        ];
    }

    public static function podeTransicionar(string $de, string $para): bool
    {
        return in_array($para, self::transicoesPermitidas()[$de] ?? [], true);
    }

    /**
     * Mapa de compatibilidade: status novo -> `situacao_id` legado (tabela `situacoes`).
     * Mantém 100% das telas antigas (agenda, fila, telão, financeiro) funcionando
     * sem alteração, enquanto o status novo passa a ser a fonte de verdade.
     *
     * IDs legados (seed `create_situacoes_table`): 1=ativo, 4=encerrado, 5=cancelado, 6=em_atendimento.
     */
    public static function legacySituacaoId(string $status): int
    {
        return match ($status) {
            self::EM_ATENDIMENTO => 6,
            self::REALIZADA => 4,
            self::CANCELADA, self::TRANSFERIDA, self::NO_SHOW => 5,
            // PENDENTE, CONFIRMADA, CHEGOU, REAGENDADA (transitório), VENCIDA: ainda "ativa" na agenda.
            default => 1,
        };
    }

    public static function label(string $status): string
    {
        return match ($status) {
            self::PENDENTE => 'Pendente',
            self::CONFIRMADA => 'Confirmada',
            self::CHEGOU => 'Chegou',
            self::EM_ATENDIMENTO => 'Em atendimento',
            self::REALIZADA => 'Realizada',
            self::CANCELADA => 'Cancelada',
            self::TRANSFERIDA => 'Transferida',
            self::REAGENDADA => 'Reagendada',
            self::NO_SHOW => 'Não compareceu',
            self::VENCIDA => 'Vencida',
            default => $status,
        };
    }
}
