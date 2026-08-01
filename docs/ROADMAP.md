# Roadmap — Marag

## Fase 0 — Documentação *(concluída)*

- [x] Decisões D1/D2/D3 (Marag, JWT, 1 clínica)
- [x] README raiz + AGENTS + docs + rules Cursor
- [x] READMEs front/back enxutos

## Fase 1 — P0 Segurança *(concluída)*

- [x] Middleware JWT nas rotas
- [x] Payload JWT limpo
- [x] Interceptor axios + tratamento 401
- [x] RBAC por perfil (admin / recepção / profissional) — API + menu + rotas
- [x] Mensagens de erro profissionais
- [x] Telão público (D4)
- [x] Teste Feature `AuthJwtTest`

## Fase 2 — P1 Clínico *(concluída)*

- [x] Agenda semana/mês + pago/forma pagamento
- [x] Fila de espera mantida (D7)
- [x] Ficha: labels, intervalos ESF/CIL, A5 subjetiva, encaminhamentos
- [x] Fluxo médico: agenda → pré-cadastro → ficha
- [x] Enforce de permissões no menu (Fase 1)
- [x] Remover mocks onde API existe / UI sem dados inventados
- [ ] Dashboard e algumas telas ainda sem API (mostram vazio, não fake)

## Fase 3 — P2 Financeiro *(em andamento)*

- [x] D9: receitas agregadas de consultas pagas (sem tabela `receitas`)
- [x] D10: despesas CRUD + resumo por período
- [x] APIs `/financeiro/resumo`, `/financeiro/relatorio`, `/financeiro/despesas`
- [x] Visão Geral e Relatório Financeiro com dados reais
- [x] Gráfico/barras comparativo de períodos
- [ ] Contas a pagar + inadimplentes (slice seguinte)
- [ ] Tela Atendimentos financeira real
- [ ] Dashboard com receita real

## Fase 4 — P3 SaaS Marag *(slice inicial)*

- [x] D11: isolamento por **banco por clínica** (conexão `central` + tenant `mysql`)
- [x] D12: 1 usuário = 1 clínica (users só no DB da clínica)
- [x] D13: provisionamento via `php artisan clinic:provision`
- [x] D14: white-label (nome/logo/cores); login/© Marag
- [x] D15: começar zerado (nova clínica sem migrar dados antigos)
- [x] Middleware `clinic` + header `X-Clinic-Slug`
- [x] Telão por canal `chamadas.pacientes.{slug}`
- [ ] Subdomínio por clínica / billing
- [ ] Painel Marag super-admin (lista clínicas na UI)

## Referências de produto

- Front: `listaToDo.mdx`, `instructions.mdx`
