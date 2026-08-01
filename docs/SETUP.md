# Setup local — Marag

Guia de instalação e subida do ambiente. Uso do dia a dia: [MANUAL_USUARIO.md](MANUAL_USUARIO.md).

---

## Pré-requisitos

- PHP 8.2+ com extensões: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`
- Composer
- Node.js 20+ (npm ou yarn)
- MySQL 8 (Windows: serviço `MySQL80` é comum)
- Opcional para telão: portas livres `8000` (API), `5173` (front), `8080` (Reverb)

---

## 1. Backend

Path: `Back-end-clinica/` (onde está o `artisan`)

```powershell
cd "Back-end-clinica"
composer install --prefer-dist
php artisan storage:link --force
# ou: php artisan marag:doctor
```

### 1.1 Arquivo `.env`

Use `env.mdx` como referência. Crie/ajuste `.env` com no mínimo:

```env
APP_KEY=          # php artisan key:generate
JWT_KEY="sua-chave-secreta"

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sturmerlocaldb
DB_USERNAME=root
DB_PASSWORD=sua_senha

CENTRAL_DB_HOST=127.0.0.1
CENTRAL_DB_PORT=3306
CENTRAL_DB_DATABASE=marag_central
CENTRAL_DB_USERNAME=root
CENTRAL_DB_PASSWORD=sua_senha

DEFAULT_CLINIC_SLUG=demo

# JWT (PRG 1.6 — default 4h se omitido)
JWT_TTL_SECONDS=14400

BROADCAST_DRIVER=reverb
# REVERB_* conforme env.mdx
```

> Em **production**: não defina `DEFAULT_CLINIC_SLUG` (PRG 1.5). Informe sempre o slug no login / header.
> `DB_DATABASE` no `.env` é só default; em runtime o tenant **troca** o database para `marag_clinic_{slug}`.

### 1.2 Banco central

Crie o database `marag_central` no MySQL (Workbench, HeidiSQL ou CLI) e rode:

```powershell
php artisan migrate --database=central --path=database/migrations/central --force
```

### 1.3 Provisionar uma clínica (obrigatório — D15 zerado)

```powershell
php artisan clinic:provision demo "Clínica Demo" --admin-email=admin@demo.local --admin-password=password --admin-name="Admin Demo"
```

Isso cria:

- Registro em `marag_central.clinics`
- Database `marag_clinic_demo` com todas as migrations
- Usuário admin no tenant

Novas clínicas = novo comando com outro slug (não migra dados antigos).

### 1.4 Subir API

```powershell
php artisan serve
```

API: `http://127.0.0.1:8000` (rotas em `/api/...`)

### 1.5 Telão (Reverb)

Em outro terminal:

```powershell
php artisan reverb:start
```

---

## 2. Frontend

Path: `Front-end-clinica/` (onde está o `package.json`)

```powershell
cd "Front-end-clinica"
npm install
npm run dev
```

Opcional — `.env` / `.env.local` no front:

```env
VITE_API_URL=http://localhost:8000/api
VITE_CLINIC_SLUG=demo
```

App: `http://localhost:5173`  
Axios: `VITE_API_URL` (default local `http://localhost:8000/api`) + header `X-Clinic-Slug`

---

## 3. Primeiro acesso

1. Abra `http://localhost:5173`
2. Clínica: `demo`
3. E-mail: `admin@demo.local`
4. Senha: `password`
5. Configure **Agendamentos** (grade de horários)
6. Cadastre profissionais em **Usuários** (perfil Profissional)
7. Cadastre paciente → agende → teste fila/telão

Telão:

`http://localhost:5173/consultas/telao-chamada?clinic=demo`

---

## 4. Checklist se algo não sobe

| Problema | Verificação |
|----------|-------------|
| Logo `/storage/...` 404 | `php artisan marag:doctor` (recria `public/storage` se apontar path antigo) |
| Path / pasta aninhada | Código fica em `Back-end-clinica/` e `Front-end-clinica/` — **sem** `paulinho-marcilio-*` |
| Composer / path longo | Usar `--prefer-dist` |
| Extensão zip PHP | Habilitar `extension=zip` no `php.ini` |
| MySQL parado | Serviços → MySQL80 |
| 400 “informe a clínica” | Slug no login / `VITE_CLINIC_SLUG` |
| Login falha | Usuário existe **no DB da clínica**, não no central |
| Agenda sem slots | Configuração de agendamento + usuário profile 3 |
| Telão desconectado | `reverb:start` + `?clinic=` correto |
| JWT_KEY | Aspas no `.env` se a chave tiver espaços |

---

## 5. Comandos úteis

```powershell
# Saúde do ambiente (storage link, paths)
php artisan marag:doctor

# Rotas API
php artisan route:list --path=api

# Nova clínica
php artisan clinic:provision outra "Outra Clínica" --admin-email=admin@outra.local --admin-password=password

# Migrations só do tenant atual (após TenantContext / DB_DATABASE apontando)
php artisan migrate --force
```

---

## Ver também

- [Manual do usuário](MANUAL_USUARIO.md)
- [Funcionamento técnico](FUNCIONAMENTO_TECNICO.md)
- [Mensagens de erro](MENSAGENS_ERRO.md)
- [Índice docs](README.md)
