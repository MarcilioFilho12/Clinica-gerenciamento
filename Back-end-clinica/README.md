# Marag — Backend (Laravel API)

API do SaaS Marag de gestão de clínica oftalmológica.

## Path

Execute comandos **nesta pasta** (onde está o `artisan` / `composer.json`):

`Back-end-clinica/`

## Quick start

```powershell
composer install --prefer-dist
# configurar .env (DB, APP_KEY, JWT_KEY, CENTRAL_DB_*, Reverb)
php artisan marag:doctor
php artisan migrate --database=central --path=database/migrations/central --force
php artisan clinic:provision demo "Clínica Demo" --admin-email=admin@demo.local --admin-password=password
php artisan serve
php artisan reverb:start   # opcional — telão
```

- API: `http://127.0.0.1:8000`
- Docs do monorepo: [README raiz](../README.md) · [SETUP](../docs/SETUP.md) · [SECURITY](../docs/SECURITY.md)

## Auth

JWT custom (`JWT_KEY`). Campo de senha no login: `senha`. Tenant: header `X-Clinic-Slug`.

## Módulos da API

Pacientes, fichas clínicas, consultas/agenda, fila, parceiros, usuários, config. agendamento, financeiro (resumo/despesas), white-label (branding/logo).

## Storage (logos)

Arquivos em `storage/app/public`. URL pública via `public/storage` → rode `php artisan marag:doctor` se `/storage/...` der 404 após mover o projeto.
