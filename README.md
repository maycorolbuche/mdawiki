# .

This template should help get you started developing with Vue 3 in Vite.

## Recommended IDE Setup

[VS Code](https://code.visualstudio.com/) + [Vue (Official)](https://marketplace.visualstudio.com/items?itemName=Vue.volar) (and disable Vetur).

## Recommended Browser Setup

- Chromium-based browsers (Chrome, Edge, Brave, etc.):
  - [Vue.js devtools](https://chromewebstore.google.com/detail/vuejs-devtools/nhdogjmejiglipccpnnnanhbledajbpd)
  - [Turn on Custom Object Formatter in Chrome DevTools](http://bit.ly/object-formatters)
- Firefox:
  - [Vue.js devtools](https://addons.mozilla.org/en-US/firefox/addon/vue-js-devtools/)
  - [Turn on Custom Object Formatter in Firefox DevTools](https://fxdx.dev/firefox-devtools-custom-object-formatters/)

## Customize configuration

See [Vite Configuration Reference](https://vite.dev/config/).

## Project Setup

```sh
npm install
```

### Compile and Hot-Reload for Development

```sh
npm run dev
```

### Compile and Minify for Production

```sh
npm run build
```

### Lint with [ESLint](https://eslint.org/)

```sh
npm run lint
```

## Publicação no GitHub Pages

O workflow `.github/workflows/pages.yml` valida o build nos PRs e publica automaticamente após push/merge na main. Também pode ser executado manualmente em Actions. O build usa Node 24 e npm ci; não cria branch dist e não precisa de token pessoal.

1. No GitHub, abrir Settings → Pages e escolher **GitHub Actions** em Source.
2. Integrar o PR na main (ou executar Build and deploy GitHub Pages manualmente depois).
3. Acompanhar o endereço publicado no job deploy / ambiente github-pages.

O caminho base vem de actions/configure-pages, contemplando publicação no subdiretório /mdawiki/ ou domínio próprio configurado no Pages. A variável de repositório VITE_API_BASE_URL pode sobrescrever a API; por padrão usa https://api.mda.wiki.br. Alterações exigem novo build.

A API precisa permitir a origem publicada em CORS_ORIGINS: por exemplo, https://maycorolbuche.github.io (sem /mdawiki no origin). Manter também as outras origens necessárias, separadas por vírgula.

O workflow copia index.html para 404.html para que acessos diretos às rotas do Vue carreguem a aplicação. O GitHub Pages ainda responde HTTP 404 nesses acessos, embora a página Vue seja exibida. Para rotas SPA com resposta HTTP 200 e melhor indexação, usar hospedagem com fallback configurável, como Cloudflare Pages. Não foi configurado domínio próprio nem alterada a hospedagem nesta entrega.
