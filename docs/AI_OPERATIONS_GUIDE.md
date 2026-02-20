# AI Operations Guide - Meetrix SaaS

Este guia define os procedimentos operacionais padrão para a IA (Antigravity) no projeto Meetrix.

> [!IMPORTANT]
> **Idioma de Comunicação**: A IA deve se comunicar com o usuário obrigatoriamente em **Português do Brasil (PT-BR)**.

## 🚀 Fluxo de Deployment & Verificação

1. **Commit & Push**: Realize o commit e push das alterações para a branch `main`.
   - **IMPORTANTE**: Verifique se o comando `git push origin main` no terminal terminou com sucesso (Exit Code 0). Se houver erro de credencial ou bloqueio de segredos, resolva localmente antes de prosseguir.
2. **Monitoramento (GitHub Actions)**: Após confirmar o push no terminal, monitore o deploy.
   - **URL**: [https://github.com/helberfmelo/meetrix/actions/workflows/deploy.yml](https://github.com/helberfmelo/meetrix/actions/workflows/deploy.yml)
   - **Protocolo de Polling**: Você **DEVE** atualizar (refresh) a página do GitHub Actions manualmente a cada 15 segundos. Não limite-se a esperar; a atualização é necessária para ver o status real.
   - **Confirmação**: Prossiga apenas quando o job "🎉 Deploy" estiver com o checkmark verde (sucesso).
3. **Validação em Produção**: Após o sucesso no GitHub, siga esta ordem:
   - **Logs**: Acesse `read_logs.php` para garantir que nenhum erro de bootstrap (ex: `headers already sent`) surgiu.
   - **Migrações**: Se houver mudanças de banco, acesse `migrate_sovereign.php`.
   - **Funcionalidade**: Teste as URLs finais (Home, Login, Dashboard).

## 🔐 Credenciais de Acesso

### Ambiente Local
- **URL**: `http://localhost:8000` (ou similar)
- **Login**: `admin@meetrix.test`
- **Senha**: `password`

### Ambiente Produção (Master Admin)
- **URL**: [https://meetrix.opentshost.com/login](https://meetrix.opentshost.com/login)
- **Login**: `admin@meetrix.pro`
- **Senha**: `MeetrixMaster2026Sovereign!#`

## 🧪 Protocolo de Testes e E-mail

- **Limpeza de Formulários**: Antes de digitar qualquer valor em um `input` (Login ou formulários em geral), a IA deve **sempre** verificar se já existe conteúdo e apagá-lo completamente para evitar interferência de auto-completar do navegador.
- **Protocolo de Testes**: Use preferencialmente `tester@meetrix.pro` ou e-mails temporários para fluxos de registro.
- **Falha em Produção**: Se um teste falhar ou terminar, **limpe os dados residuais** antes de um novo ciclo.
- **Limpeza**: Use o script `migrate_sovereign.php` para um reset total ("Nuclear") ou crie scripts PHP temporários para deletar registros específicos.

## 🗄️ Manutenção do Banco de Dados

1. **Migrações**: Use migrações padrão do Laravel sempre que possível.
2. **Ajustes Ad-hoc**: Em produção, se necessário, crie scripts PHP na pasta `public/` (ex: `fix_db.php`), execute-os via navegador e **APAGUE-OS** imediatamente após o uso.
3. **Paridade**: Garanta que o banco de produção esteja sempre alinhado com o `DatabaseSeeder.php` e as migrations locais.

## 🤖 Orientações Adicionais para a IA

- **Proatividade**: Deploys, testes e ajustes finos devem ser feitos autonomamente pela IA, reportando o progresso.
- **Segurança**: Nunca exponha segredos (`.env`) em logs ou documentação pública.
- **Stripe/Google**: Verifique sempre as chaves no `.env` local antes de assumir que o fluxo de integração funcionará em produção.
- **Soft Deletes**: A tabela `bookings` utiliza SoftDeletes. Lembre-se disso ao consultar/limpar dados.

## 🌐 Infraestrutura & Roteamento (Produção)

> [!IMPORTANT]
> O ambiente de produção possui uma configuração de path mapping específica que deve ser seguida para migrações e acessos diretos.

- **Domínio Principal (SPA/API)**: `https://meetrix.opentshost.com`
  - Aponta internamente para a pasta `/public/` do projeto.
  - No servidor HostGator, o caminho físico é `/public_html/meetrix/public`.
- **Acesso Direto (Scripts/Manutenção)**: `https://opentshost.com/meetrix/`
  - Permite acessar arquivos e subpastas que estão na **raiz** do projeto (fora da `public`).
  - **Migration Sync**: [https://opentshost.com/meetrix/migrate_sovereign.php](https://opentshost.com/meetrix/migrate_sovereign.php)
  - Utilize este caminho para rodar scripts de manutenção `fix_db.php` ou resets de cache.

---
*Última atualização: 2026-02-20*
