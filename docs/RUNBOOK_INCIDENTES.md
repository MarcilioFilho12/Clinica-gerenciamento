# Runbook de incidentes — Marag (piloto)

Canal de suporte interno: definir WhatsApp/e-mail do responsável técnico.

## API fora do ar

**Sintoma:** front com erro de conexão / timeout.

1. Abrir `https://<api>/up` — se falhar, API ou rede.  
2. Railway → logs do serviço API.  
3. Redeploy do último commit **bom** (rollback).  
4. Avisar a clínica: “sistema em manutenção, use papel até aviso”.

## Banco fora / lento

1. Checar plugin MySQL no Railway (CPU/conexões).  
2. Se corrompido / drop acidental → **restore do último backup** (Fase 3).  
3. Não rodar `migrate:fresh`.

## Deploy quebrou produção/staging

1. Rollback de imagem/commit (API + front).  
2. Confirmar `/up` + login.  
3. Só então investigar o commit ruim em branch.

## Dados apagados por usuário

1. Soft delete: verificar `deleted_at` (users/consultas quando aplicável).  
2. Se hard delete / sem soft → restore backup point-in-time para **outro** DB e recuperar registro.  
3. Registrar incidente (quando / quem / o quê).

## Backup (mínimo piloto)

**Frequência:** diária (central + tenant).

```bash
# Exemplo local / jump host com mysql client
mysqldump -h HOST -u USER -p marag_central > marag_central_YYYYMMDD.sql
mysqldump -h HOST -u USER -p marag_clinic_piloto > marag_clinic_piloto_YYYYMMDD.sql
```

**Restore de teste (obrigatório 1× antes de dados reais):**

```bash
mysql -h HOST -u USER -p marag_central_restore < marag_central_YYYYMMDD.sql
# apontar CENTRAL_DB_* temporário, validar login branding
```

Guardar dumps **fora** do volume efêmero do app (S3, drive criptografado, etc.).

## Contato

| Papel | Nome | Contato |
|-------|------|---------|
| Técnico Marag | | |
| Admin clínica | | |
