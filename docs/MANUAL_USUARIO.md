# Manual do usuário — Marag

Guia de uso diário do sistema de gestão de clínica oftalmológica.

**URLs locais típicas**
- Painel: `http://localhost:5173`
- API: `http://localhost:8000/api`
- Telão: `http://localhost:5173/consultas/telao-chamada?clinic=SEU_SLUG`

---

## 1. Perfis e o que cada um faz

| Perfil | Quem é | Acesso principal |
|--------|--------|------------------|
| **Administrador** (1) | Dono / gestor | Tudo + usuários + marca da clínica + ajustes |
| **Recepção** (2) | Secretaria | Agenda, pacientes, fila, financeiro, parceiros, relatórios |
| **Profissional** (3) | Optometrista / médico | Agenda do dia, pacientes, ficha clínica, fila |

O menu lateral filtra itens conforme o perfil. Se você abrir uma URL sem permissão, o sistema redireciona para `/home`.

---

## 2. Login

1. Abra o painel.
2. Informe:
   - **Clínica** = slug (ex.: `demo`) — criado no provisionamento
   - **E-mail** e **Senha**
3. Clique em **Entrar**.

O login mostra a marca **Marag**. O nome/logo da sua clínica aparecem depois, no cabeçalho do painel.

### Problemas comuns no login

| Situação | O que fazer |
|----------|-------------|
| “Clínica não encontrada” | Confira o slug; a clínica precisa estar ativa |
| “E-mail ou senha incorretos” | Verifique credenciais da **clínica** (cada clínica tem seus usuários) |
| Volta sozinho para o login | Token expirado ou 401 — faça login de novo |

---

## 3. Fluxo usual do dia (recepção + profissional)

```
Agendar → Paciente chega → Confirmar chegada → Fila → Chamar
    → Pré-cadastro → Ficha clínica → Consulta encerrada
```

### 3.1 Configurar horários (primeira vez)

1. Menu **Configurações → Agendamentos**
2. Crie/edite a grade: dias da semana, horário início/fim, duração e intervalo
3. Associe aos profissionais (usuários com perfil Profissional)

Sem configuração ativa, a agenda não gera slots.

### 3.2 Cadastrar paciente

1. **Pacientes → Gerenciar** → **Cadastrar**, ou  
   **Pacientes → Cadastro** (`/pacientes/cadastro`)
2. Preencha o mínimo:
   - Nome completo
   - CPF
3. Opcionais: data de nascimento, telefone, RG, sexo, endereço, e-mail, responsável, observações
4. Salve

Também é possível cadastrar a partir do modal de **Nova consulta** na agenda (botão **Novo**).

### 3.3 Agendar consulta

1. Abra **Agenda** — ela abre no modo **Dia** (operação da recepção), com um profissional em foco
2. Use **Semana** / **Mês** só para planejamento; ao clicar em um dia ou em **Hoje**, volta ao modo Dia
3. Clique em **Nova consulta**, ou em um horário livre / célula da semana
4. No modal (um passo só):
   - **Data** e **Profissional** (preenchidos automaticamente quando possível)
   - **Horário**: use as **Sugestões** da grade (só slots livres) **ou** **Horário livre** para encaixe em qualquer horário dentro do expediente
   - **Paciente** (busca por nome/CPF) ou **Novo** para cadastrar e voltar
   - Procedimento, prioridade, parceiro, pagamento e observações
5. Salve

O profissional precisa existir com perfil **Profissional** ativo. A configuração de agendamentos define o expediente e a grade de sugestões — a recepção **não** fica presa só aos slots: horário livre cobre encaixes.

**Formas de pagamento:** dinheiro, PIX, cartão crédito/débito, convênio, transferência, outro.

### 3.4 Confirmar chegada

Na agenda do dia, no paciente agendado (ainda sem chegada):

- Clique no ícone de **confirmar chegada** (check), **ou**
- Use o fluxo da recepção conforme o botão disponível

O paciente passa a aparecer na **Fila de espera** (com código de chegada).

> Dica: clicar no **nome** do paciente na agenda abre o **pré-cadastro** (fluxo médico), não a confirmação de chegada.

### 3.5 Fila de espera

1. Menu **Consultas → Fila de espera**
2. Veja quem chegou / urgências
3. **Chamar** → confirma → paciente vai para “em atendimento” e aparece no **telão**
4. O sistema abre o **pré-cadastro** para seguir o atendimento

Também é possível **adicionar à fila** (urgência) sem horário padrão.

### 3.6 Atendimento (profissional)

1. Clique no paciente na agenda **ou** use **Chamar** na fila
2. Revise o **pré-cadastro** (dados da secretaria)
3. **Salvar e continuar para ficha**
4. Preencha a **ficha clínica**
5. Salve — se a consulta estava em atendimento, ela é **encerrada** automaticamente

### 3.7 Ficha clínica — o que preencher

| Bloco | Conteúdo |
|-------|----------|
| Anamnese | Motivo da consulta (obrigatório), último controle optométrico, antecedentes |
| Acuidade | VL / VP / PH por olho |
| Refração | Estática, dinâmica, subjetiva (ESF/CIL/EIXO/ADD com intervalos) |
| Biomicroscopia | Córnea, íris, conjuntiva, cristalino, pupilas |
| Prescrição | Material, tipo de lente, filtro, diagnóstico, conduta, **encaminhamento**, próxima consulta |

**Validações importantes**
- Motivo da consulta obrigatório
- Ao menos ESF ou CIL na refração subjetiva de um olho

**Impressões**
- Ficha completa
- Refração subjetiva em **A5**

### 3.8 Pacientes em atendimento

Em **Gerenciar pacientes**, a seção “em atendimento” tem:

- **Atender** → pré-cadastro → ficha
- **Detalhes** → ficha do paciente / histórico

---

## 4. Telão de chamadas

### Como abrir

```
http://localhost:5173/consultas/telao-chamada?clinic=demo
```

Troque `demo` pelo slug da clínica. **Não precisa login.**

### Como funciona

1. Recepção/profissional clica **Chamar** na fila
2. A API marca a consulta como em atendimento
3. Evento em tempo real (Reverb) no canal `chamadas.pacientes.{slug}`
4. O telão destaca o paciente + código + profissional

### Requisitos técnicos (TI)

- API rodando (`php artisan serve`)
- Reverb rodando (`php artisan reverb:start`)
- Front apontando para a API / WebSocket corretos

### Problemas

| Sintoma | Causa provável |
|---------|----------------|
| “Desconectado” no rodapé | Reverb parado ou porta errada |
| Não atualiza ao chamar | Slug do telão ≠ slug da clínica logada |
| Sem som | Bloqueio de áudio do navegador — interaja com a página |

---

## 5. Financeiro (recepção / admin)

### Visão geral (`/financeiro/visao-geral`)

- **Entradas** = consultas marcadas como **pagas** com valor
- **Saídas** = despesas lançadas
- **Saldo**, ticket médio, a receber, comparativo com período anterior
- Formas de pagamento e receita por procedimento
- **Nova despesa** (descrição, valor, data, categoria, forma)

Períodos: hoje, semana, mês, trimestre, ano.

### Relatório financeiro (`/relatorios/financeiro`)

Mesma base de dados, com série no período, convênios × particulares, histórico comparativo e exportação TXT.

### Como a receita entra no sistema

Na agenda, ao criar/editar consulta: marque **Pago** + forma + valor.  
Consultas não pagas com valor informado entram em **A receber**.

> A tela **Atendimentos** financeira ainda pode estar mockada — use Visão Geral / Relatório.

---

## 6. Cadastros de apoio

### Usuários (só admin)

**Configurações → Usuários**

- Crie recepção e profissionais
- Profissional precisa de `profile_id` = Profissional para aparecer na grade da agenda

### Parceiros / convênios

**Configurações → Parceiros** — cadastre Unimed, labs etc. e vincule na consulta.

### Marca da clínica (white-label)

**Configurações → Marca da Clínica** (admin)

- Nome, URL do logo, cor primária e secundária
- Login e rodapé © continuam **Marag**

### Nova clínica no SaaS (TI / Marag)

Não é self-serve. Provisionar no servidor:

```bash
php artisan clinic:provision slug "Nome da Clínica" --admin-email=... --admin-password=...
```

Cada clínica tem **banco próprio** e usuários próprios.

---

## 7. Impressões úteis

| O quê | Como |
|-------|------|
| Ficha completa | Na ficha / visualização → imprimir |
| Refração subjetiva A5 | Botão na ficha clínica |

Use impressora / “Salvar como PDF” do navegador.

---

## 8. Mapa rápido de telas

| Menu | Uso |
|------|-----|
| Início | Dashboard (parcialmente mock) |
| Agenda | Agendar, chegada, abrir atendimento |
| Pacientes | Cadastro, detalhes, fichas |
| Fila de espera | Chamar / urgência |
| Telão | TV da sala de espera |
| Financeiro | Entradas/saídas reais |
| Relatórios | Financeiro real; outros podem estar mock |
| Configurações | Grade, parceiros, usuários, marca |

---

## 9. Boas práticas

1. Sempre preencher telefone e nascimento no pré-cadastro
2. Confirmar chegada **antes** de lotar a fila mentalmente
3. Marcar pagamento na agenda quando receber na recepção
4. Telão em tela cheia (`F11`) com `?clinic=` correto
5. Não compartilhar login de admin; crie usuário por papel

---

## Ver também

- [Funcionamento técnico](FUNCIONAMENTO_TECNICO.md)
- [Mensagens de erro](MENSAGENS_ERRO.md)
- [Setup](SETUP.md)
- [Arquitetura](ARCHITECTURE.md)
