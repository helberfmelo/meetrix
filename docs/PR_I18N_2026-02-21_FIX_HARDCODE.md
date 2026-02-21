## Objetivo do PR
Corrigir textos hardcoded em portugues detectados em producao e alinhar i18n:
- CTA de cobranca no Home e Account.
- Estados de assinatura e senha no Account.
- Sufixo de minutos no BookingPage.

## Etapa / Escopo
- Plano: Pente fino de idiomas
- Lote: Hotfix i18n (hardcoded)
- Itens implementados:
  - Substituicao de strings fixas por chaves i18n.
  - Inclusao de novas chaves `home.activate_billing`, `account.*` e `booking.minutes_short` em todos os locales.

## Fora de escopo (neste PR)
- Outras strings hardcoded fora do escopo descrito no relatorio do Atigravity.

## Mudancas tecnicas
- Frontend:
  - Atualizacao de `resources/js/Views/Home.vue`.
  - Atualizacao de `resources/js/Views/AccountView.vue`.
  - Atualizacao de `resources/js/Views/BookingPage.vue`.
  - Atualizacao de `resources/js/locales/*.json`.
- Backend:
  - Sem alteracoes.
- Banco:
  - Sem alteracoes.
- Config/Infra:
  - Sem alteracoes.

## Arquivos principais alterados
- `resources/js/Views/Home.vue`
- `resources/js/Views/AccountView.vue`
- `resources/js/Views/BookingPage.vue`
- `resources/js/locales/*.json`
- `docs/PR_I18N_2026-02-21_FIX_HARDCODE.md`

## Evidencias de teste
- [x] Build frontend executado
- [ ] Smoke visual desktop e mobile (pt-BR, pt, es, fr, de, it, ja, ko, ru, tr, zh, zh-CN, ar) executado

Comandos executados:
```bash
npm run build
```

Resultado:
- Build OK (vite build)

## Gate
- [ ] Zero textos hardcoded em portugues nos trechos corrigidos
- [x] Build verde
- [ ] Smoke visual em producao validado

## QA em producao (Antigravity)
- Relatorio: `docs/RELATORIO_QA_VISUAL_I18N_PRODUCAO.md`
- Resultado: falhou para `tr`, `zh`, `ar` na Home e nao encontrou o sufixo de duracao nas Booking Pages.
- Acao corretiva: habilitar `tr`, `zh`, `ar` no roteamento/i18n e ajustar LanguageSwitcher.

## Observacoes
- Este PR implementa as correcoes descritas no relatorio do Atigravity.
- Correcao adicional aplicada: habilitar `tr`, `zh`, `ar` no roteamento/i18n e LanguageSwitcher.
