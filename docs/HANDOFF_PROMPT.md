# PROMPT DE CONTINUAÇÃO (HANDOFF)

**OBJETIVO**: Finalizar a Fase 9 do projeto Meetrix, resolvendo os erros 405 (Cupom) e 500 (Dashboard) em Produção.

**INSTRUÇÕES OBRIGATÓRIAS**:
1. Leia **TODOS** os arquivos da pasta `docs/` para entender a arquitetura e o estado atual.
2. Verifique o arquivo `docs/implementation_plan.md` para o guia técnico.
3. Examine `docs/TASK.md` para ver o progresso.

**RESUMO TÉCNICO**:
- **Ambiente Local**: ESTÁVEL. Todos os testes passam. O código reflete a versão final da Fase 9 com banco padronizado.
- **Ambiente Produção**: CRÍTICO. O servidor parou de executar PHP (exibe código-fonte) e o banco de dados está travado por conflitos de migração.
- **Meta Imediata**: 1) Restaurar o `.htaccess` para que o PHP volte a funcionar. 2) Rodar o script `migrate_fresh` em produção para alinhar o banco.

**ARQUIVO DE CONFIGURAÇÃO CRÍTICO**:
- `public/.htaccess`: Provável causa da falha de PHP em produção.

**AVISO**: Não tente correções incrementais no banco de produção; use `migrate:fresh` via script manual para garantir paridade total com o ambiente local já validado.
