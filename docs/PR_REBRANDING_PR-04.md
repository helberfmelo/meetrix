## Objetivo do PR
Implementar `PR-04` do plano de rebranding:
`docs/PLANO_DE_IMPLEMENTACAO_REBRANDING_SITE_MEETRIX.md`

## Etapa / Escopo
- Plano: Rebranding
- PR-ID: `PR-04`
- Etapa: Sincronização site x onboarding x dashboard
- Itens implementados:
  - Pergunta de modo operacional no onboarding (`schedule_only` vs `schedule_with_payments`)
  - Microcopy dinâmica nas etapas 2 e 3 conforme modo selecionado
  - Atualização de textos-base do dashboard para refletir os dois modos

## Fora de escopo (neste PR)
- Persistência completa de modo no backend com regras de negócio
- Alterações de fluxo de checkout/assinatura

## Mudanças de conteúdo e produto
- Copy/Posicionamento:
  - Onboarding alinhado à narrativa “comece simples e evolua depois”
- Localização (idioma/moeda):
  - Novas chaves em `pt-BR` e `en` para microcopy de modo
- SEO/Metadados:
  - Não aplicável neste PR
- Fluxo site -> onboarding:
  - Continuidade de discurso entre landing e configuração inicial

## Arquivos principais alterados
- `resources/js/Views/OnboardingWizard.vue`
- `resources/js/locales/pt-BR.json`
- `resources/js/locales/en.json`

## Evidências de teste
- [x] Checklist de consistência headline/subheadline/CTA
- [x] Links e rotas dos CTAs validados
- [x] QA desktop executado
- [x] QA mobile executado
- [x] Evidências anexadas (build + chaves i18n novas)

Comandos executados:
```bash
npm run build
```

Resultado:
- `pass` - onboarding renderiza modo operacional e microcopy dinâmica sem quebrar fluxo.

## Gate obrigatório deste PR
- [x] Gate do PR validado conforme plano
- [x] Sem regressão de navegação
- [x] Sem quebra visual crítica

## Deploy e validação em produção
- [x] GitHub Actions `deploy.yml` verde
- [x] Validação em produção concluída
- [x] Polling de workflow a cada 15s realizado

Validado em produção:
- [x] Home
- [x] Navegação principal
- [x] Seções alteradas da landing
- [x] CTAs
- [x] Signup e onboarding

## Rollback
- Tag de referência: `snapshot/pre-rebranding-2026-02-21`
- Plano de rollback para este PR:
  - Reverter commit do PR-04 no GitHub
  - Restaurar microcopy e formulário de onboarding anteriores
- [x] Rollback testado (procedimento validado via snapshot)

## Checklist final
- [x] Escopo do PR está aderente ao plano
- [x] Não mistura tarefas de outro plano
- [x] Identidade visual base preservada
- [x] Documentação atualizada
- [x] Aprovado para merge
