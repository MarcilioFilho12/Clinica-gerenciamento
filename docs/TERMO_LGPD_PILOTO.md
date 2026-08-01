# Termo de tratamento de dados — piloto Marag (rascunho)

**Status:** rascunho para revisão jurídica / da clínica. **Não substitui** assessoria legal.  
**Uso:** soft launch 1 clínica (G0.1). Sem assinatura → **não** colocar dados reais de pacientes em produção (G3.3).

---

## Partes

- **Controladora / Operadora da clínica:** _________________________________ (CNPJ: _______________)
- **Fornecedora do software (Marag):** _________________________________
- **Clínica / unidade:** _________________________________
- **Data de início do piloto:** ____/____/________

---

## 1. Objeto

A clínica utilizará o sistema **Marag** para gestão de agenda, cadastro de pacientes, fila de atendimento, ficha clínica oftalmológica e registros financeiros básicos (consulta paga e despesas), em ambiente de piloto assistido.

## 2. Dados tratados (categorias)

Podem ser tratados, conforme o uso do sistema:

- Dados de identificação e contato do paciente (nome, CPF, telefone, e-mail, endereço)
- Dados de responsável legal (quando aplicável)
- Dados de saúde / clínicos da ficha oftalmológica e histórico de consultas
- Dados de agendamento, atendimento e pagamento da consulta
- Dados de usuários da clínica (nome, e-mail, perfil de acesso)

## 3. Finalidades

- Prestação do atendimento oftalmológico e organização da agenda/fila
- Manutenção do prontuário / ficha clínica no sistema
- Controle financeiro básico das consultas e despesas da clínica
- Segurança da conta (autenticação, auditoria mínima de acesso)

## 4. Bases legais (indicação — validar com jurídico)

- Execução de procedimentos relacionados à saúde / tutela da saúde (art. 7º e 11 da LGPD, conforme o caso)
- Cumprimento de obrigação legal ou regulatória, quando houver
- Legítimo interesse da clínica na organização administrativa, quando aplicável e com testes de balanceamento

## 5. Papéis

- A **clínica** é responsável pelas decisões sobre coleta, finalidade e compartilhamento dos dados dos pacientes e pela relação com o titular.
- A **Marag** (ou operador técnico do hosting) trata os dados **sob instrução da clínica**, para disponibilizar e manter o software e a infraestrutura do piloto.

## 6. Segurança (mínimo do piloto)

- Acesso por usuário autenticado (JWT) e perfil (admin / recepção / profissional)
- Isolamento dos dados da clínica em banco próprio
- Comunicação HTTPS em staging/produção
- Backup periódico (central + banco da clínica) com retenção acordada e teste de restore
- Sem telão público de chamada neste piloto

## 7. Retenção e exclusão

- Durante o piloto, os dados permanecerão enquanto necessários às finalidades acima.
- Após o piloto ou a pedido da clínica/titular (quando cabível), as partes definirão: exportação, anonimização ou exclusão, em prazo razoável.
- Exclusão definitiva de paciente no sistema poderá ser entregue em versão posterior; no piloto, inativação / restrição de acesso pode ser o mecanismo imediato.

## 8. Incidentes

Em caso de incidente de segurança com risco relevante aos titulares, a clínica será avisada sem demora indevida, para as comunicações cabíveis à ANPD e aos titulares.

## 9. Confidencialidade

Usuários da clínica devem manter sigilo dos dados acessados e não compartilhar senhas/tokens.

## 10. Aceite

Declaramos ciência deste termo para o **piloto assistido** do Marag.

| | Clínica | Marag / Operador |
|--|---------|------------------|
| Nome | | |
| Cargo | | |
| Assinatura | | |
| Data | | |

---

*Documento gerado no soft launch Marag — revisar com advogado antes de uso com pacientes reais.*
