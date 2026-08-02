# Passo a passo — Deploy Marag (você no painel)

Repo único no GitHub.  
**API** = Railway (`Back-end-clinica`) · **Front** = Vercel (`Front-end-clinica`).

Código já preparado: `nixpacks.toml` (só PHP), `railway.toml`, docs.  
O que falta é **só no seu painel** (Railway / Vercel / MySQL).

---

## Antes de começar

- [ ] Conta Railway + GitHub conectado  
- [ ] Conta Vercel + mesmo repo  
- [ ] Decida: **reaproveitar** o projeto Railway atual **ou** apagar e criar outro (ambos ok)

Se o projeto atual estiver confuso → delete o projeto no Railway e siga da **Parte 1** do zero.

---

## Parte 1 — Railway: serviço da API

1. **New Project** → Deploy from GitHub → `Clinica-gerenciamento` (branch `main`).
2. No serviço criado:
   - **Settings → Root Directory** = `Back-end-clinica`
   - Salve / **Apply**
3. Confirme que o deploy usa um commit **igual ou depois** de `c0ec386` (tem `nixpacks.toml`).
4. **Start Command** (se pedir override):

```bash
php artisan serve --host=0.0.0.0 --port=$PORT
```

5. Healthcheck path: `/up` (se houver campo).

**Não** escolha a pasta do front neste serviço.

---

## Parte 2 — Railway: MySQL

1. No mesmo projeto: **+ New** → **Database** → **MySQL**.
2. Abra o MySQL → **Data** / Query / cliente externo e rode:

```sql
CREATE DATABASE IF NOT EXISTS marag_central CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS marag_clinic_piloto CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. Anote o **nome do serviço** MySQL no Railway (ex.: `MySQL`, `mysql`, `Postgres` não — tem que ser MySQL).  
   Esse nome entra nas referências `${{NomeDoServico.VAR}}`.

---

## Parte 3 — Variáveis da API (cole no serviço Laravel)

Em **Variables** do serviço da API, adicione (ajuste o nome `MySQL` se o seu serviço tiver outro nome):

### App

| Variável | Valor |
|----------|--------|
| `APP_NAME` | `Marag` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | *(gere local: `php artisan key:generate --show` — cole o `base64:...`)* |
| `APP_URL` | `https://SEU-SERVICO.up.railway.app` *(preencha depois do 1º deploy com domínio público)* |
| `LOG_LEVEL` | `warning` |
| `FILESYSTEM_DISK` | `public` |

### Sessão / cache (piloto — sem depender de tabelas ainda)

| Variável | Valor |
|----------|--------|
| `SESSION_DRIVER` | `file` |
| `CACHE_STORE` | `file` |
| `QUEUE_CONNECTION` | `sync` |
| `BROADCAST_CONNECTION` | `log` |

### JWT

| Variável | Valor |
|----------|--------|
| `JWT_KEY` | *(string longa aleatória — 64 hex; **não** reutilize chave antiga vazada)* |
| `JWT_TTL_SECONDS` | `7200` |

### Banco (referências Railway)

Substitua `MySQL` pelo nome real do serviço de banco:

| Variável | Valor |
|----------|--------|
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | `${{MySQL.MYSQLHOST}}` |
| `DB_PORT` | `${{MySQL.MYSQLPORT}}` |
| `DB_DATABASE` | `marag_clinic_piloto` |
| `DB_USERNAME` | `${{MySQL.MYSQLUSER}}` |
| `DB_PASSWORD` | `${{MySQL.MYSQLPASSWORD}}` |
| `CENTRAL_DB_HOST` | `${{MySQL.MYSQLHOST}}` |
| `CENTRAL_DB_PORT` | `${{MySQL.MYSQLPORT}}` |
| `CENTRAL_DB_DATABASE` | `marag_central` |
| `CENTRAL_DB_USERNAME` | `${{MySQL.MYSQLUSER}}` |
| `CENTRAL_DB_PASSWORD` | `${{MySQL.MYSQLPASSWORD}}` |

### CORS (preencha depois do Vercel)

| Variável | Valor |
|----------|--------|
| `CORS_ALLOWED_ORIGINS` | `https://seu-projeto.vercel.app` |

**Não** defina `DEFAULT_CLINIC_SLUG`.

4. **Redeploy** da API.

5. Abra a URL pública + `/up` → deve responder **200**.  
   Ex.: `https://xxxx.up.railway.app/up`

6. Atualize `APP_URL` com essa URL (sem `/api` no final).

---

## Parte 4 — Migrations + clínica piloto

No serviço da API → **Shell** / Console:

```bash
php artisan migrate --database=central --path=database/migrations/central --force
php artisan migrate --database=mysql --force
php artisan clinic:provision piloto "Clinica Piloto" --admin-email=admin@piloto.local --admin-password="TroqueEstaSenha9" --admin-name="Admin"
```

- Senha: **mínimo 8 caracteres**, diferente de `password`.
- Se `CREATE DATABASE` falhar no provision: os DBs do passo 2 já existem — use o mesmo slug `piloto` / database `marag_clinic_piloto` conforme o provisioner criar o registro central.

Guarde: **slug** `piloto` + e-mail + senha do admin.

---

## Parte 5 — Vercel (front)

1. Projeto com Root Directory = `Front-end-clinica`.
2. **Environment Variables**:

| Chave | Valor |
|-------|--------|
| `VITE_API_URL` | `https://SEU-SERVICO.up.railway.app/api` |

(obrigatório o `/api` no final)

3. Ambientes: Production + Preview.  
4. **Deploy / Redeploy** (build falha sem `VITE_API_URL`).
5. Copie a URL do front (ex. `https://clinica-gerenciamento.vercel.app`).
6. Volte no Railway → `CORS_ALLOWED_ORIGINS` = essa URL (sem barra no final) → redeploy API.

---

## Parte 6 — Smoke test

1. Abra o front na Vercel.  
2. Login: slug `piloto` + admin criado.  
3. Happy path curto: agenda → paciente → fila (telão off).  
4. Se 401/CORS: confira `VITE_API_URL`, `CORS_ALLOWED_ORIGINS` e `APP_URL`.

---

## Se o build Railway falhar de novo

| Sintoma | Ação |
|---------|------|
| Colisão `yarn/LICENSE` vs `composer` | Confirme Root = `Back-end-clinica` e commit com `nixpacks.toml` |
| `composer.json` not found | Root Directory errado |
| App sobe mas 500 | Falta `APP_KEY` / DB / migrate |
| Login CORS | `CORS_ALLOWED_ORIGINS` = URL exata do Vercel |

Detalhes técnicos: [`DEPLOY_RAILWAY.md`](DEPLOY_RAILWAY.md).

---

## O que o agente já fez no código

- [x] `nixpacks.toml` só PHP  
- [x] `railway.toml` sem yarn  
- [x] Push no `main`  
- [x] Docs de deploy / soft launch / termo LGPD rascunho  

## O que só você faz

- [ ] Railway root + MySQL + variables  
- [ ] `/up` verde  
- [ ] migrate + `clinic:provision`  
- [ ] Vercel `VITE_API_URL` + CORS  
- [ ] Login smoke  
- [ ] Termo LGPD assinado antes de dados reais (`TERMO_LGPD_PILOTO.md`)  
- [ ] Backup Opção B (dump diário + antes de migrate)

---

*Piloto 1 clínica · telão off · JWT TTL 2h.*
