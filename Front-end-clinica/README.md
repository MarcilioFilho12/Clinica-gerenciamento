# Marag — Frontend (Vue 3 + Vite)

SPA do painel Marag (agenda, pacientes, ficha, fila, financeiro UI, configs).

## Path

Execute comandos **nesta pasta** (onde está o `package.json`):

`Front-end-clinica/`

## Quick start

```powershell
npm install
npm run dev
```

- App: `http://localhost:5173`
- API: `VITE_API_URL` (padrão em `src/services/axios.js`)

Templates:

- `.env.example` — desenvolvimento  
- `.env.production.example` — staging/prod (obrigatório definir `VITE_API_URL` no build)

```powershell
# Staging/prod
$env:VITE_API_URL="https://sua-api.up.railway.app/api"
npm run build:check-env
npm run build
```

Deploy: [docs/DEPLOY_RAILWAY.md](../docs/DEPLOY_RAILWAY.md)

## Telão

Rota `/consultas/telao-chamada` — requer Reverb no backend (`php artisan reverb:start`).

## Notas

- Piloto: algumas rotas (modelos, inadimplentes, etc.) redirecionam para Home
- Requisitos de produto legados: `listaToDo.mdx`, `instructions.mdx`
