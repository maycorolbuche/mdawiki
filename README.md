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


## Exportação de artigos em PDF

Nas páginas demonstrativas (`/especialidade-de-resposta-a-emergencias-e-desastres`, `/acampamento` e `/natureza`), o botão **Baixar PDF** gera e baixa o documento no navegador. Por enquanto, o corpo é o HTML de exemplo de `src/mocks/articlePdf.html`, identificado como demonstrativo no próprio PDF; o título e a URL de origem correspondem à página aberta.

A função reutilizável fica em `src/utils/articlePdf.js`:

```js
const { downloadArticlePdf } = await import('./utils/articlePdf')
await downloadArticlePdf({
  title: article.title,
  html: article.html,
  filename: 'especialidade.pdf',
  sourceUrl: window.location.href,
})
```

Para integrar a API, substitua `mockArticleHtml` pelo HTML do artigo no `PageView.vue`. O conteúdo não precisa incluir o título, que é inserido pelo modelo.

O modelo usa A4, margens próprias, fonte Roboto incluída no pacote, cabeçalho MDAWiki e rodapé com página atual/total. Edite `createArticlePdfDefinition` para personalizar esses elementos, as cores e os estilos de títulos, tabelas e links. As bibliotecas e fontes são carregadas sob demanda, ao solicitar o download.

Para iniciar uma seção em outra página, use uma classe no HTML:

```html
<h2 class="pdf-page-break">Nova seção</h2>
```

Títulos, parágrafos, listas, tabelas e links são convertidos em elementos do PDF, com texto selecionável. O CSS do Bootstrap, Flexbox e Grid não é transportado: o PDF usa os estilos definidos no conversor. Elementos marcados com `data-pdf-exclude` e controles de interface são excluídos. Não passe menus ou painéis laterais como conteúdo do artigo.

Execute `npm test` para conferir a geração de um PDF real de duas páginas, a numeração, a quebra explícita e a exclusão dos controles do HTML.
