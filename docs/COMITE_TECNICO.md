# Comitê Técnico Marag — playbook

Sistema de revisão contínua no Cursor: agentes especializados + Release Manager.

## Estrutura (Cursor Rules)

Arquivos em `.cursor/rules/` (formato `.mdc` — o Cursor não usa `.md` puro como rule):

| Arquivo | Papel | Quando |
|---------|--------|--------|
| `00-orquestrador.mdc` | Prompt mestre | Sempre |
| `produto-clinica.mdc` | Produto / decisões | Sempre |
| `architect.mdc` | Arquitetura | Diffs estruturais |
| `backend.mdc` + `laravel-api.mdc` | Laravel | `Back-end-clinica/**` |
| `frontend.mdc` + `vue-front.mdc` | Vue | `Front-end-clinica/**` |
| `security.mdc` + `seguranca-api.mdc` | DevSecOps | Auth, uploads, env |
| `qa.mdc` | QA Lead | Antes de “pronto” |
| `sre.mdc` | SRE / Railway | Deploy |
| `dba.mdc` | Banco / tenant | Migrations |
| `lgpd.mdc` | Privacidade saúde | Dados reais |
| `performance.mdc` | Performance | Queries / UX veloz |
| `ux.mdc` | UX clínica | Telas do dia a dia |
| `product.mdc` | Business value | Escopo / roadmap |
| `release.mdc` | GO / NO-GO | Deploy / piloto |
| `comite-indice.mdc` | Mapa rápido | Consulta |

## Como usar no chat

### Desenvolvimento do dia a dia
O orquestrador já está ativo. Ao pedir uma feature, o agente deve classificar riscos e acionar checklists das áreas tocadas.

### Review explícito (recomendado antes do Railway)

```
Rode o gate de release do Comitê Técnico Marag
para piloto 1 clínica no Railway.
Aplique release.mdc e consolide security, sre, qa, lgpd, frontend.
```

### Agente único

```
Atue como DevSecOps (security.mdc) e audite auth + upload + rate limit.
```

## Severidades

- **CRÍTICO** — bloqueia deploy
- **ALTO** — corrigir antes de clínica real
- **MÉDIO** — plano de dívida
- **BAIXO** — melhoria

## Gate mínimo piloto (1 empresa)

### Fase 1 código (feita)

1. Rate limit no login (`throttle:login`)
2. Sem vazamento de exceptions 500
3. Sem SVG no logo
4. Logs de chamada sem PII
5. `DEFAULT_CLINIC_SLUG` ignorado em production
6. JWT TTL 2h (`JWT_TTL_SECONDS=7200`)

### Ainda obrigatório antes do soft launch

1. ~~Front com `VITE_API_URL` no build~~ (template + `DEPLOY_RAILWAY.md`) — **aplicar no host**
2. ~~TrustProxies / HTTPS / CORS env~~ (código Fase 2)
3. Subir staging Railway e smoke test (`DEPLOY_RAILWAY.md`)
4. Backup MySQL + **1 restore testado** (`RUNBOOK_INCIDENTES.md`)
5. Termo/LGPD com a clínica
6. Happy path do `CHECKLIST_PILOTO.md` verde em staging

Enquanto staging/backup/LGPD abertos, Release Manager = **❌ NÃO AUTORIZADO** para dados reais.

## Relação com AGENTS.md

`AGENTS.md` = regras permanentes do repo.  
Comitê = papéis de review e gate de produção. Ambos se complementam.
