# Runbook de Operacao e Incidentes - Upgrade PR-07

## Objetivo
Consolidar o fechamento do ciclo de upgrade com procedimentos operacionais para:
- monitoramento de deploy e saude;
- tratamento de incidentes financeiros;
- rollback controlado para estado estavel.

## Escopo operacional coberto
- Home, Login, Onboarding, Checkout, Dashboard e pagina publica `/p/{slug}`.
- APIs de conta e master admin (`/api/account/*`, `/api/super-admin/*`).
- KPIs financeiros do bloco `financial` em `/api/super-admin/overview`.

## Rotina de verificacao pos-deploy
1. Confirmar run do `deploy.yml` em `success` (polling de 15s).
2. Validar rotas criticas em producao com `200`.
3. Validar APIs criticas autenticadas com `200`.
4. Checar logs operacionais em `read_logs.php`:
   - sem `Fatal error`;
   - sem `headers already sent`.
5. Validar dashboard master admin:
   - cards de KPI carregados;
   - tabelas de `Receita por Moeda` e `GMV por Pais` sem quebra.

## Hardening aplicado no PR-07
- Cache de KPI financeiro para reduzir custo de agregacao:
  - chave: `financial:kpis:snapshot:v1`
  - TTL: `ANALYTICS_KPI_CACHE_TTL_SECONDS` (default `60`).
- Limite de volume do breakdown por pais:
  - `ANALYTICS_KPI_COUNTRY_LIMIT` (default `25`).
- Fallback resiliente:
  - em falha de agregacao, resposta retorna `degraded_mode=true` e payload seguro.

## Procedimentos de incidente

### 1) Falha de KPI financeiro (degraded mode)
Sintoma:
- `/api/super-admin/overview` retorna `financial.degraded_mode=true`.

Acao imediata:
1. Consultar `read_logs.php` e identificar `error_code=financial_kpi_snapshot_failed`.
2. Reduzir risco operacional:
   - definir `ANALYTICS_KPI_CACHE_TTL_SECONDS=0` temporariamente para bypass de cache, se necessario.
3. Revalidar endpoint com refresh forçado:
   - `/api/super-admin/overview?refresh_kpis=1`
4. Se persistir, abrir hotfix e manter operacao principal (agendamento/cobranca) ativa.

### 2) Degradacao de performance no Master Admin
Sintoma:
- carregamento lento no painel financeiro.

Acao imediata:
1. Aumentar `ANALYTICS_KPI_CACHE_TTL_SECONDS` (ex.: `120`).
2. Reduzir `ANALYTICS_KPI_COUNTRY_LIMIT` (ex.: `10`).
3. Validar novamente `/api/super-admin/overview`.

### 3) Erro critico funcional em producao
Sintoma:
- regressao em fluxos criticos (login/onboarding/checkout/dashboard/pagina publica).

Acao imediata:
1. Reverter PR no GitHub (quando aplicavel).
2. Em incidente severo, restaurar estado base:
   - tag: `snapshot/pre-upgrade-2026-02-21`
3. Executar novo deploy do estado estavel e repetir smoke completo.

## Checklist rapido de fechamento
- Deploy verde.
- Smoke HTTP completo.
- APIs de conta/admin validadas.
- Logs limpos.
- Browser QA desktop + mobile simulado aprovado.
- Evidencia registrada em `docs/PR_UPGRADE_PR-07.md`.
