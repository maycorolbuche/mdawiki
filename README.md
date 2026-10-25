# MDAWiki — frontend Vue

Frontend de uma wiki com backend independente em JavaScript, planejado para Cloudflare Workers. Vue 3, Composition API, Vue Router, Pinia, BootstrapVueNext e Bootstrap 5. O layout mantém a referência visual anterior; páginas e configuração editorial são resolvidas por um único serviço de API.

## Executar a prévia

Node.js `^22.18.0 || >=24.12.0`.

```sh
npm ci
```

Copie `.env.example` para `.env.local`. No PowerShell:

```powershell
Copy-Item .env.example .env.local
npm run dev
```

O exemplo usa `VITE_DATA_SOURCE=mock`. Em desenvolvimento, esse também é o padrão sem configuração. O mock simula endpoints JSON com `Response`, sem uma API real. O modo `api` usa HTTP de verdade e nunca faz fallback silencioso para os exemplos.

## Conectar ao Worker

```dotenv
VITE_DATA_SOURCE=api
VITE_API_BASE_URL=https://api.exemplo.com/v1
```

Reinicie o Vite após alterar as variáveis. Em produção, sem configuração explícita, o padrão é `api` com base `/api/v1`; para backend em outra origem, configure sua URL antes do build. Nunca coloque segredos em variáveis `VITE_`, pois ficam públicos no bundle. O Worker deve liberar a origem do frontend via CORS. O contrato está em [docs/api-contract.md](docs/api-contract.md).

Para gerar uma prévia estática com dados fictícios, configure `VITE_DATA_SOURCE=mock` no ambiente do build. Para publicar com API real, configure `api` e a origem correta. As variáveis Vite são definidas no build, não lidas de um `.env` após a publicação de `dist/`.

## Rotas públicas

- `/`: início recebido da API.
- `/:slug`: qualquer artigo, especialidade, categoria ou mídia. Exemplos: `/adra`, `/acampamento`, `/resposta-a-emergencias-e-desastres`, `/imagem-emblema-adra`.
- `/busca?q=...&pagina=2`: busca em todos os tipos, paginada.
- `/acampamento?acao=historico` e `?acao=discussao`: atividade na mesma página.

Não há prefixo obrigatório `/artigos/...` ou `/especialidades/...`. A API fornece o tipo, o slug canônico e os dados. Slugs/aliases não são recriados a partir de títulos no navegador. Os exemplos incluem aliases como `/Resposta_a_Emergências_e_Desastres` e `/ADRA`; a migração dos endereços reais ainda precisa ser construída no backend.

## Estrutura

| Pasta                         | Responsabilidade                                                         |
| ----------------------------- | ------------------------------------------------------------------------ |
| `src/layouts`                 | Shell da wiki, carregamento inicial e falhas de configuração             |
| `src/views`                   | Resolução de página, busca e 404                                         |
| `src/components/layout`       | Header, footer, navegação e sidebar de artigo                            |
| `src/components/article`      | Cabeçalho, metadados opcionais, seções, índice e sugestão local          |
| `src/components/wiki`         | Blocos genéricos, links, cartões, HTML sanitizado e atividade            |
| `src/components/common`       | Estados de carregamento/erro e modais compartilhados                     |
| `src/services/api`            | Cliente HTTP, erros, timeout, cancelamento e validação básica de DTOs    |
| `src/services/wikiService.js` | Contrato de leitura utilizado por todas as telas                         |
| `src/config`                  | Configuração pública por ambiente                                        |
| `src/stores`                  | Configuração do site e estado compartilhado de interface                 |
| `src/composables`             | Ciclo de vida de requisições, clipboard e índice                         |
| `src/utils`                   | Rotas, validações de URL e sanitização                                   |
| `src/mocks`                   | Fixtures e transporte de demonstração, sem importação direta pelas views |
| `public/demo`                 | Emblemas usados exclusivamente nas fixtures                              |
| `docs`                        | Análise do legado, arquitetura e contrato da API                         |

Os componentes usam PascalCase, funções/estado camelCase e composables `use...`. Conteúdo de negócio não fica nos componentes: menu, rodapé, home, seções, metadados, categorias, relações, mídia, resultados, revisões e fonte vêm do serviço. Apenas a apresentação e os rótulos de interação pertencem ao frontend.

## Validação

```sh
npm run lint
npm test
npm run build
npm run preview
```

A CI executa lint sem correção, formatação, testes e build. Os testes verificam contrato, resolução de tipos/aliases, paginação, normalização de busca no mock, isolamento das respostas, codificação, 404/503, rede, timeout, cancelamento e URLs. O modo real não herda regras de busca do mock: o Worker deve implementá-las.

## Hospedagem e escopo

Publique `dist/` com fallback para `index.html` em navegações de History API. Se usar Workers Static Assets no frontend, a opção de SPA é `assets.not_found_handling = "single-page-application"`; o backend pode continuar em outro Worker/origem. Isso atende refresh da SPA, mas não fornece SSR, redirects 301 ou status 404 editorial no HTTP inicial. Esses requisitos de SEO precisam ser resolvidos antes da migração pública.

A preparação atual contém leitura e dados fictícios. Não implementa Worker, migração do banco/acervo, autenticação, gravação, uploads, parser legado, restauração ou jobs. “Sugerir alteração” consulta a fonte e baixa um `.txt`, sem publicar. Categorias/relações são prévias; grandes conjuntos precisarão de endpoints paginados. HTML recebido é sanitizado com DOMPurify e não é compilado como Vue.

Veja a [análise minuciosa do legado e plano de migração](docs/analise-legado.md), incluindo evidências, riscos concretos e o que precisa permanecer no backend.

## Repositórios e coluna de informações

- Frontend: https://github.com/maycorolbuche/mdawiki
- API JavaScript/Cloudflare Workers: https://github.com/maycorolbuche/mdawiki-api
- Legado de referência: https://github.com/maycorolbuche/mdawiki-site

No desktop, as informações do legado ficam à direita: dados da imagem (quando aplicável), categorias, páginas não existentes, menções a esta página e páginas relacionadas. Os blocos de relações vazios não aparecem. Contadores mostram a quantidade de itens recebidos, não um total global de registros. Índice, colaboração e ferramentas permanecem na coluna, após as relações.

Em telas menores, a coluna passa para depois do artigo; as informações continuam acessíveis. Links internos presentes em `missingLinks` recebem destaque também nos blocos de conteúdo. Os dados vêm da API; o frontend não calcula relações por conta própria.

O endereço do repositório não é o endereço HTTP da API: `VITE_API_BASE_URL` deve receber a URL do Worker quando ele estiver disponível. Enquanto isso, use o modo `mock`. O repositório da API estava vazio durante esta alteração; este PR não inicializa a branch principal do backend nem implanta um Worker.
