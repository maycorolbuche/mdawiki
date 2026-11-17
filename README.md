# MDAWiki frontend

Nova base **Vue 3 + TypeScript + Vite + Vue Router + Bootstrap 5**, substituindo integralmente a implementação anterior. O backend continua independente, em PHP puro no repositório `maycorolbuche/mdawiki-api`. Não há Inertia, Laravel ou backend Node para hospedar.

## Modelo de publicação

1. A build consulta a exportação pública da API e gera um `index.html` por artigo, com HTML e metatags.
2. No navegador, Vue monta sobre esse snapshot e sempre consulta a API, inclusive na abertura inicial e nas trocas de artigo.
3. Alterações aparecem na tela antes da próxima build; o código-fonte e as prévias sociais publicados continuam na versão gerada até o próximo deploy.
4. `404.html` carrega o mesmo aplicativo no caminho original. Consulta a API e mostra artigos novos ainda não gerados. O HTTP inicial permanece 404 até a próxima build.
5. Um 404 da API remove o snapshot; falhas de rede/503 preservam o snapshot com aviso e retry. A API é a fonte de verdade, não o payload de build.

A estrutura inicial inclui leitura e exportação; a migração completa do layout legado, parser, busca, login, edição, histórico e mídia será feita nos próximos PRs. O layout atual é uma base responsiva, não uma reprodução visual final do legado. Não há botões de edição ou login simulados.

## Desenvolvimento

```sh
npm ci
cp .env.example .env
npm run dev
npm run typecheck
npm test
npm run build
```

A API PHP precisa estar disponível. Para validar sem banco, use explicitamente `BUILD_FIXTURE=tests/fixtures/pages.json VITE_API_BASE_URL=http://127.0.0.1:8080 VITE_SITE_URL=https://mda.wiki.br npm run build`. Fixtures nunca são ativadas pelo deploy de produção. `dist/` não é versionada.

O build falha se a exportação da API estiver indisponível ou inválida, preservando a última publicação. Paginação usa `next_cursor`; há validação contra colisão de rotas e escrita fora de `dist`. Conteúdo HTML é sanitizado no SSR da build e no navegador; não executamos templates ou handlers fornecidos pelos artigos. Estilos inline e scripts antigos não são preservados nessa base: precisam de adaptação explícita durante a migração visual.

## GitHub Pages

Em Settings → Pages, selecione GitHub Actions. Configure as variáveis de repositório:

| Variável | Valor |
|---|---|
| `API_BASE_URL` | URL HTTPS da nova API PHP, sem barra final |
| `SITE_URL` | URL canônica pública, incluindo subdiretório se houver |
| `BASE_PATH` | `/` no domínio próprio, `/mdawiki/` no endereço de projeto |

Configure o domínio próprio no Pages quando aplicável. Nada é publicado pelo PR. Após merge na `main`, o workflow testa, gera com a API real e publica. Há execução manual para atualizar os snapshots sem alterar código. Não há agendamento nem disparo automático por edição nesta etapa.

O servidor da API precisa permitir a origem do frontend em `ALLOWED_ORIGINS`. Dados pessoais e conteúdo privado nunca devem participar da exportação pública. Todas as variáveis `VITE_*` e o artefato Pages são públicos; não inclua segredos. Autenticação e a adequação da hospedagem para telas sensíveis continuam pendentes da etapa correspondente.

## Verificação

`npm run test:browser` usa um servidor estático de teste que retorna `404.html` com HTTP 404 para caminhos inexistentes. Testa artigo estático desatualizado, artigo novo, inexistente, indisponibilidade, HTML malicioso e navegação. O servidor é exclusivo dos testes e não implementa backend de produção.

API: [maycorolbuche/mdawiki-api](https://github.com/maycorolbuche/mdawiki-api). Origem das regras a migrar: [maycorolbuche/mdawiki-site](https://github.com/maycorolbuche/mdawiki-site), preservado.
