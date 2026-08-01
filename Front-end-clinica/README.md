# Marag — Frontend (Vue 3 + Vite)

SPA do painel Marag (agenda, pacientes, ficha, fila, financeiro UI, configs).

## Path

Execute comandos **nesta pasta** (onde está o `package.json`):

`Front-end-clinica/paulinho-marcilio-front-main/paulinho-marcilio-front-main`

## Quick start

```powershell
npm install
npm run dev
```

- App: `http://localhost:5173`
- API esperada: `http://localhost:8000/api` (`src/services/axios.js`)

Docs do monorepo: [README raiz](../../../README.md) · [SETUP](../../../docs/SETUP.md)

## Telão

Rota `/consultas/telao-chamada` — requer Reverb no backend (`php artisan reverb:start`).

## Notas

- Alguns módulos (dashboard, financeiro, relatórios) ainda usam mock — ver roadmap Fase 2–3
- Requisitos de produto legados: `listaToDo.mdx`, `instructions.mdx`
