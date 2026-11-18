# MDAWiki frontend

Vue 3, TypeScript, Vite, Vue Router, Bootstrap 5 e HTML estático para GitHub Pages. Logo, ícones, tipografia e composição dos artigos preservam o visual do legado. Todas as páginas do aplicativo usam o novo frontend e a API PHP independente.

Inclui login/logout, cadastro/ativação, recuperação/redefinição de senha, perfil/foto, alteração de senha/e-mail, editor de código wiki e HTML, prévia, envio/troca/remoção de imagens, histórico/comparação/restauração, busca, categorias/listas, estatísticas, usuários administrativos, reprocessamento e doação pelo PayPal. Não há navegação ou formulários enviados ao PHP antigo.

## Desenvolvimento

```sh
npm ci
npm run dev -- --port 5174
```

O Vite encaminha `/api` à API configurada em `API_PROXY_TARGET` (padrão de produção em `config/public.json`). Para API PHP local, use `API_PROXY_TARGET=http://127.0.0.1:8080`. Configure essa origem/porta em ALLOWED_ORIGINS da API e SESSION_SECURE=false para HTTP local. O ambiente de produção usa `VITE_API_BASE_URL`, `VITE_SITE_URL` e `VITE_BASE_PATH`; valores públicos padrão estão em `config/public.json`.

## Publicação

**Publique/configure primeiro a nova API e importe as imagens**, seguindo o [guia do backend](https://github.com/maycorolbuche/mdawiki-api/blob/main/docs/deployment.md). Contas precisam de APP_KEY estável, e-mail configurado, banco com permissão de escrita e armazenamento persistente. O frontend não armazena senhas nem tokens de sessão em localStorage; usa cookie HttpOnly e CSRF da API.

Use domínio próprio do mesmo site da API, por exemplo `site.mda.wiki.br` com `api.mda.wiki.br`. Autenticação a partir de `github.io` pode ser bloqueada pelas políticas de cookies de terceiros.

O workflow publica somente após merge em main. A build consulta a API pública e gera HTML/metatags por artigo, páginas estáticas do sistema e um `404.html` que mantém a URL e consulta a API para conteúdo novo. A atualização da tela independe da próxima build; metatags do código-fonte publicado aguardam o deploy. O HTTP inicial de rotas ainda não geradas permanece 404 no GitHub Pages. As páginas de conta têm noindex e não recebem dados pessoais na build; edição e histórico carregam informações somente após autenticação.

O conteúdo é sanitizado tanto no build quanto no navegador, com CSS restrito, proteção de links e vídeos somente de embeds YouTube isolados. Fontes são empacotadas. O frontend não depende de jQuery ou do Bootstrap antigo.

## Validação

`npm run typecheck`, `npm test` e `npm run test:browser`. Para build de testes: `BUILD_FIXTURE=tests/fixtures/pages.json npm run build`. O deploy nunca usa fixtures. Os testes de navegador cobrem também login, edição/conflito, restauração, busca e perfil; as capturas desktop/mobile ficam no artefato `browser-layout` do Actions.
