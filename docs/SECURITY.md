# Segurança — Marag

## Baseline atual (dívida conhecida)

- Rotas de negócio em `routes/api.php` majoritariamente **sem** middleware de auth
- JWT custom em `App\Custom\Jwt` via `JWT_KEY`
- Front sem interceptor Bearer automático
- Mensagens de auth informais no controller (corrigir na Fase 1)

## Alvo Fase 1 (P0)

1. Middleware JWT em todas as rotas de negócio (exceto login público e, se necessário, telão)
2. Payload JWT mínimo: `id`, `name`, `email`, `profile_id` — **nunca** password
3. Axios: `Authorization: Bearer <token>` + logout em 401
4. RBAC básico por `profile_id` (admin / recepção / médico)
5. `.env.example` com `JWT_KEY`, DB, Reverb — sem valores secretos reais
6. Respostas 401/403 profissionais

## Boas práticas contínuas

- Secrets só em `.env` / vault
- Validação server-side obrigatória
- Soft deletes já existem em User — manter consistência
- Logs sem CPF/dados clínicos em plain text desnecessário
- LGPD: minimizar exposição em impressão e APIs de listagem

## Proibido

- Commitar `.env`
- `migrate:fresh` em produção ou banco com dados de clínica
- Endpoints de delete em massa sem auth
- Broadcast sensível em canal público sem necessidade (revisar telão)
