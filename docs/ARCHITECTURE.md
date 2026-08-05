# Arquitetura — Marag

## Visão geral

```
Vue SPA (Pinia)  --JWT-->  Laravel API  -->  MySQL
       ^                      |
       +---- Reverb WS -------+  (telão / chamada)
```

- Front: SPA consumindo `http://localhost:8000/api`
- Back: JSON API (não Blade de negócio)
- Tempo real: evento `PacienteChamado` → canal `chamadas.pacientes`

## Bounded contexts (módulos)

| Contexto | Responsabilidade |
|----------|------------------|
| Identity | Login JWT, users, profiles |
| Pacientes | Cadastro / cadastros |
| Clínico | Ficha (anamnese, AV, refração, biomicroscopia, prescrição) |
| Agenda | Configuração de horários + consultas |
| Fila | Chegada, espera, chamada, atendimento |
| Parceiros | Convênios / labs |
| Financeiro | Receitas = consultas pagas (D9); despesas em `despesas`; APIs `/financeiro/*` (staff) |
| Tenant | Banco central (`clinics`) + 1 MySQL por clínica (D11); white-label D14 |

## Decisões (ADR resumido)

| ID | Decisão | Status |
|----|---------|--------|
| ADR-001 | Marca do produto = Marag | Aceito |
| ADR-002 | Auth = JWT custom endurecido | Aceito (Fase 1) |
| ADR-003 | Uma clínica primeiro; multi-tenant depois | Aceito → Fase 4 |
| ADR-007 | Isolamento = banco por clínica (D11-2) | Aceito |
| ADR-008 | White-label parcial; login/© Marag (D14) | Aceito |
| ADR-005 | Telão de chamada público (sem login) | Aceito (D4) |
| ADR-006 | RBAC básico admin/recepção/profissional desde Fase 1 | Aceito (D5) |
| ADR-009 | Ciclo de vida da consulta = 1 registro + campo `status` (enum) + `consulta_historico` (auditoria), sem duplicar linhas; `situacoes`/`situacao_id` mantido só por compat legado, sincronizado pelo `ConsultaStatusService` | Aceito |

## Banco

- MySQL 8, database local típico: `sturmerlocaldb`
- Migrations em `database/migrations`
- Multi-tenant: `php artisan clinic:migrate-all` roda migrations em **todas** as clínicas ativas (o `migrate` padrão só afeta a conexão atual)
- Profiles seed: Administrador, Recepção, Profissional
- `consulta_historico`: trilha de auditoria append-only de toda mudança de status de consulta (nunca editada/apagada) — ver [FUNCIONAMENTO_TECNICO.md §4](FUNCIONAMENTO_TECNICO.md)

## Fronteiras técnicas

- Multi-tenant: conexão `central` (registro) + `mysql` trocado por clínica em runtime
- Receitas financeiras agregadas de `consultas` (`pago`/`valor`/`forma_pagamento`) — D9; despesas em tabela própria
- Contas a pagar / inadimplentes ainda não modelados (slice seguinte da Fase 3)
- Front não deve chamar axios com path relativo `/api` fora da instância configurada
