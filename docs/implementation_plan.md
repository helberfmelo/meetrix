# Plano de Implementação: Fase 9 & Recuperação de Produção

## Objetivo
Resolver os bugs de validação de Cupom (405) e Dashboard (500) identificados no teste E2E.

## O Que Foi Feito (Status Local: 100% OK)
1. **Padronização de Dados**: Todas as referências a `page_id` foram alteradas para `scheduling_page_id` para evitar ambiguidades.
2. **Dashboard**: Corrigida a lógica de contagem de bookings no `DashboardController` e adicionado suporte a `softDeletes` na tabela `bookings`.
3. **Cupons**: Corrigida a validação no `SubscriptionController` para aplicar descontos de 100% ignorando o Stripe.
4. **Testes**: Criados e validados `tests/Feature/CouponTest.php` e `tests/Feature/DashboardTest.php`. Ambos estão **verdes**.
5. **Fluxo de Auth/Onboarding/Checkout**: Ajustado frontend para:
   - Persistir sessão após cadastro antes de avançar no onboarding;
   - Redirecionar login para `/dashboard` apenas quando onboarding estiver completo;
   - Enviar payload correto de checkout (`interval`, `coupon_code`) e tratar `redirect_url`/`checkout_url`.
6. **Scripts Soberanos**: Endurecimento de `migrate_sovereign.php` (raiz e `public/`) para evitar falhas de header quando houver saída inesperada no bootstrap.
7. **Seeder Idempotente**: `DatabaseSeeder.php` ajustado com `updateOrCreate` em usuário, página, disponibilidade e tipos de agendamento.

## Status de Produção (Atualizado em 2026-02-20)
1. **Deploy**: Pipeline `deploy.yml` concluído com sucesso.
2. **Sincronização**: `https://opentshost.com/meetrix/migrate_sovereign.php` executado com `--- SYNC COMPLETE ---`.
3. **Validação E2E Real**:
   - **Cliente existente**: login -> onboarding (após reset) -> checkout com `cupom100` -> `/dashboard?subscription=free_success` -> relogin em `/dashboard`.
   - **Cliente novo**: cadastro -> onboarding -> checkout com `cupom100` -> `/dashboard?subscription=free_success` -> relogin em `/dashboard`.
   - **Dashboard/Cupons**: carregamento confirmado; `cupom100` visível e `times_used` incrementando.

## Arquivos Chave Preparados
- `public/migrate_sovereign.php`: Script para forçar migração, seed e limpeza de cache com proteção contra saída inesperada no bootstrap.
- `routes/web.php`: Roteamento configurado para priorizar `/sys/` e isolar o SPA.
