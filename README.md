# Marag — Gestão de Clínica

SaaS de gestão para clínicas oftalmológicas: agenda, pacientes, ficha clínica, fila de espera, chamada em tempo real, financeiro e white-label por clínica.

**Marca do software:** Marag  
**Auth:** JWT custom  
**Multi-tenant:** banco MySQL **por clínica** + registro em `marag_central`

---

## Stack

| Camada | Tecnologia |
|--------|------------|
| Frontend | Vue 3 · Vite · Pinia · Tailwind · Laravel Echo |
| Backend | Laravel 12 · MySQL · JWT · Reverb (WebSocket) |
| Domínio | Oftalmologia (anamnese, acuidade, refração, biomicroscopia, prescrição) |

---

## Estrutura do repositório

```
Clinica Gestão/
├── Front-end-clinica/   # SPA Vue
├── Back-end-clinica/    # API Laravel
├── AGENTS.md
├── docs/                # Documentação completa
└── .cursor/rules/       # Comitê Técnico (Cursor)
```

Paths reais do código:

- **Back:** `Back-end-clinica/`
- **Front:** `Front-end-clinica/`

---

## Documentação (comece aqui)

| Documento | Para quê |
|-----------|----------|
| **[docs/README.md](docs/README.md)** | Índice de toda a documentação |
| **[docs/MANUAL_USUARIO.md](docs/MANUAL_USUARIO.md)** | Como usar no dia a dia (cadastros, agenda, fila, ficha, telão, financeiro) |
| **[docs/FUNCIONAMENTO_TECNICO.md](docs/FUNCIONAMENTO_TECNICO.md)** | Como o sistema funciona por dentro |
| **[docs/MENSAGENS_ERRO.md](docs/MENSAGENS_ERRO.md)** | Erros e o que fazer |
| **[docs/SETUP.md](docs/SETUP.md)** | Instalação e provisionamento de clínica |
| [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) | ADRs e módulos |
| [docs/SECURITY.md](docs/SECURITY.md) | Segurança |
| [docs/DEPLOY_RAILWAY.md](docs/DEPLOY_RAILWAY.md) | Staging Railway |
| [docs/ROADMAP.md](docs/ROADMAP.md) | Fases de entrega |
| [AGENTS.md](AGENTS.md) | Regras para IA / contribuidores |

---

## Módulos

### Operacionais (API + UI)

- Auth JWT + slug da clínica
- Pacientes (CRUD) + pré-cadastro no fluxo de atendimento
- Ficha clínica + impressão (completa e A5 subjetiva)
- Agenda (dia/semana/mês) com pagamento
- Fila de espera + telão (Reverb)
- Financeiro: resumo, despesas, relatório (receitas = consultas pagas)
- Parceiros, usuários, configuração de agendamento
- White-label (nome, logo, cores) por clínica

### Ainda parcial (UI vazia, sem inventar dados)

- Dashboard, Fluxo diário, Atendimentos financeiros, Consultas atendidas e alguns relatórios — aguardando API; listas/KPIs em zero/vazio
- Inadimplentes / permissões granulares

---

## Setup local (resumo)

Detalhes: **[docs/SETUP.md](docs/SETUP.md)**

### Pré-requisitos

PHP 8.2+ · Composer · Node 20+ · MySQL 8

### Backend

```powershell
cd "Back-end-clinica"
composer install --prefer-dist
php artisan storage:link --force   # ou: php artisan marag:doctor
# configurar .env (DB_* + CENTRAL_DB_* + JWT_KEY + APP_KEY)
php artisan migrate --database=central --path=database/migrations/central --force
php artisan clinic:provision demo "Clínica Demo" --admin-email=admin@demo.local --admin-password=password
php artisan serve
# Telão: php artisan reverb:start
```

### Frontend

```powershell
cd "Front-end-clinica"
npm install
npm run dev
```

- Painel: `http://localhost:5173`
- Login: clínica `demo` · `admin@demo.local` · `password`
- Telão: `http://localhost:5173/consultas/telao-chamada?clinic=demo`

---

## Fluxo clínico (resumo)

```
Agendar → Confirmar chegada → Fila → Chamar (telão)
  → Pré-cadastro → Ficha clínica → Consulta encerrada
```

Passo a passo: [Manual do usuário](docs/MANUAL_USUARIO.md).

---

## Decisões de produto (travadas)

| Tema | Decisão |
|------|----------|
| Marca | Marag |
| Auth | JWT endurecido |
| Isolamento | 1 banco MySQL por clínica |
| Usuário | 1 usuário = 1 clínica |
| Onboarding | Provisionamento manual (`clinic:provision`) |
| White-label | Nome/logo/cores; login/© Marag |
| Telão | Público (sem login), canal por slug |
| Receitas | Agregadas de consultas pagas |

---

## Contribuição

1. Ler `AGENTS.md` e `docs/SECURITY.md`
2. Não abrir rotas de negócio sem auth (exceto branding/telão conforme desenho)
3. Não usar `migrate:fresh` em banco com dados reais
4. Sempre enviar / respeitar `X-Clinic-Slug` em APIs de tenant
