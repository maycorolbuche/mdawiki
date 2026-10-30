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

## Publicação no Cloudflare Pages

Ambiente de testes: **https://sites.mda.wiki.br**. O domínio **mda.wiki.br** permanece no legado até a migração final. O build/deploy é feito pela integração Git nativa do Pages, sem branch dist nem workflow de publicação no GitHub.

### Criar o projeto

No painel Cloudflare, acessar Workers & Pages, criar uma aplicação **Pages** e escolher importar/conectar um repositório Git existente. Conectar o GitHub e selecionar maycorolbuche/mdawiki. Se a interface abrir a criação de um Worker, voltar e selecionar Pages.

| Configuração | Valor |
| --- | --- |
| Nome do projeto | mdawiki (ou outro disponível) |
| Branch de produção do projeto de teste | main |
| Framework preset | Vue / Vite, conforme as opções exibidas |
| Build command | npm run build |
| Build output directory | dist |
| Root directory | raiz do repositório |
| NODE_VERSION | 24 |
| VITE_API_BASE_URL | https://api.mda.wiki.br |

A branch de produção do projeto Pages alimenta somente o site de testes, não altera o site legado. Definir as variáveis para Production; replicar em Preview se for usar builds de branches. .node-version também fixa Node 24. A URL da API é pública e incorporada no build.

Após salvar/deploy, testar primeiro o endereço atribuído pelo Pages. Depois acessar o projeto → Custom domains → Set up a custom domain e adicionar **sites.mda.wiki.br**. Confirmar a criação do DNS sugerido quando a zona estiver na mesma conta; se precisar configurar manualmente, seguir o CNAME informado pelo Pages para o endereço atribuído ao projeto. Registrar o domínio no projeto antes de criar somente o CNAME. Aguardar domínio/certificado ativos.

Não alterar registros DNS de mda.wiki.br nem de api.mda.wiki.br.

### API / CORS

A API precisa autorizar https://sites.mda.wiki.br. O PR correspondente adiciona a origem à configuração padrão; publicar a API atualizada antes de usar o site. Se CORS_ORIGINS estiver configurada no Worker, ela substitui os padrões: incluir https://sites.mda.wiki.br nessa variável, mantendo as outras origens necessárias. Sem barra final e sem caminho.

O endereço temporário pages.dev também precisa ser autorizado explicitamente se for utilizado para testar chamadas à API. Não há liberação genérica de domínios de preview.

### Rotas Vue e atualizações

Usar base / (padrão do Vite), sem /mdawiki/. O Pages fornece fallback SPA para index.html quando não existe um 404.html na raiz. Este projeto não gera esse 404.html; acessar ou atualizar diretamente /Especialidade-de-Cães deve carregar o Vue.

Depois da integração, pushes/merges na main geram novos deploys automaticamente pelo Cloudflare. Acompanhar o build no painel Deployments. Não é necessário rodar Wrangler para publicar o frontend nem token Cloudflare no GitHub.

Se o GitHub Pages já tiver sido ativado antes, despublicar/desativar em Settings → Pages para encerrar a hospedagem anterior. Remover o workflow não apaga um site GitHub Pages já publicado.
