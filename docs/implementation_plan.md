# Plano de Implementação: Fase 9 & Recuperação de Produção

## Objetivo
Resolver os bugs de validação de Cupom (405) e Dashboard (500) identificados no teste E2E.

## O Que Foi Feito (Status Local: 100% OK)
1. **Padronização de Dados**: Todas as referências a `page_id` foram alteradas para `scheduling_page_id` para evitar ambiguidades.
2. **Dashboard**: Corrigida a lógica de contagem de bookings no `DashboardController` e adicionado suporte a `softDeletes` na tabela `bookings`.
3. **Cupons**: Corrigida a validação no `SubscriptionController` para aplicar descontos de 100% ignorando o Stripe.
4. **Testes**: Criados e validados `tests/Feature/CouponTest.php` e `tests/Feature/DashboardTest.php`. Ambos estão **verdes**.

## O Que Falta (Status Produção: CRÍTICO)
1. **Restaurar Execução PHP**: O servidor de produção está exibindo código-fonte. É necessário restaurar um `.htaccess` funcional que reative o interpretador PHP do HostGator.
2. **Sincronização de Banco**: O banco de produção está em conflito. Deve-se rodar `migrate:fresh --force` para reconstruir a estrutura idêntica à local.
3. **Teste E2E Final**: Validar o fluxo `Registro -> Cupom100 -> Resumo Dash` no ambiente real.

## Arquivos Chave Preparados
- `public/migrate_sovereign.php`: Script para forçar migração e limpar cache (atualmente inacessível pelo erro do PHP).
- `routes/web.php`: Roteamento configurado para priorizar `/sys/` e isolar o SPA.
