# Plano Soft Launch — Marag (5,5 → 7,5+)

**Objetivo:** piloto assistido com **1 clínica**, staging estável, dados reais só após GO do Release Manager.  
**Prazo alvo:** 10–14 dias úteis.  
**Fora deste plano:** billing, self-signup, subdomínio, painel Marag, contas a pagar.

Cada **GATE** exige resposta do Marcilio (ou dono do produto) antes de seguir. Sem resposta → não avançar o item.

---

## Como usar

1. Responder as perguntas do **GATE 0** (hoje).
2. Executar Dias 1–3 sem ambiguidade.
3. Parar em cada GATE seguinte até decidir.
4. No fim: Release Manager → ✅ / ⚠️ / ❌.

---

## GATE 0 — Escopo e risco (antes de qualquer código)

Responda **antes** de começar a sprint:

| # | Pergunta | Opções típicas | Impacto |
|---|----------|----------------|---------|
| G0.1 | O soft launch é **staging com dados fictícios** ou **1 clínica real**? | A) só fake · B) clínica real assistida | Define se LGPD/backup bloqueiam ou não |
| G0.2 | Telão de chamada entra no piloto? | A) sim · B) não (recomendado se B em G0.1) | Canal público + PII |
| G0.3 | Onde sobe a API? | A) Railway · B) outro VPS · C) só local | SRE e HTTPS |
| G0.4 | Front: hospedagem? | A) Railway static / Cloudflare / Netlify · B) mesmo host da API | `VITE_API_URL` obrigatório |
| G0.5 | Quem opera o piloto na clínica? | Nome + perfil (admin/recepção/médico) | Treino e suporte |
| G0.6 | Aceita esconder telas incompletas do menu? | A) sim (recomendado) · B) deixar visíveis | UX e percepção de “bug” |
| G0.7 | Backup: quem executa e com que frequência? | Ex.: diário manual / job Railway | Bloqueia dados reais |

**Decisão travada se não responder:** tratar como **staging + dados fake + telão off + esconder telas incompletas**.

### GATE 0 — respostas (2026-08-01)

| # | Decisão |
|---|--------|
| G0.1 | **1 clínica real** (piloto assistido) |
| G0.2 | Telão **off** |
| G0.3 | API **Railway** |
| G0.4 | Front **Vercel** |
| G0.5 | Operador = **admin atual** |
| G0.6 | Esconder telas incompletas = **sim** |
| G0.7 | Backup **Opção B**: dump diário (central + tenant, 14 dias, off-site) **+ dump antes de migrate/deploy** + 1 restore testado antes do go-live |

Implicação: termo LGPD + backup/restore são **bloqueadores** para GO com dados reais.

---

## Semana 1 — Fechar CRÍTICOS (nota → ~6,5)

### Dia 1 — Segredos, build e higiene

| ID | Tarefa | Dono | Feito |
|----|--------|------|-------|
| S1 | Remover `Back-end-clinica/env.mdx` do Git; rotacionar `JWT_KEY` / Reverb se já vazou | Dev | [x] removido do tracking — **rotacionar chaves no Railway no 1º deploy** |
| S2 | Garantir que `npm run build` **falha** sem `VITE_API_URL` (ligar `build:check-env`) | Dev | [x] |
| S3 | Commitar artefatos de deploy ainda untracked (`railway.toml`, doctor, docs de deploy se faltarem) | Dev | [ ] (pendente commit sob pedido; arquivos criados) |
| S4 | Remover/ocultar do menu: Fluxo Diário, Consultas Atendidas (sem API), Relatório Consultas mock, etc. | Dev | [x] + Atendimentos financeiro + telão |

#### GATE 1 — Segredos e ambiente

| # | Pergunta | Por quê |
|---|----------|---------|
| G1.1 | As chaves em `env.mdx` já foram usadas em algum ambiente compartilhado? | Se sim → rotacionar obrigatório |
| G1.2 | URL definitiva da API de staging? | Build do front depende disso |
| G1.3 | CORS: lista fechada de origins do front? | Não usar `*` em staging/prod |

### GATE 1 — respostas (2026-08-01)

| # | Decisão |
|---|--------|
| G1.1 | **Sim** — rotacionar `JWT_KEY` (e Reverb se aplicável) agora e de novo no 1º deploy Railway |
| G1.2 | URL API Railway **ainda não** — Dias 4–5 pausados até existir |
| G1.3 | Domínio Vercel / CORS **ainda não** |

#### GATE 2 — respostas (2026-08-01)

| # | Decisão |
|---|--------|
| G2.1 | **B** — sem revogação/blacklist no piloto |
| G2.2 | **TTL 2 horas** (`JWT_TTL_SECONDS=7200`) |
| G2.3 | **A** — token no `localStorage` |

---

### Dia 2 — Segurança operacional (auth / tenant)

| ID | Tarefa | Feito |
|----|--------|-------|
| S5 | Bloquear login de usuário inativo (`situacao_id`) | [x] + JWT middleware |
| S6 | Aplicar `throttle:api` (ou equivalente) nas rotas autenticadas | [x] |
| S7 | Remover `paciente_nome` / PII dos logs de chamada (só IDs + slug) | [x] |
| S8 | Senha mínima ≥ 8 (API + front); forçar troca da senha `password` no provisionamento | [x] |

#### GATE 2 — Sessão JWT

| # | Pergunta | Opções | Recomendação piloto |
|---|----------|--------|---------------------|
| G2.1 | Precisa **revogar JWT** ao trocar senha / inativar? | A) sim (versão de token / blacklist) · B) TTL curto basta (ex. 1–2h no piloto) | B no piloto; A antes de várias clínicas |
| G2.2 | TTL do JWT no piloto? | 1h / 2h / 4h (atual) | 2h se G2.1 = B |
| G2.3 | Token continua no `localStorage`? | A) manter · B) migrar para cookie httpOnly | A no piloto (menor escopo); B na Fase SaaS |

**Sem resposta G2:** manter TTL 4h, sem blacklist — documentar como ressalva ⚠️.

---

### Dia 3 — Agenda, LGPD e telão

| ID | Tarefa | Feito |
|----|--------|-------|
| S9 | Lock/transação (ou unique index) para evitar double-book do mesmo profissional/horário | [x] |
| S10 | Se telão **off** (G0.2=B): esconder rota/menu e não exigir Reverb no piloto | [x] |
| S11 | Se telão **on**: minimizar payload (iniciais / código de chamada, sem nome completo se possível) | [x] N/A (telão off) |
| S12 | Rascunho de termo de uso / aviso LGPD para a clínica piloto (1 página) | [x] `docs/TERMO_LGPD_PILOTO.md` |

#### GATE 3 — Telão e dados de saúde

| # | Pergunta | Opções |
|---|----------|--------|
| G3.1 | Telão fica em TV da recepção (rede local) ou URL pública na internet? | Local · Pública |
| G3.2 | O que pode aparecer na TV? | Nome completo · Primeiro nome · Só código/senha |
| G3.3 | Clínica já assinou (ou vai assinar) termo de tratamento de dados de saúde? | Sim com data · Ainda não (bloqueia dados reais) |
| G3.4 | Precisa de exclusão/anonimização de paciente no piloto? | Sim · Não (só soft-delete depois) |

### GATE 3 — respostas (2026-08-01)

| # | Decisão |
|---|--------|
| G3.1–G3.2 | **N/A** — telão off no piloto |
| G3.3 | Termo LGPD **ainda não** → Release ❌ para dados reais até assinar |
| G3.4 | Pendente (perguntar depois do termo) |

**Se G3.3 = Ainda não** → Release = ❌ para dados reais (só fake).

---

### Dias 4–5 — Staging no ar + backup

| ID | Tarefa | Feito |
|----|--------|-------|
| S13 | Subir API Railway (ou host escolhido) com `APP_DEBUG=false`, sem `DEFAULT_CLINIC_SLUG` | [ ] |
| S14 | Build front com `VITE_API_URL` da staging; smoke login | [ ] |
| S15 | Health: documentar `/up` + checagem manual central DB + 1 tenant | [ ] |
| S16 | Backup MySQL (central + tenant) + **1 restore testado** (anotar data/hora no runbook) | [ ] |
| S17 | `php artisan marag:doctor --fix` no ambiente de staging | [ ] |

#### GATE 4 — Operação

| # | Pergunta | Opções |
|---|----------|--------|
| G4.1 | Runtime da API: aceita `php artisan serve` só no piloto ou exige PHP-FPM/Nginx já? | Serve (só staging curto) · FPM/Nginx já |
| G4.2 | Logo da clínica: aceita sumir em redeploy (disco efêmero) ou já precisa storage persistente? | Aceita · Precisa volume/S3 |
| G4.3 | Quem tem acesso SSH/painel do host e ao dump do banco? | Lista de nomes |
| G4.4 | Em incidente: rollback = redeploy da imagem anterior ou restore de DB? | Documentar no runbook |

---

## Semana 2 — Qualidade e aceite (nota → ~7,5)

### Dias 6–7 — Testes e CI mínimos

| ID | Tarefa | Feito |
|----|--------|-------|
| Q1 | Corrigir migrations incompatíveis com SQLite **ou** rodar testes em MySQL no CI | [ ] |
| Q2 | Suite auth/JWT/upload verde (mínimo) | [ ] |
| Q3 | 1 teste de isolamento: 2 clínicas, slug A não lê dados de B | [ ] |
| Q4 | Workflow CI básico (testes PHP + `npm run build` com env fake de check) | [ ] |

#### GATE 5 — Barra de qualidade

| # | Pergunta | Opções | Recomendação |
|---|----------|--------|--------------|
| G5.1 | Soft launch exige CI verde obrigatório? | Sim · Não (só local) | Sim |
| G5.2 | Precisa de teste E2E (Playwright) no happy path agora? | Sim · Não | Não no piloto; checklist manual basta |
| G5.3 | Falha de teste de upload por falta de GD no CI: instalar GD ou mockar? | GD · Mock | GD se possível |

---

### Dias 8–9 — Happy path + perfis

| ID | Tarefa | Feito |
|----|--------|-------|
| Q5 | Executar `docs/CHECKLIST_PILOTO.md` completo em **staging** (3 perfis) | [ ] |
| Q6 | Corrigir bugs bloqueantes do happy path (4xx/5xx) | [ ] |
| Q7 | Conferir menu sem itens fora do piloto | [ ] |
| Q8 | Provisionar clínica piloto com senha forte (não `password`) | [ ] |

#### GATE 6 — Produto / o que a clínica paga

| # | Pergunta | Opções |
|---|----------|--------|
| G6.1 | Financeiro do piloto: só “consulta paga + despesas” basta? | Sim · Precisa recibo/PDF já |
| G6.2 | Dashboard zerado é aceitável no soft launch? | Sim (esconder) · Precisa receita real |
| G6.3 | Parceiros/convênios entram no piloto? | Sim · Não |
| G6.4 | Impressão A5 / ficha completa será usada no dia 1? | Sim · Depois |
| G6.5 | Suporte: canal WhatsApp / e-mail / presença presencial nos primeiros 3 dias? | Definir |

---

### Dia 10 — Gate de Release

| Área | Meta mínima piloto | Nota alvo |
|------|-------------------|-----------|
| Segurança | Sem segredo no Git; throttle login+API; sem PII em log; build com API URL | ≥ 7 |
| SRE | Staging no ar; backup+1 restore; doctor ok | ≥ 7 |
| QA | Checklist piloto verde; auth tests verdes | ≥ 7 |
| LGPD | Termo assinado **se** dados reais; telão conforme G3 | ≥ 7 ou N/A fake |
| Frontend | Menu limpo; sem localhost no bundle prod | ≥ 7 |
| Negócio | Happy path resolve o dia da clínica | ≥ 7 |

#### GATE 7 — GO / NO-GO (perguntas finais)

| # | Pergunta | Se “Não” |
|---|----------|----------|
| G7.1 | Backup + restore foram feitos e anotados? | ❌ dados reais |
| G7.2 | Bundle de staging **não** contém `localhost`? | ❌ |
| G7.3 | Happy path checklist 100% verde? | ❌ ou ⚠️ só se bugs MÉDIO documentados |
| G7.4 | Termo LGPD assinado (se clínica real)? | ❌ dados reais |
| G7.5 | Telão está off **ou** payload mínimo aprovado? | ❌ se público com nome completo |
| G7.6 | Há plano de suporte nos 3 primeiros dias? | ⚠️ |
| G7.7 | Autoriza Release Manager a marcar **⚠️ COM RESSALVAS** com a lista abaixo? | Precisa lista explícita |

**Ressalvas aceitáveis no piloto assistido (exemplo):**
- Sem billing / subdomínio
- Dashboard oculto ou zerado
- Sem revogação JWT (TTL curto)
- Logo pode sumir em redeploy
- Telão off

**Não aceitável como ressalva:**
- Segredo no repositório
- Build apontando localhost
- Sem backup/restore
- Dados reais sem termo
- Happy path quebrado

---

## Ordem sugerida das perguntas (resumo rápido)

Responder nesta ordem quando estiver com o time:

1. **G0** — fake vs real, telão, host, esconder telas  
2. **G1** — URLs, CORS, rotação de chaves  
3. **G2** — TTL / revogação JWT  
4. **G3** — o que aparece no telão + LGPD  
5. **G4** — serve vs FPM, storage logo, quem acessa infra  
6. **G5** — CI obrigatório?  
7. **G6** — recibo, dashboard, suporte  
8. **G7** — GO / ⚠️ / ❌  

---

## Critério de sucesso (nota ~7,5)

- [ ] Soft launch em staging estável  
- [ ] 1 clínica operando o happy path sem erro bloqueante  
- [ ] Release Manager ≠ ❌  
- [ ] Dívidas ALTO restantes listadas com dono e prazo (pós-piloto)  

## Pós-piloto (não misturar nesta sprint)

- Billing + planos  
- Subdomínio / painel Marag  
- Cookie httpOnly + revogação JWT  
- Storage persistente de logo  
- E2E Playwright  
- Contas a pagar / inadimplência  
- Auditoria clínica/financeira  

---

## Registro de decisões

Preencher conforme for respondendo os GATEs:

| Gate | Data | Decisão | Quem |
|------|------|---------|------|
| G0 | 2026-08-01 | Clínica real; telão off; Railway+Vercel; admin atual; menu limpo; backup Opção B | Marcilio |
| G1 | 2026-08-01 | G1.1 chaves já usadas → **rotacionar obrigatório**; G1.2 URL Railway **ainda não**; G1.3 Vercel/CORS **ainda não** | Marcilio |
| G2 | 2026-08-01 | G2.1 **B** (sem blacklist); G2.2 **TTL 2h**; G2.3 **A** (localStorage) | Marcilio |
| G3 | 2026-08-01 | Telão off (G0.2); termo LGPD **ainda não** (bloqueia GO com dados reais) | Marcilio |
| G4 | | | |
| G5 | | | |
| G6 | | | |
| G7 | | Deploy: ✅ / ⚠️ / ❌ | |

---

*Documento do Comitê Técnico Marag — alinhado a `CHECKLIST_PILOTO.md`, `SECURITY.md`, `DEPLOY_RAILWAY.md`, `RUNBOOK_INCIDENTES.md` e `release.mdc`.*
