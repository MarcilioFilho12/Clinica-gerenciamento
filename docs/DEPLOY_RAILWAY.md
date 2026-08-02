# Deploy staging — Marag (Railway + front static)

Piloto: **1 clínica**. Telão/Reverb **off** no primeiro mês.  
Pré-requisito: **PRG Fase 1** (throttle, erros, SVG, JWT TTL, slug) já aplicada.

## Arquitetura alvo (staging)

```
Browser (Vue build)
   │  HTTPS
   ▼
Front static (Railway static / Cloudflare Pages / Vercel)
   │  VITE_API_URL → https://api-xxx.up.railway.app/api
   ▼
Laravel API (Railway service)  ← TrustProxies + force HTTPS
   │
   ├─ MySQL: marag_central
   └─ MySQL: marag_clinic_{slug}   (criar na mão se CREATE DATABASE falhar)
```

## 1. MySQL no Railway

1. Crie um plugin **MySQL**.
2. Crie **dois databases** no mesmo servidor (cliente MySQL / query):
   - `marag_central`
   - `marag_clinic_piloto` (ou o slug escolhido)
3. Se o user Railway **não** puder `CREATE DATABASE`, **não** use `clinic:provision` com create automático — crie o DB antes e ajuste o provisioner / rode migrations no DB já existente.

### Migrations central

No serviço da API (root = `Back-end-clinica`):

```bash
php artisan migrate --database=central --path=database/migrations/central --force
```

### Provisionar clínica (quando CREATE DATABASE ok)

```bash
php artisan clinic:provision piloto "Clínica Piloto" \
  --admin-email=admin@clinica.com \
  --admin-password='SenhaForteAqui' \
  --admin-name="Admin"
```

Se CREATE falhar: crie `marag_clinic_piloto`, registre em `marag_central.clinics`, aponte a conexão e rode:

```bash
php artisan migrate --database=mysql --force
```

## 2. Serviço API (Railway)

- **Root Directory:** `Back-end-clinica` (obrigatório — monorepo com o front)
- Builder: **NIXPACKS** + arquivo `Back-end-clinica/nixpacks.toml` (`providers = ["php"]`)
  - Impede `yarn install` / `yarn build` na API (evita colisão `yarn/LICENSE` vs `composer/LICENSE`)
  - Front Vue sobe só na **Vercel**
- **Start command:**

```bash
php artisan serve --host=0.0.0.0 --port=$PORT
```

(Opcional após o 1º deploy estável: `php artisan marag:doctor --fix &&` antes do `serve`.)

Variáveis mínimas:

| Var | Valor |
|-----|--------|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | `php artisan key:generate --show` |
| `APP_URL` | `https://<api>.up.railway.app` |
| `JWT_KEY` | string longa aleatória (nunca a do `env.mdx` antigo) |
| `JWT_TTL_SECONDS` | `7200` |
| `DB_*` / `CENTRAL_DB_*` | do plugin MySQL |
| `FILESYSTEM_DISK` | `public` |
| `CORS_ALLOWED_ORIGINS` | URL https do front Vercel (sem `*`) |
| `LOG_LEVEL` | `warning` ou `error` |

**Não** definir `DEFAULT_CLINIC_SLUG` em production.

Health check: `GET /up`

Storage logos (após o serviço subir):

```bash
php artisan marag:doctor --fix
```

Em disco efêmero do Railway, logos somem no redeploy — para piloto curto ok; depois use S3/R2.

### Se o build ainda puxar Yarn

1. Confirme que `nixpacks.toml` está no **GitHub** (commit + push).
2. No painel: Build Command = só `composer install --no-dev --optimize-autoloader --ignore-platform-reqs`
3. Redeploy.

## 3. Front (static)

Root: `Front-end-clinica`

Build:

```bash
# .env.production (ou vars do painel)
VITE_API_URL=https://<api>.up.railway.app/api

npm ci   # ou yarn
npm run build
```

Publique a pasta `dist/` (Pages / Vercel / Railway static).

Checklist pós-build: no DevTools, requests devem ir para a URL da API (nunca `localhost:8000`).

## 4. Rollback

1. No Railway / Git: redeploy do **commit anterior** da API.  
2. Front: republicar `dist` do commit anterior.  
3. DB: **não** rode migrate down em pânico — use backup (ver runbook).

## 5. Smoke test staging

1. `GET https://api.../up` → 200  
2. Login com slug + admin  
3. Upload logo → URL `/storage/...` abre (ou documentar limitação de disco)  
4. Happy path resumido do `CHECKLIST_PILOTO.md`  
5. 6º login falho/min → 429  

## 6. Fora deste estágio

- Domínio custom / Cloudflare (opcional)  
- Reverb  
- CI completo  
- Soft launch com dados reais (Fases 3–6)

Ver também: [RUNBOOK_INCIDENTES.md](RUNBOOK_INCIDENTES.md) · [SETUP.md](SETUP.md) · [SECURITY.md](SECURITY.md)
