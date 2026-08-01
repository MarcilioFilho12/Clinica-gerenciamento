# AGENTS.md — Marag Clínica

Instruções para qualquer agente de IA trabalhando neste repositório.

## Idioma

- Responder ao usuário em **português (Brasil)**
- Código e commits: português ou inglês consistente com o arquivo tocado

## Paths reais

- Backend Laravel: `Back-end-clinica/paulinho-marcilio-back-main/paulinho-marcilio-back-main`
- Frontend Vue: `Front-end-clinica/paulinho-marcilio-front-main/paulinho-marcilio-front-main`
- Nunca rodar `composer` / `artisan` / `yarn` uma pasta acima desses paths

## Produto

- Marca do software: **Marag**
- Domínio: clínica **oftalmológica** (não generalizar para hospital/genérico sem pedido)
- Escopo atual: **multi-tenant** (Fase 4): banco central + 1 DB por clínica
- Isolamento: **não** usar `clinic_id` nas tabelas de negócio — o tenant é o database
- Branding: Marag no login/©; nome/logo/cores por clínica (white-label)

## Prioridade de trabalho

1. Segurança (JWT endurecido, rotas autenticadas, interceptor front)
2. Núcleo clínico (agenda, pacientes, ficha, fila, telão)
3. Financeiro + relatórios reais
4. Multi-tenant + white-label

Não pular fases sem confirmação do usuário.

## Decisões travadas

- Auth: **manter JWT** e endurecer (não migrar para Sanctum agora)
- Tenant: header `X-Clinic-Slug` / JWT `clinic_slug`; provisionar com `clinic:provision`
- Branding: Marag no login; white-label via `/configuracoes/marca`
- Perguntar ao usuário antes de decisões de produto, auth alternativa ou destruição de dados

## Segurança (obrigatório)

- Não deixar rotas de negócio públicas
- Não colocar hash de senha / User completo no payload JWT
- Não commitar `.env` com secrets
- Não sugerir `migrate:fresh` / `migrate:refresh` em banco com dados
- Mensagens de erro de API: profissionais (sem gíria)

## Backend (Laravel)

- Controllers magros; validação em Form Request quando criar/editar endpoints
- Preferir Eloquent + migrations versionadas
- Status de consulta/usuário: evitar IDs mágicos soltos; preferir enums/constantes nomeadas
- Evento de chamada (`PacienteChamado`) + Reverb para telão

## Frontend (Vue)

- Usar `src/services/axios.js` (não axios “solto” com URL errada)
- Token Bearer via interceptor (Fase 1)
- Sem mocks em módulos que já têm API
- Branding: Marag no login/©; white-label dinâmico na clínica (Fase 4)

## Comandos locais úteis

```powershell
# API
cd "Back-end-clinica\paulinho-marcilio-back-main\paulinho-marcilio-back-main"
php artisan serve

# Front
cd "Front-end-clinica\paulinho-marcilio-front-main\paulinho-marcilio-front-main"
npm run dev

# Telão
php artisan reverb:start
```

## Quando perguntar ao usuário

- Troca de estratégia de auth
- Multi-tenant / white-label antecipado
- Apagar dados, reset de banco, force push
- Integrações externas (WhatsApp, TISS, pagamento)
- Mudança de marca ou domínio clínico
