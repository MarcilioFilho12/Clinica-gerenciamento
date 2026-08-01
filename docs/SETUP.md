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

Path:

`Back-end-clinica/paulinho-marcilio-back-main/paulinho-marcilio-back-main`

```powershell
cd "Back-end-clinica\paulinho-marcilio-back-main\paulinho-marcilio-back-main"
composer install --prefer-dist
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

BROADCAST_DRIVER=reverb
# REVERB_* conforme env.mdx
```

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

Path:

`Front-end-clinica/paulinho-marcilio-front-main/paulinho-marcilio-front-main`

```powershell
cd "Front-end-clinica\paulinho-marcilio-front-main\paulinho-marcilio-front-main"
npm install
npm run dev
```

Opcional — `.env` no front:

```env
VITE_CLINIC_SLUG=demo
```

App: `http://localhost:5173`  
Axios: `http://localhost:8000/api` + header `X-Clinic-Slug`

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
| Composer / path longo | Usar `--prefer-dist`; path do projeto sem pasta “zip” duplicada errada |
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
