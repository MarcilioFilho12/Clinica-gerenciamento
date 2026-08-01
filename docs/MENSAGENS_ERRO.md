# Mensagens de erro e respostas — Marag

Referência do que o usuário / integrador pode ver. Mensagens de API são profissionais (sem gíria).

---

## 1. HTTP — significado geral

| Código | Significado típico no Marag |
|--------|-----------------------------|
| **200** | Sucesso |
| **201** | Criado (ex.: ficha, despesa) |
| **400** | Falta clínica / regra de negócio (ex.: chegada já confirmada) |
| **401** | Não autenticado ou credenciais inválidas |
| **404** | Recurso ou clínica não encontrada |
| **422** | Validação (campos obrigatórios / formato) |
| **500** | Erro interno |

Formato comum de erro:

```json
{
  "success": false,
  "message": "Texto legível",
  "errors": { "campo": ["detalhe"] }
}
```

No front, toasts (`vue3-toastify`) mostram `message` ou mensagens de validação local.

---

## 2. Clínica / tenant

| Mensagem | Quando | O que fazer |
|----------|--------|-------------|
| Informe a clínica (header X-Clinic-Slug ou parâmetro clinic). | Request sem slug | Enviar header ou escolher slug no login |
| Clínica não encontrada ou inativa. | Slug inválido / inativo | Conferir provisionamento |
| Informe a clínica (X-Clinic-Slug) para autenticar. | Login sem tenant resolvido | Preencher campo Clínica |
| Informe o slug da clínica. | Branding sem slug | `?slug=` ou header |
| Clínica não encontrada. | Branding | Slug errado |
| Token não pertence a esta clínica. | JWT de outra clínica | Login de novo no slug certo |
| Clínica não resolvida. | Update branding sem contexto | Header + JWT válidos |

---

## 3. Autenticação

| Mensagem | HTTP | O que fazer |
|----------|------|-------------|
| E-mail ou senha incorretos. | 401 | Conferir usuário **desta** clínica |
| Não autenticado. Informe o token Bearer. | 401 | Login novamente |
| Token inválido. | 401 | Login novamente |
| Token inválido ou expirado. | 401 | Login novamente |
| Usuário do token não encontrado. | 401 | Usuário apagado no tenant |

**Front:** em 401 (exceto POST `/auth`), limpa token e redireciona para `/`.

---

## 4. Agenda / consultas

| Mensagem | Situação |
|----------|----------|
| Consulta agendada com sucesso! | OK |
| Consulta atualizada com sucesso! | OK |
| Horário já ocupado para este profissional | Conflito de slot |
| Horário não disponível conforme configuração de agendamento. | Fora da grade |
| Nenhuma configuração de agendamento encontrada para esta data. | Falta config |
| Clínica não funciona neste dia | Dia sem expediente na config |
| Informe a forma de pagamento quando a consulta estiver paga. | `pago=true` sem forma |
| Chegada do paciente confirmada com sucesso | OK |
| Chegada já foi confirmada anteriormente | 400 — idempotente na prática do fluxo |
| Paciente chamado para atendimento | OK |
| Consulta cancelada / finalizada / confirmada / excluída… | OK conforme ação |
| Consulta não encontrada | ID inválido |

**Front (agenda):** toasts de sucesso/erro ao salvar, confirmar chegada, etc.

---

## 5. Pacientes / pré-cadastro

### Validação local (CadastroPaciente)

- Nome é obrigatório
- Data de nascimento é obrigatória
- Telefone é obrigatório
- Email inválido
- CPF deve ter 11 dígitos
- CPF do responsável deve ter 11 dígitos
- Telefone deve ter entre 10 e 11 dígitos
- Complete nome, data de nascimento e telefone antes de continuar (fluxo atendimento)

### API

| Mensagem | Situação |
|----------|----------|
| Paciente cadastrado / atualizado com sucesso | OK |
| Dados inválidos + `errors` | 422 |
| Erro de conexão. Tente novamente. | Rede / API fora |

### Fluxo atendimento

| Mensagem | Situação |
|----------|----------|
| Pré-cadastro confirmado | OK antes da ficha |
| Não foi possível iniciar o atendimento | Falha em chegada/chamar |
| Não foi possível identificar o paciente desta consulta | Agenda sem `paciente_id` (API antiga) |

---

## 6. Ficha clínica

### Validação local (FichaClinica)

- Motivo da consulta obrigatório
- Informe ao menos a refração subjetiva (ESF ou CIL) de um dos olhos
- Outros campos conforme regras da tela

### API

| Mensagem | Situação |
|----------|----------|
| Ficha clínica criada com sucesso | OK |
| Ficha clínica criada com sucesso e consulta encerrada automaticamente | Veio de atendimento (situacao 6→4) |
| Ficha clínica atualizada com sucesso | OK |
| Consulta não encontrada | `consulta_id` inválido |
| A consulta não pertence a este paciente | IDs inconsistentes |
| Não é possível criar ficha clínica para uma consulta cancelada | situacao 5 |
| Dados inválidos | 422 |

Avisos front possíveis: consulta não encontrada / erro ao validar → ficha sem vínculo.

---

## 7. Fila / telão

| Mensagem / UI | Situação |
|---------------|----------|
| … foi chamado para atendimento | Toast sucesso |
| Erro ao chamar paciente | API/rede |
| Paciente não encontrado | Modal sem seleção |
| Telão: Conectado / Desconectado | Estado WebSocket |
| Canal `chamadas.pacientes.{slug}` | Técnico — slug errado = silêncio |

---

## 8. Financeiro

| Mensagem | Situação |
|----------|----------|
| Resumo / relatório financeiro carregado | OK |
| Despesa registrada / atualizada / excluída com sucesso | OK |
| Despesa não encontrada | 404 |
| Dados inválidos | Valor, data, forma |
| Período inválido. Use: hoje, semana… | Query `periodo` errada |
| Erro ao carregar resumo/relatório financeiro | 500 |

UI: “Sem receitas no período”, “Nenhuma despesa lançada…” — estados vazios, não erro.

---

## 9. Branding

| Mensagem | Situação |
|----------|----------|
| Branding atualizado | OK |
| Dados inválidos | Cor fora do formato `#RGB` / `#RRGGBB` |

---

## 10. Autorização de perfil (front)

Não há toast específico: rota com `meta.profiles` sem match → redirect para `/home`.

API com middleware `profile:` → tipicamente **403** (mensagem padrão Laravel / middleware).

---

## 11. Checklist rápido de diagnóstico

1. **400 clínica** → slug no login / header  
2. **401** → login / token / clínica do token  
3. **422** → ler `errors` campo a campo  
4. **Agenda vazia** → config de agendamento + profissionais profile 3  
5. **Telão mudo** → Reverb + `?clinic=` igual ao slug logado  
6. **Receita zerada** → consultas com `pago` + `valor`  
7. **Ficha não encerra consulta** → consulta precisa estar situacao 6 (Chamar)

---

## Ver também

- [Manual do usuário](MANUAL_USUARIO.md)
- [Funcionamento técnico](FUNCIONAMENTO_TECNICO.md)
- [Segurança](SECURITY.md)
