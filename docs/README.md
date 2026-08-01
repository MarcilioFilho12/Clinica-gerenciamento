# Documentação Marag

Índice da pasta `docs/`.

## Para quem usa o sistema

| Documento | Conteúdo |
|-----------|----------|
| [MANUAL_USUARIO.md](MANUAL_USUARIO.md) | Login, perfis, agenda, cadastros, fila, ficha, telão, financeiro, boas práticas |
| [MENSAGENS_ERRO.md](MENSAGENS_ERRO.md) | Códigos HTTP, toasts e mensagens da API — o que significam e o que fazer |
| [CHECKLIST_PILOTO.md](CHECKLIST_PILOTO.md) | Roteiro de aceite para deploy e fase de testes (1 clínica) |
| [PLANO_SOFT_LAUNCH.md](PLANO_SOFT_LAUNCH.md) | Plano 10–14 dias (5,5→7,5+) com GATEs de decisão |
| [TERMO_LGPD_PILOTO.md](TERMO_LGPD_PILOTO.md) | Rascunho de termo LGPD para assinatura da clínica |

## Para quem configura / desenvolve

| Documento | Conteúdo |
|-----------|----------|
| [SETUP.md](SETUP.md) | Instalação local, MySQL central/tenant, provisionar clínica, Reverb |
| [DEPLOY_RAILWAY.md](DEPLOY_RAILWAY.md) | Staging/produção Railway + front static (PRG Fase 2) |
| [RUNBOOK_INCIDENTES.md](RUNBOOK_INCIDENTES.md) | API/DB down, rollback, backup/restore |
| [FUNCIONAMENTO_TECNICO.md](FUNCIONAMENTO_TECNICO.md) | Arquitetura runtime, JWT, multi-DB, fluxos, APIs, telão |
| [ARCHITECTURE.md](ARCHITECTURE.md) | Bounded contexts e ADRs |
| [SECURITY.md](SECURITY.md) | Baseline de segurança |
| [ROADMAP.md](ROADMAP.md) | Fases 0–4 e status |
| [COMITE_TECNICO.md](COMITE_TECNICO.md) | Comitê de agentes Cursor (Architect → Release GO/NO-GO) |

## Na raiz do repositório

| Arquivo | Conteúdo |
|---------|----------|
| [../README.md](../README.md) | Visão geral do produto e links |
| [../AGENTS.md](../AGENTS.md) | Regras para agentes de IA / contribuidores |
| [../.cursor/rules/](../.cursor/rules/) | Rules do Comitê Técnico (`.mdc`) |

## Fluxo recomendado de leitura

1. **Operador da clínica** → Manual do usuário → Mensagens de erro  
2. **TI / setup** → Setup → Funcionamento técnico  
3. **Desenvolvimento** → Architecture → Security → Roadmap → AGENTS  
4. **Deploy / piloto** → [PLANO_SOFT_LAUNCH](PLANO_SOFT_LAUNCH.md) → [DEPLOY_RAILWAY](DEPLOY_RAILWAY.md) → Checklist piloto → Runbook → Release Manager
