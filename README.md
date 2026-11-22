# MDAWiki frontend

Vue 3, TypeScript, Vite, Vue Router, Bootstrap 5 e HTML estático para GitHub Pages. Logo, ícones, tipografia e composição dos artigos preservam o visual do legado. Todas as páginas do aplicativo usam o novo frontend e a API PHP independente.

Inclui login/logout, cadastro/ativação, recuperação/redefinição de senha, perfil/foto, alteração de senha/e-mail, editor de código wiki e HTML, prévia, envio/troca/remoção de imagens, histórico/comparação/restauração, busca, categorias/listas, estatísticas, usuários administrativos, reprocessamento e doação pelo PayPal. Não há navegação ou formulários enviados ao PHP antigo.

## Desenvolvimento

```sh
npm ci
npm run dev -- --port 5174
```

No servidor de desenvolvimento, o navegador usa `/api` na mesma origem do front para manter o cookie e o token CSRF na mesma sessão. O Vite encaminha as chamadas para `API_PROXY_TARGET`, quando definido, ou para `VITE_API_BASE_URL`. O padrão fica em `config/public.json`. Para uma API PHP local, use `VITE_API_BASE_URL=http://127.0.0.1:8080` no front e `APP_ENV=local` e `SESSION_SECURE=false` na API para testes em HTTP. Em produção, o build usa `VITE_API_BASE_URL`, `VITE_SITE_URL` e `VITE_BASE_PATH`.

## Publicação

**Publique/configure primeiro a nova API e importe as imagens**, seguindo o [guia do backend](https://github.com/maycorolbuche/mdawiki-api/blob/main/docs/deployment.md). Contas precisam de APP_KEY estável, e-mail configurado, banco com permissão de escrita e armazenamento persistente. O frontend não armazena senhas nem tokens de sessão em localStorage; usa cookie HttpOnly e CSRF da API.

Use domínio próprio do mesmo site da API, por exemplo `mda.wiki.br` com `api.mda.wiki.br`. Autenticação a partir de `github.io` pode ser bloqueada pelas políticas de cookies de terceiros.

O workflow publica somente após merge em main. A build consulta a API pública e gera HTML/metatags por artigo, páginas estáticas do sistema e um `404.html` que mantém a URL e consulta a API para conteúdo novo. A atualização da tela independe da próxima build; metatags do código-fonte publicado aguardam o deploy. O HTTP inicial de rotas ainda não geradas permanece 404 no GitHub Pages. As páginas de conta têm noindex e não recebem dados pessoais na build; edição e histórico carregam informações somente após autenticação.

O conteúdo é sanitizado tanto no build quanto no navegador, com CSS restrito, proteção de links e vídeos somente de embeds YouTube isolados. Fontes são empacotadas. O frontend não depende de jQuery ou do Bootstrap antigo.

## Validação

`npm run typecheck`, `npm test` e `npm run test:browser`. Para build de testes: `BUILD_FIXTURE=tests/fixtures/pages.json npm run build`. O deploy nunca usa fixtures. Os testes de navegador cobrem também login, edição/conflito, restauração, busca e perfil; as capturas desktop/mobile ficam no artefato `browser-layout` do Actions.

## Google Analytics e AdSense

`config/google.json` mantém o publisher AdSense do legado: `ca-pub-4184100205220975`. A build publica a metatag de verificação e `public/ads.txt` com o mesmo publisher. O script de anúncios automáticos é inserido uma única vez no head e permanece disponível na navegação SPA; os formatos e posições continuam definidos no painel AdSense, como no legado (não havia slots fixos no código).

O Analytics legado `UA-75685104-1` é Universal Analytics, encerrado pelo Google. Para ativar a coleta atual, informe o **ID de medição GA4** (`G-...`) em uma destas opções:

- GitHub → Settings → Secrets and variables → Actions → Variables: `VITE_GOOGLE_ANALYTICS_ID`; depois execute uma nova build.
- Desenvolvimento/configuração local: `VITE_GOOGLE_ANALYTICS_ID` no `.env`.
- Configuração versionada: `analyticsId` em `config/google.json`.

Sem ID GA4, o AdSense funciona independentemente e o Analytics permanece desativado. IDs `UA-...` são rejeitados com uma mensagem explícita na build.

A navegação é acompanhada manualmente por `page_view`, incluindo a abertura inicial e as mudanças de rota. Para evitar duplicação, em **GA4 → Administrador → Fluxos de dados → Web → Medição otimizada → Visualizações de página → Configurações avançadas**, desative **Alterações de página com base em eventos do histórico do navegador**. A configuração do código já usa `send_page_view: false` para a abertura inicial. Como no header legado, as telas de edição não enviam visualizações. Query strings e fragmentos (como tokens de acesso e recuperação) não entram nos parâmetros de URL enviados pelo nosso código.

A inicialização automática só ocorre em build de produção e no domínio configurado em `SITE_URL`. Servidor Vite, localhost e prévias em outro domínio não enviam métricas nem carregam anúncios reais. Bloqueio dos scripts por extensões não interrompe a wiki.

Referências oficiais: [migração do Universal Analytics](https://support.google.com/analytics/answer/11583528?hl=pt-BR), [pageviews manuais e eventos de histórico](https://developers.google.com/analytics/devguides/collection/ga4/views), [ads.txt](https://support.google.com/adsense/answer/12171612?hl=pt-BR).
