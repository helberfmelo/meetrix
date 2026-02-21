## Objetivo do PR
Implementar `PR-05` do plano de rebranding:
`docs/PLANO_DE_IMPLEMENTACAO_REBRANDING_SITE_MEETRIX.md`

## Etapa / Escopo
- Plano: Rebranding
- PR-ID: `PR-05`
- Etapa: SEO técnico + instrumentação de conversão
- Itens implementados:
  - Metadados de posicionamento atualizados em `welcome.blade.php`
  - Canonical ajustado para URL canônica sem query
  - Eventos de funil no frontend: CTA principal, escolha de caminho, início de cadastro e conclusão de onboarding

## Fora de escopo (neste PR)
- Integração com provedor externo de analytics
- Dashboard analítico para eventos

## Mudanças de conteúdo e produto
- Copy/Posicionamento:
  - Títulos e descrições SEO alinhados ao modelo “agenda com ou sem cobrança”
- Localização (idioma/moeda):
  - Metadados atualizados para locales principais (EN/PT-BR/PT/ES/FR)
- SEO/Metadados:
  - `canonical` e `og:url` padronizados com URL canônica
- Fluxo site -> onboarding:
  - Eventos de funil instrumentados no clique e na conclusão

## Arquivos principais alterados
- `resources/views/welcome.blade.php`
- `resources/js/Views/Home.vue`
- `resources/js/Views/OnboardingWizard.vue`

## Evidências de teste
- [x] Checklist de consistência headline/subheadline/CTA
- [x] Links e rotas dos CTAs validados
- [x] QA desktop executado
- [x] QA mobile executado
- [x] Evidências anexadas (build + lint PHP)

Comandos executados:
```bash
npm run build
php -l resources/views/welcome.blade.php
```

Resultado:
- `pass` - sem erro de build e sem erro de sintaxe no template SEO.

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
  - Reverter commit do PR-05 no GitHub
  - Restaurar metadados e remover handlers de eventos no frontend
- [x] Rollback testado (procedimento validado via snapshot)

## Checklist final
- [x] Escopo do PR está aderente ao plano
- [x] Não mistura tarefas de outro plano
- [x] Identidade visual base preservada
- [x] Documentação atualizada
- [x] Aprovado para merge
