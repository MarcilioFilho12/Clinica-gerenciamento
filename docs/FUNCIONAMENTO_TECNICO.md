# Funcionamento técnico — Marag

Visão técnica de como o sistema funciona: stacks, multi-tenant, auth, fluxos clínicos, financeiro, telão e APIs.

---

## 1. Visão geral

```
Browser (Vue 3 SPA)
   │  HTTP JSON + header X-Clinic-Slug + Bearer JWT
   ▼
Laravel API (:8000/api)
   │
   ├─ conexão `central` → MySQL marag_central (tabela clinics)
   └─ conexão `mysql`   → MySQL marag_clinic_{slug} (dados da clínica)
   │
   └─ Reverb (:8080) → WebSocket → Telão (Echo)
```

| Camada | Tecnologia |
|--------|------------|
| Front | Vue 3, Vite, Pinia, Tailwind, Laravel Echo |
| Back | Laravel 12, MySQL 8, JWT (firebase/php-jwt), Reverb |
| Auth | JWT custom (`App\Custom\Jwt`) + middlewares `clinic`, `jwt`, `profile` |

Paths de código:

- Back: `Back-end-clinica/`
- Front: `Front-end-clinica/`

---

## 2. Multi-tenant (D11 — banco por clínica)

### Modelo

- **Central** (`CENTRAL_DB_*`): registro de clínicas (slug, database_name, branding)
- **Tenant**: cada clínica tem um database `marag_clinic_{slug}`
- **Não** há `clinic_id` nas tabelas de negócio — o isolamento é a conexão

### Resolução do tenant

Middleware `ResolveClinicTenant` (`clinic`):

1. Header `X-Clinic-Slug`, ou
2. Query `?clinic=`, ou
3. Claim JWT `clinic_slug`, ou
4. Fallback `DEFAULT_CLINIC_SLUG` no `.env`

Depois: carrega `Clinic` no central → `TenantContext::set()` → troca `database.connections.mysql.database` e reconecta.

### Provisionamento

```bash
php artisan migrate --database=central --path=database/migrations/central
php artisan clinic:provision {slug} "{Nome}" --admin-email=... --admin-password=...
```

O comando:

1. Cria o database MySQL
2. Insere linha em `clinics`
3. Roda migrations do tenant
4. Cria usuário admin (profile 1)

### Front

- `localStorage.clinic_slug` + opcional `VITE_CLINIC_SLUG`
- Interceptor em `src/services/axios.js` envia `X-Clinic-Slug`
- Store Pinia `clinic` aplica branding (CSS vars `--clinic-primary`, title)

---

## 3. Autenticação e RBAC

### Login

`POST /api/auth` (dentro do middleware `clinic`)

- Body: `{ email, senha }`
- Valida usuário **no DB da clínica**
- Resposta: `token`, `user` (claims), `clinic` (branding)

### JWT claims

```json
{
  "id": 1,
  "name": "...",
  "email": "...",
  "profile_id": 1,
  "clinic_slug": "demo",
  "clinic_nome": "Clínica Demo"
}
```

Sem hash de senha / User completo no token.

### Middlewares

| Alias | Função |
|-------|--------|
| `clinic` | Resolve e aplica DB da clínica |
| `jwt` | Bearer obrigatório; confere `clinic_slug` do token |
| `profile:admin,recepcao,...` | Autoriza por `profile_id` |

Perfis (`App\Support\Profiles`):

| ID | Nome |
|----|------|
| 1 | Administrador |
| 2 | Recepção |
| 3 | Profissional |

Front: `meta.profiles` no router + filtro do Sidebar + `auth.hasProfile(...)`.

### Exceção pública

- `GET /api/clinic/branding` — sem `clinic` middleware (lê central)
- Rota Vue `/consultas/telao-chamada` — sem auth (WebSocket público por slug)

---

## 4. Status de consulta (IDs)

Tabela `situacoes` compartilhada semanticamente (seed nas migrations):

| ID | Uso em consulta |
|----|-----------------|
| 1 | Agendada (criação) |
| 4 | Encerrada / realizada |
| 5 | Cancelada |
| 6 | Em atendimento (após “Chamar”) |

Campos extras: `chegada_em`, `codigo_chegada`, `pago`, `forma_pagamento`, `valor`, `prioridade`, `parceiro_id`.

---

## 5. Fluxo clínico (estado)

```
store consulta          situacao=1
        │
confirmar-chegada       chegada_em + codigo_chegada  → entra na fila
        │
POST .../chamar         situacao=6 + broadcast PacienteChamado
        │
pré-cadastro (front)    PUT paciente
        │
POST fichas-clinicas    se situacao=6 → situacao=4
```

Utilitário front: `src/utils/fluxoAtendimento.js`

- `urlPreCadastro` / `urlFichaClinica`
- `prepararConsultaParaAtendimento` (chegada se preciso + chamar)

---

## 6. Ficha clínica (modelo de dados)

`cadastros_fichas_clinicas` + filhos:

- `anamneses`
- `acuidades_visuais`
- `refracoes` (tipos: estática / dinâmica / subjetiva; olhos OD/OE)
- `biomicroscopias`
- `prescricoes` (inclui `encaminhamento`, `proximo_controle`)

API:

- `POST /pacientes/{id}/fichas-clinicas`
- `GET|PUT /fichas-clinicas/{id}`

---

## 7. Financeiro

### Receitas (D9)

Agregação de `consultas` onde `pago = true` e `valor > 0` (não canceladas).

### Despesas (D10)

Tabela `despesas` no DB da clínica.

### APIs (staff: admin + recepção)

| Método | Rota |
|--------|------|
| GET | `/financeiro/resumo?periodo=` |
| GET | `/financeiro/relatorio?periodo=` |
| GET/POST | `/financeiro/despesas` |
| PUT/DELETE | `/financeiro/despesas/{id}` |

Períodos: `hoje`, `semana`, `mes`, `trimestre`, `semestre`, `ano`.

---

## 8. Telão (tempo real)

### Back

- Evento `App\Events\PacienteChamado` (`ShouldBroadcastNow`)
- Canal: `chamadas.pacientes.{slug}`
- Payload: paciente, profissional, `codigo_chegada`, horários

### Front

- `src/services/echo.js` → Laravel Echo + Reverb/Pusher protocol
- `TelaoChamada.vue` assina o canal do slug (`?clinic=` ou localStorage)
- Som de chamada local (`painel-chamada.mp3`)

### Env relevante

`BROADCAST_DRIVER=reverb`, `REVERB_APP_*`, `VITE_REVERB_*` no front se aplicável.

---

## 9. Mapa de rotas API (resumo)

### Público

- `GET /clinic/branding?slug=`

### Com `clinic` + sem JWT

- `POST /auth`

### Com `clinic` + `jwt` + perfis

Grupos em `routes/api.php`:

- Clínico (1,2,3): pacientes, fichas, consultas, fila, config agendamento (leitura)
- Staff (1,2): parceiros, config agendamento (escrita), financeiro
- Admin (1): usuários, `PUT /clinic/branding`

---

## 10. Front — stores e serviços

| Arquivo | Papel |
|---------|-------|
| `services/axios.js` | baseURL, Bearer, X-Clinic-Slug, logout em 401 |
| `stores/auth.js` | token, user, profiles |
| `stores/clinic.js` | slug, branding, CSS vars |
| `utils/fluxoAtendimento.js` | URLs e preparação de atendimento |
| `utils/refracaoOptions.js` | opções ESF/CIL/EIXO/ADD |

---

## 11. Decisões de produto (referência rápida)

| ID | Decisão |
|----|---------|
| D1 | Marca Marag |
| D2 | JWT endurecido |
| D3→F4 | Multi-tenant |
| D4 | Telão público |
| D5 | RBAC básico 3 perfis |
| D7 | Manter fila |
| D9 | Receitas = consultas pagas |
| D10 | Despesas + resumo |
| D11 | Banco por clínica |
| D12 | 1 user = 1 clínica |
| D13 | Provisionamento manual |
| D14 | White-label parcial |
| D15 | Novas clínicas zeradas |

---

## 12. O que ainda é parcial (sem inventar dados)

- Dashboard, fluxo diário, atendimentos financeiros e alguns relatórios: UI com **zeros/listas vazias** até haver API
- Inadimplentes / contas a pagar / permissões granulares

Fonte de verdade do status: [ROADMAP.md](ROADMAP.md).

---

## Ver também

- [Manual do usuário](MANUAL_USUARIO.md)
- [Mensagens de erro](MENSAGENS_ERRO.md)
- [Setup](SETUP.md)
- [Segurança](SECURITY.md)
