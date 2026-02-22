# Smoke Test Final em Produção (2026-02-22)

## Escopo validado
- Deploy com pipeline GitHub Actions (build + FTP + pós-deploy SSH)
- Migração e seed automática de pricing
- Catálogo de preços por região/moeda (BRL, USD, EUR)
- Reflexo de pricing no front e no admin master
- Coerência de região/moeda em tenant

## Ambiente
- `BASE_URL`: `https://meetrix.opentshost.com`
- Migração aplicada: `2026_02_22_030000_create_pricing_locale_currency_maps_table`
- Registros em `pricing_locale_currency_maps`: `15`

## Evidências funcionais
1. API `country_code=BR`:
- Esperado: `region=BR`, `currency=BRL`
- Obtido: `PASS`

2. API `country_code=US`:
- Esperado: `region=USD`, `currency=USD`
- Obtido: `PASS`

3. API `country_code=PT`:
- Esperado: `region=EUR`, `currency=EUR`
- Obtido: `PASS`

4. API `locale=fr-FR`:
- Esperado: `currency=EUR` (sem remapeamento customizado)
- Obtido: `PASS`

5. UI Master Admin:
- Esperado: seção de gestão de planos/moedas carregada com matriz BR/USD/EUR
- Obtido: `PASS`

6. Front público (locale europeu):
- Esperado: símbolo `€` e preços coerentes com catálogo
- Obtido: `PASS`

7. Tenant/account:
- Esperado: região/moeda coerentes com resolução aplicada
- Obtido: `PASS`

## Resultado
- **GO para produção**
- Nenhum bug funcional crítico encontrado no fluxo de pricing/região/moeda.

## Observações operacionais
- O workflow de deploy foi mantido em modo **FTP + SSH pós-deploy** com:
  - `php artisan optimize:clear`
  - `php artisan migrate --force`
  - `php artisan db:seed --class=GeoPricingSeeder --force`
- Segredos ficam exclusivamente no GitHub Actions Secrets.
