# MDAWiki

Interface de wiki colaborativa para Desbravadores e Aventureiros, baseada no HTML de referência. Vue 3 com Composition API (`<script setup>`), Vite, Vue Router, Pinia, Bootstrap 5, BootstrapVueNext e Bootstrap Icons. Dependências e emblemas são servidos localmente, sem CDN.

## Executar

Use Node.js `^22.18.0 || >=24.12.0` (conforme `package.json`).

```sh
npm ci
npm run dev
```

```sh
npm run lint
npm test
npm run build
npm run preview
```

## Organização

- `src/assets/images`: marca demonstrativa e emblemas extraídos do HTML enviado.
- `src/assets/styles`: tema, responsividade e impressão.
- `src/components/layout`: header, sidebar responsiva, footer e sidebar do artigo.
- `src/components/article`: cabeçalho, metadados, seções, índice e modais.
- `src/components/specialties`: catálogo e cartões reutilizáveis.
- `src/components/search`: busca de especialidades.
- `src/components/common`: componentes compartilhados.
- `src/layouts/WikiLayout.vue`: estrutura persistente das páginas.
- `src/views`: páginas carregadas sob demanda pelas rotas.
- `src/router`: rotas nomeadas, título e rolagem.
- `src/stores`: estado global da interface (Pinia).
- `src/composables`: carregamento do catálogo, índice e cópia para a área de transferência.
- `src/services`: contrato assíncrono de acesso aos dados.
- `src/mocks`: dados fictícios separados da apresentação.

Componentes usam PascalCase; funções e estado usam camelCase; composables têm prefixo `use`; URLs usam kebab-case. Props fornecem dados aos componentes, eventos comunicam ações; estado compartilhado da interface fica no Pinia, estado da página permanece local.

## Rotas

| URL                                                  | Conteúdo                           |
| ---------------------------------------------------- | ---------------------------------- |
| `/`                                                  | Início e especialidade em destaque |
| `/especialidades`                                    | Catálogo                           |
| `/especialidades/resposta-a-emergencias-e-desastres` | Artigo do HTML de referência       |
| `/desbravadores` e `/aventureiros`                   | Páginas iniciais das áreas         |
| `/comunidade/mudancas-recentes`                      | Exemplo de histórico               |
| `/comunidade/colaboradores`                          | Exemplo de colaborador             |
| `/ajuda`                                             | Orientações de uso                 |
| Outros endereços                                     | Página 404                         |

O artigo possui abas Artigo/Discussão, índice por âncoras, edição demonstrativa com download de sugestão `.txt`, histórico fictício, impressão, cópia de link e referência. A busca ignora acentos e aceita `Ctrl/Cmd + K`. Menu móvel, modais e dropdowns utilizam BootstrapVueNext; não há manipulação de componentes pelo JavaScript do Bootstrap.

## Integração futura com API

`articleService.list(query)` retorna `Promise<Article[]>`; `articleService.findBySlug(slug)` retorna `Promise<Article | null>`. O formato inicial de `Article` está em `src/mocks/articles.js`: identificação, metadados, requisitos, recursos, referências, categorias, colaboradores e revisões. Troque a implementação desse serviço pelo cliente HTTP quando o contrato da API estiver definido; os componentes não importam os mocks. Atualmente as respostas são cópias independentes para evitar alteração acidental da fonte.

As telas tratam carregamento, erro com nova tentativa, busca vazia e artigo inexistente. Não há autenticação nem publicação: sugestões são apenas baixadas, discussões estão vazias e histórico/colaboradores são exemplos identificados. O conteúdo resumido não substitui os requisitos oficiais. A marca usa o símbolo do template com o nome MDAWiki e pode ser substituída pelo logo definitivo.

## Hospedagem

Publique o conteúdo de `dist/`. Como as rotas usam History API, configure o servidor para devolver `index.html` quando o caminho não corresponder a um arquivo existente (inclusive ao atualizar a página em `/especialidades/...`). Para hospedar em subdiretório, configure `base` no Vite; o roteador utiliza `import.meta.env.BASE_URL`. A cópia automática depende da permissão do navegador e de HTTPS (ou localhost); em caso de falha, o texto da referência continua selecionável no modal.
