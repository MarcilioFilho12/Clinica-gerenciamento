# Checklist — Piloto Marag (fase de testes)

Escopo: **1 clínica**, fluxo clínico + caixa básico. Telas pesadas/fora do piloto ficam ocultas.

## Pré-requisitos do ambiente

- [ ] MySQL ativo (`MySQL80` ou equivalente)
- [ ] Banco central `marag_central` + tenant da clínica (ex.: `marag_clinic_demo`)
- [ ] Clínica provisionada (`php artisan clinic:provision ...` ou demo existente)
- [ ] API Laravel: `php artisan serve` (`:8000`)
- [ ] Front Vite: `npm run dev` (`:5173`)
- [ ] Reverb (se testar telão): `php artisan reverb:start` (`:8080`)
- [ ] `APP_URL=http://localhost:8000` e `php artisan storage:link` (logo)
- [ ] Login com slug da clínica + usuário admin

**Credenciais demo típicas:** slug `demo` · `admin@demo.local` / `password`

## Perfis a validar

| Perfil | O que deve conseguir |
|--------|----------------------|
| Admin | Tudo do piloto + usuários + marca + ajustes |
| Recepção | Agenda, pacientes, fila, financeiro, parceiros |
| Profissional | Agenda, pacientes, ficha, fila (sem financeiro/config staff) |

## Happy path (obrigatório)

1. [ ] Login com slug + e-mail + senha
2. [ ] Abrir **Agenda** → Nova consulta → pergunta “paciente já cadastrado?”
3. [ ] **Não** → cadastrar paciente → retorno automático à Agenda com paciente selecionado
4. [ ] Completar agendamento (médico, data, horário, procedimento) e salvar
5. [ ] **Sim** (outro fluxo) → buscar paciente existente → agendar
6. [ ] Confirmar chegada → paciente na **Fila de Espera**
7. [ ] Chamar / iniciar atendimento (conforme fluxo do perfil)
8. [ ] Abrir/criar **Ficha clínica** e salvar
9. [ ] Marcar consulta como **paga** (agenda ou financeiro)
10. [ ] Ver **Financeiro → Visão Geral** (receita / despesas)
11. [ ] Lançar uma **despesa** e conferir no resumo
12. [ ] Em Detalhes do Paciente → **Consulta antiga** (data passada) → aparece no histórico

## Branding / white-label

- [ ] Configurações → **Marca da Clínica**: nome e cores
- [ ] Upload de logo em **Ajustes Gerais** (ou URL na Marca)
- [ ] Login continua com marca **Marag** no vendor/copyright

## Relatórios do piloto

- [ ] Aniversariantes (lista a partir de pacientes)
- [ ] Consultas vencidas (agenda do período)
- [ ] Relatório financeiro (API real)

## Telão (opcional neste piloto)

- [ ] Abrir `/consultas/telao-chamada`
- [ ] Chamar paciente e ver atualização (Reverb)

## Fora do piloto (não testar / redireciona para Home)

- Modelos / Templates  
- Inadimplentes  
- Permissões (UI)  
- Fluxo Diário  
- Relatório de Consultas (mock)  

## Critério de aceite do piloto

- [ ] Happy path completo sem erro 4xx/5xx bloqueante
- [ ] Menu sem itens fora do escopo
- [ ] Bundle: primeira carga sem carregar todas as views de uma vez (lazy routes)
- [ ] Três perfis respeitam o que podem / não podem ver

## Bugs / anotações

| # | Passo | Problema | Severidade |
|---|-------|----------|------------|
| 1 | | | |
| 2 | | | |
| 3 | | | |

---

*Documento gerado para a fase de testes do piloto Marag. Atualizar conforme o teste avança.*
