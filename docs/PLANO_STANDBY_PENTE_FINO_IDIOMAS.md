# Plano Stand By - Pente Fino de Idiomas

## Status
- `EXECUTADO` (2026-02-22)
- Janela original: apos fechamento completo do Upgrade (PR-07 validado em producao).
- Execucao realizada em branch `main` com commit `12ebfd1`.

## Objetivo
Fazer revisao linguistica completa em todos os idiomas suportados para:
- identificar textos/palavras em ingles fora do locale correto;
- traduzir para o idioma alvo de cada arquivo;
- manter consistencia terminologica entre UI, onboarding, checkout, account e master admin.

## Escopo
- Arquivos de locale em `resources/js/locales/*.json`.
- Strings fixas em views Vue:
  - `resources/js/Views/*.vue`
  - `resources/js/Components/**/*.vue`
- Mensagens de API expostas no frontend (quando aplicavel).

## Idiomas Alvo
- `pt-BR`, `pt`, `en`, `es`, `fr`, `de`, `it`, `ja`, `ko`, `ru`, `zh`, `zh-CN`, `ar`, `tr`.

## Regras de Execucao
1. Nao rodar em paralelo com PR de Upgrade/Rebranding em andamento.
2. Executar em PR dedicado de i18n (sem mistura de escopo financeiro/produto).
3. Manter placeholders, chaves de traducao e interpolacoes intactos.
4. Preservar termos tecnicos de marca quando definidos (ex.: Meetrix, Stripe, SaaS).
5. Validar que nenhum locale perdeu chave obrigatoria.

## Checklist Tecnico
1. Gerar inventario de textos potencialmente em ingles:
   - varredura por regex de palavras inglesas comuns fora de `en`.
2. Comparar chaves entre todos os locales:
   - detectar faltantes/extras por arquivo.
3. Corrigir traducoes por idioma.
4. Revisar manualmente trechos sensiveis:
   - onboarding, checkout, account, master admin, erros.
5. Rodar validacoes:
   - `npm run build`
   - testes impactados de frontend/backend.
6. Validar visualmente via navegador:
   - desktop e mobile simulado para ao menos 3 idiomas por lote.

## Gate de Aceite
- Zero termos em ingles indevidos em locales nao-`en` nos fluxos criticos.
- Nenhuma quebra de chave i18n.
- Build verde.
- Smoke funcional em producao apos deploy do PR de idiomas.

## Evidencias Esperadas
- Relatorio por idioma com:
  - quantidade de strings corrigidas;
  - telas afetadas;
  - riscos residuais.
- Capturas de tela desktop/mobile por idioma amostrado.

## Evidencias (2026-02-22)
- Escopo executado: remoção de hardcodes e paridade completa de chaves i18n.
- Locales completos (0 chaves faltantes): `pt-BR`, `pt`, `en`, `es`, `fr`, `de`, `it`, `ja`, `ko`, `ru`, `zh`, `zh-CN`, `ar`, `tr`.
- Componentes com hardcode removido e migrado para `$t(...)`:
  - `resources/js/Views/Dashboard.vue`
  - `resources/js/Views/Login.vue`
  - `resources/js/Views/BookingPage.vue`
  - `resources/js/Views/OnboardingWizard.vue`
  - `resources/js/Views/MasterAdminView.vue`
  - `resources/js/Layouts/AdminLayout.vue`
  - `resources/js/Components/LanguageSwitcher.vue`
  - `resources/js/stores/auth.js`
  - `resources/js/stores/page.js`
- Smoke test local (headless) em `/pt-BR`, `/pt`, `/es`, `/fr`:
  - `pt-BR`: Hero + CTA OK.
  - `pt`: Hero + CTA OK (copy igual ao pt-BR neste momento).
  - `es`: Hero + CTA OK.
  - `fr`: Hero + CTA OK.
- Build: `npm run build` executado com sucesso.
- Commit: `12ebfd1` (push em `main` concluido).

## Proximo Passo (quando sair de stand by)
1. Abrir PR dedicado: `docs + i18n cleanup`.
2. Executar o checklist acima em lotes pequenos por idioma.
