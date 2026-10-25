# Contrato de leitura proposto para o Worker

Este documento define o contrato que o frontend já consome, simulado até a implementação da API. Não descreve um backend implantado. Base configurável por `VITE_API_BASE_URL`, por exemplo `https://api.exemplo.com/v1`. O caminho público do Vue continua `/:slug`; caminhos técnicos da API não são rotas públicas da wiki.

## Endpoints

| Método e caminho relativo à base                   | Resposta                                                                                           |
| -------------------------------------------------- | -------------------------------------------------------------------------------------------------- |
| `GET /site`                                        | Configuração editorial da interface: nome, marca, menu, rodapé e orientação de colaboração.        |
| `GET /pages/~home`                                 | Página inicial, tipo `home`. `~home` é reservado.                                                  |
| `GET /pages/{slug}`                                | Página resolvida por slug/alias; tipo `article`, `specialty`, `category`, `media` ou `home`.       |
| `GET /search?q=...&page=1&perPage=10&type=article` | Resultados com ID, slug, título, descrição e tipo. Sem filtro `type`, todos os tipos pesquisáveis. |
| `GET /pages/{id}/revisions?page=1&perPage=10`      | Lista de revisões.                                                                                 |
| `GET /pages/{id}/discussions?page=1&perPage=10`    | Lista de tópicos.                                                                                  |
| `GET /pages/{id}/source`                           | `{ format: 'text', text: '...' }` para preparar sugestão local.                                    |

Os segmentos são codificados com `encodeURIComponent`, preservando caracteres e aliases antigos. A resolução usa slug; operações vinculadas usam ID estável. A home tem ID próprio na resposta. O backend deve distinguir as rotas de operação antes da resolução de slug.

## Envelopes e erros

```json
{
  "data": [],
  "meta": { "page": 1, "perPage": 10, "total": 0, "hasMore": false }
}
```

Respostas únicas usam `{ "data": { ... } }`. Erros usam HTTP 4xx/5xx e `{ "error": { "code": "NOT_FOUND", "message": "Página não encontrada." } }`. Não devolver `200` com página inexistente. Exemplos: 400 de validação, 401 não autenticado, 403 não autorizado, 404 ausente, 409 conflito de revisão em futura escrita, 429 limite, 5xx indisponibilidade.

`Content-Type: application/json; charset=utf-8`. Datas em ISO 8601 com timezone. Paginação baseada em 1, `perPage` limitado no servidor; preservar ordem estável. Não devolver SQL, stack trace, tokens ou credenciais. O cliente preserva status/código, cancela requisições obsoletas, aplica timeout e não substitui falhas HTTP por dados fictícios.

## Page

```json
{
  "data": {
    "id": "page-123",
    "slug": "acampamento",
    "title": "Acampamento",
    "description": "Planejamento e convivência ao ar livre.",
    "type": "article",
    "redirectFrom": null,
    "notice": "",
    "capabilities": { "suggest": true, "history": true, "discussion": true },
    "metadata": null,
    "media": null,
    "contributors": [{ "name": "Colaborador", "initials": "CL" }],
    "sections": [
      {
        "id": "planejamento",
        "title": "Planejamento",
        "blocks": [
          { "type": "paragraph", "text": "Conteúdo do artigo." },
          { "type": "links", "items": [{ "slug": "adra", "title": "ADRA" }] }
        ]
      }
    ],
    "toc": [{ "id": "planejamento", "label": "Planejamento" }],
    "categories": [{ "slug": "desbravadores", "title": "Desbravadores" }],
    "members": [],
    "relatedPages": [],
    "backlinks": [],
    "missingLinks": []
  }
}
```

`slug` é vazio apenas para home; os demais são um segmento (sem `/`, `\\`, `?`, `#`, controles, `.` ou `..`). Novos slugs podem usar kebab-case; aliases de importação conservam caixa, acentos e underscores. `busca` e `~home` são reservados. `id`/`title` e IDs válidos de seção são verificados no cliente; coleções editoriais opcionais ausentes são normalizadas para arrays vazios. A API deve retornar `sections` completas para a versão renderizada; IDs únicos e estáveis de seção servem ao índice.

Links internos usam `{ slug, title, description? }`; externos usam `{ url, title }`. A UI não constrói slug pelo título e não injeta HTML para mostrar títulos/resumos. `members`, `relatedPages`, `backlinks` e `missingLinks` são prévias pequenas; a API futura precisará de paginação dedicada para conjuntos grandes.

`metadata` opcional contém `fields: [{ label, value }]` e `images: [{ url, alt }]`. Especialidades usam esses campos; artigos comuns não precisam deles. `media` opcional contém `url`, `alt`, `width`, `height`, `format`, `attribution`. URLs de mídia devem ser HTTP(S) ou caminhos absolutos locais seguros, nunca base64 editorial no DTO. Arquivos de conteúdo pertencem ao armazenamento da API, não ao bundle Vue.

## Blocos suportados nesta fase

| `type`         | Campos                                                                                          |
| -------------- | ----------------------------------------------------------------------------------------------- |
| `paragraph`    | `text`                                                                                          |
| `list`         | `items: string[]`                                                                               |
| `links`        | `items: Link[]`                                                                                 |
| `page-cards`   | `items: PageSummary[]`                                                                          |
| `requirements` | `items: [{ text, items?: string[], links?: Link[] }]`                                           |
| `resources`    | `items: [{ title, description, url ou slug, icon? }]`                                           |
| `references`   | `items: Link[]`                                                                                 |
| `image`        | `url`, `alt`, `caption`                                                                         |
| `html`         | `html` já renderizado e sanitizado no servidor; sanitizado novamente pelo DOMPurify no cliente. |

O bloco HTML admite texto editorial, cabeçalhos, listas, tabelas, imagens e links; remove scripts, handlers inline, estilos, SVG, iframes, formulários e URLs perigosas. Conteúdo legado precisa ser transformado pelo backend. Não é suporte a toda sintaxe histórica nem a embeds arbitrários. Blocos desconhecidos mostram uma mensagem de incompatibilidade.

A API pode preferir blocos estruturados para conteúdo novo e HTML editorial seguro para a migração. O parser de marcação, os includes, a extração de links e os templates nunca rodam no Vue.

## Site

A fixture `src/mocks/database.js` contém um exemplo completo do envelope de `/site`. Campos consumidos: `name`, `tagline`, `logoUrl`, `viewerLabel`, `helpSlug`, `motto`, `mottoDescription`, `navigation: [{ id, label, items: [{ slug, label, icon }] }]`, `footer: { text, notice, links: [{ slug, label }] }`, `collaboration: { title, description, steps: string[], notice }`.

`icon` referencia uma classe de Bootstrap Icons; é apresentação, sem HTML executável. Não devolver templates Vue ou nomes de componentes para executar. A shell e os rótulos de ações da aplicação são locais; configuração, conteúdo e dados de negócio vêm da API.

## Atividade e fonte

Revisões: `{ id, date, author, summary }`. Discussões: `{ id, date, author, text }`. Ambas usam envelope paginado. O componente carrega a atividade apenas quando aberta, com cancelamento, nova tentativa e navegação de páginas.

`source` retorna o texto integral da página; “Editar seção” identifica a seção na sugestão, mas ainda carrega a fonte integral. A edição desta etapa apenas baixa `.txt`. Não há POST/PUT nem simulação de publicação. `capabilities` controla apresentação; não substitui a autorização no Worker. Se a fonte não puder ser pública, a API deve retornar 401/403 e o cliente exibirá o erro.

## Origens, autenticação e cache

A leitura atual usa `credentials: 'omit'`, sem tokens locais. Para origem separada, configurar CORS no Worker para a origem exata do frontend; responder OPTIONS quando aplicável. Não usar credenciais de Cloudflare/D1/R2 no navegador nem variáveis `VITE_` para segredos. A escolha de autenticação futura precisa definir cookies/tokens, CSRF quando aplicável, expiração e revogação antes de habilitar escrita.

Proposta para o backend: leitura pública cacheável com ETag/revisão; informações privadas e fonte restrita sem cache público; invalidar conteúdo dependente após publicação. Esta fase não implementa cache HTTP próprio nem um banco. Sitemap e redirects HTTP permanecem responsabilidades de publicação/edge.

## Repositório oficial e relações do legado

A implementação do backend ficará em [maycorolbuche/mdawiki-api](https://github.com/maycorolbuche/mdawiki-api), separada do frontend. Esse endereço Git não deve ser usado como `VITE_API_BASE_URL`. O contrato deste documento é a referência de integração; a URL HTTP do Worker ainda será configurada após a implementação.

| Campo do Page                                 | Bloco na coluna direita | Origem conceitual no legado |
| --------------------------------------------- | ----------------------- | --------------------------- |
| `media.width`, `media.height`, `media.format` | Dados da imagem         | `data.imagem`               |
| `categories`                                  | Categorias              | `data.categorias`           |
| `missingLinks`                                | Páginas não existentes  | `data.nao_existentes`       |
| `backlinks`                                   | Menções a esta página   | `data.mencoes`              |
| `relatedPages`                                | Páginas relacionadas    | `data.relacionadas`         |

Esses blocos seguem a ordem do legado. Arrays vazios ocultam o bloco; contadores refletem os itens recebidos. O mesmo slug que aparece em `missingLinks` é marcado como inexistente nos links estruturados do corpo. Para HTML renderizado, o backend deve fornecer a marcação editorial adequada ou converter os links em blocos estruturados; o Vue não reinterpreta o grafo a partir de HTML.
