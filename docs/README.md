# Documentação Marag

Índice da pasta `docs/`.

## Para quem usa o sistema

| Documento | Conteúdo |
|-----------|----------|
| [MANUAL_USUARIO.md](MANUAL_USUARIO.md) | Login, perfis, agenda, cadastros, fila, ficha, telão, financeiro, boas práticas |
| [MENSAGENS_ERRO.md](MENSAGENS_ERRO.md) | Códigos HTTP, toasts e mensagens da API — o que significam e o que fazer |
| [CHECKLIST_PILOTO.md](CHECKLIST_PILOTO.md) | Roteiro de aceite para deploy e fase de testes (1 clínica) |

## Para quem configura / desenvolve

| Documento | Conteúdo |
|-----------|----------|
| [SETUP.md](SETUP.md) | Instalação local, MySQL central/tenant, provisionar clínica, Reverb |
| [FUNCIONAMENTO_TECNICO.md](FUNCIONAMENTO_TECNICO.md) | Arquitetura runtime, JWT, multi-DB, fluxos, APIs, telão |
| [ARCHITECTURE.md](ARCHITECTURE.md) | Bounded contexts e ADRs |
| [SECURITY.md](SECURITY.md) | Baseline de segurança |
| [ROADMAP.md](ROADMAP.md) | Fases 0–4 e status |

## Na raiz do repositório

| Arquivo | Conteúdo |
|---------|----------|
| [../README.md](../README.md) | Visão geral do produto e links |
| [../AGENTS.md](../AGENTS.md) | Regras para agentes de IA / contribuidores |

## Fluxo recomendado de leitura

1. **Operador da clínica** → Manual do usuário → Mensagens de erro  
2. **TI / setup** → Setup → Funcionamento técnico  
3. **Desenvolvimento** → Architecture → Security → Roadmap → AGENTS  
