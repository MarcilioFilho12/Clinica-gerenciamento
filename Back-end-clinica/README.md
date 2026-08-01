# Marag — Backend (Laravel API)

API do SaaS Marag de gestão de clínica oftalmológica.

## Path

Execute comandos **nesta pasta** (onde está o `artisan` / `composer.json`):

`Back-end-clinica/paulinho-marcilio-back-main/paulinho-marcilio-back-main`

## Quick start

```powershell
composer install --prefer-dist
# configurar .env (DB, APP_KEY, JWT_KEY, Reverb)
php artisan migrate --seed
php artisan serve
php artisan reverb:start   # opcional — telão
```

- API: `http://127.0.0.1:8000`
- Docs do monorepo: [README raiz](../../../README.md) · [SETUP](../../../docs/SETUP.md) · [SECURITY](../../../docs/SECURITY.md)

## Auth

JWT custom (`JWT_KEY`). Campo de senha no login: `senha`.

## Módulos da API

Pacientes, fichas clínicas, consultas/agenda, fila, parceiros, usuários, config. agendamento.

Financeiro e multi-tenant: roadmap nas Fases 3–4.
