# Segurança — Marag

## Estado atual (PRG Fase 1 — código)

- Rotas de negócio: middlewares `clinic` + `jwt` + `profile`
- Login: `POST /api/auth` com `throttle:login` (5/min por e-mail+IP)
- JWT: claims mínimas (`id`, `name`, `email`, `profile_id`, `clinic_slug`) — **nunca** password
- TTL JWT: `JWT_TTL_SECONDS` (default **2 horas** no piloto, mínimo 5 min)
- Erros 500: **sem** `$e->getMessage()` no JSON; `report($e)` no servidor
- Upload logo: jpeg/jpg/png/gif/webp — **sem SVG**
- Logs de chamada: só IDs / slug (sem nome de paciente)
- `DEFAULT_CLINIC_SLUG`: **ignorado em `production`**

## Produção (obrigatório)

```env
APP_ENV=production
APP_DEBUG=false
JWT_KEY=...   # forte, único
JWT_TTL_SECONDS=7200
# NÃO definir DEFAULT_CLINIC_SLUG em production
```

## Boas práticas

- Secrets só em `.env` / vault
- Validação server-side
- Soft deletes em User
- Logs sem CPF/dados clínicos
- LGPD: minimizar exposição em impressão e listagens

## Proibido

- Commitar `.env`
- `migrate:fresh` com dados de clínica
- Delete em massa sem auth
- Expandir canal público do telão sem revisão Security/LGPD
