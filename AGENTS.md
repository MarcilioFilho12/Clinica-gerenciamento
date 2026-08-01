# AGENTS.md — Marag Clínica

Instruções para qualquer agente de IA neste repositório.

## Idioma

- Responder ao usuário em **português (Brasil)**
- Código e commits: português ou inglês consistente com o arquivo tocado

## Paths reais

- Backend Laravel: `Back-end-clinica/`
- Frontend Vue: `Front-end-clinica/`
- Rodar `composer` / `artisan` / `npm|yarn` **dentro** desses paths

## Comitê Técnico (review / deploy)

Regras em `.cursor/rules/` — playbook: [`docs/COMITE_TECNICO.md`](docs/COMITE_TECNICO.md).

- Orquestrador sempre ativo (`00-orquestrador.mdc`)
- Deploy / Railway / clínica real → acionar **Release Manager** (`release.mdc`)
- Item **CRÍTICO** ⇒ código **não** está pronto

## Produto

- Marca: **Marag**
- Domínio: clínica **oftalmológica**
- Multi-tenant: banco central (`marag_central`) + 1 DB por clínica
- Isolamento: **não** usar `clinic_id` nas tabelas de negócio
- Branding: Marag no login/©; nome/logo/cores por clínica

## Prioridade de trabalho

1. Segurança (JWT, rate limit, sem leak de erros, uploads seguros)
2. Núcleo clínico (agenda, pacientes, ficha, fila)
3. Financeiro real (consulta paga + despesas)
4. Deploy piloto (Railway) + observabilidade mínima
5. SaaS (subdomínio, billing, painel Marag) — depois do piloto estável

## Decisões travadas

- Auth: **JWT** endurecido (não Sanctum agora)
- Tenant: `X-Clinic-Slug` / JWT `clinic_slug`; `php artisan clinic:provision`
- Perguntar ao usuário antes de trocar auth, destruir dados ou mudar modelo de tenant

## Segurança (obrigatório)

- Rotas de negócio autenticadas
- Claims JWT mínimas (nunca password)
- Não commitar `.env`
- Não sugerir `migrate:fresh` com dados de clínica
- Mensagens de erro profissionais (sem stack/SQL ao cliente)
- Logs sem CPF/dados clínicos desnecessários

## Backend (Laravel)

- Controllers magros; Form Request em endpoints novos
- Eloquent + migrations versionadas (central vs tenant)
- Evento `PacienteChamado` + Reverb (telão opcional no piloto)

## Frontend (Vue)

- Só `src/services/axios.js`
- `VITE_API_URL` em produção (nunca hardcode localhost em build de prod)
- Bearer + slug no interceptor; Pinia auth/clinic

## Comandos locais úteis

```powershell
cd "Back-end-clinica"
php artisan marag:doctor --fix   # repara public/storage se path antigo quebrou
php artisan serve

cd "Front-end-clinica"
npm run dev

# Telão (opcional)
cd "Back-end-clinica"
php artisan reverb:start
```

## Quando perguntar ao usuário

- Troca de estratégia de auth ou tenant
- Deploy com dados reais / LGPD
- Escopo fora do piloto
- Qualquer operação destrutiva de banco
