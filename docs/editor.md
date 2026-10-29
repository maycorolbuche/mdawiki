# Páginas e edição integradas à API

`PageView.vue` busca `GET /pages/:slug` a partir do caminho atual. Apenas slugs de um segmento com pelo menos dois caracteres são artigos. Segmentos de um caractere e caminhos internos como /p/ajuda, /u/profile e /i/qualquer ficam reservados ao sistema. As rotas próprias existentes de início e sobre continuam prioritárias.

Página encontrada mostra título, conteúdo, categorias e metadados reais, mantendo abas e painéis. A tabela de especialidade é formada pelos metadados; imagens fictícias não são aplicadas aos artigos reais. Discussão e consulta ao histórico continuam explicitamente pendentes de integração.

404 mostra como título a escrita derivada da URL (a mesma usada no formulário), seguida de “Ainda não existe um artigo neste endereço.” e do botão Criar página. Falha de rede, timeout, erro interno ou falha ao obter diretrizes mostra erro/tentar novamente, sem oferecer criação por engano. A navegação cancela consultas anteriores. Redirecionamentos da API levam à URL canônica da página.

## Ambiente

- `.env.development`: `VITE_API_BASE_URL=http://127.0.0.1:8787`.
- `.env.production`: `VITE_API_BASE_URL=https://api.mda.wiki.br`.
- Para sobrescrever localmente, use `.env.development.local` ou `.env.production.local`. Uma variável definida no ambiente do processo/build também prevalece.

Execute `npm install` após atualizar as dependências e reinicie o Vite após alterar env. Variáveis VITE são públicas e incorporadas no build: mudar a configuração publicada requer novo build. Nunca incluir segredos nelas. A API deve permitir a origem real do site via CORS. Os padrões locais contemplam a porta 5006; para outro domínio/porta, configure CORS_ORIGINS no Worker.

## Diretrizes e formulário

`GET /page-schema` fornece versão, limites, tags/atributos/protocolos HTML e os tipos com campos de metadados. É carregado em paralelo à página e armazenado somente na memória da sessão. Abrir ou salvar o editor revalida por ETag. Se as diretrizes mudarem com o formulário aberto, o salvamento é interrompido e pede para reabrir/revisar. A API sempre valida novamente.

Ao criar uma página, o título inicial mantém a escrita do caminho, substituindo hífens por espaços: `/especialidade-de-caes` sugere `especialidade de caes`; `/Especialidade de Cães` sugere `Especialidade de Cães` e ajusta o endereço para `/Especialidade-de-Cães`. Não há capitalização automática. A busca usa o slug normalizado (`especialidade-de-caes`); quando a página existe ou é salva, a URL passa a refletir seu título, preservando maiúsculas e acentos. Espaços e pontuação que separa palavras viram hífens; caracteres especiais são codificados para transporte. Query e âncora são preservadas nos ajustes do endereço.

O formulário mostra título, endereço gerado, tipo, campos específicos, categorias e conteúdo em Tiptap. Metadados são renderizados a partir das definições da API; números, datas, seleções, booleanos e listas de texto são suportados. Código de especialidade é texto para manter zeros. Ao alternar tipos, os rascunhos dos campos são mantidos enquanto o modal estiver aberto; somente os campos do tipo selecionado são enviados. Metadados existentes não descritos pela API bloqueiam edição para evitar perda silenciosa.

O corpo não aceita H1: o título principal pertence à página. As extensões do Tiptap seguem a política da API e preservam tabelas, imagens com data-media-id, figuras/legendas e links. A barra disponibiliza as formatações implementadas e autorizadas. Não há upload de imagem nesta etapa; imagens já presentes são preservadas. Se a API introduzir tags ainda não suportadas, o editor informa a necessidade de atualização em vez de remover conteúdo desconhecido.

DOMPurify aplica tags, atributos por elemento e protocolos da política antes de exibir ou enviar HTML. A API usa suas próprias configurações para sanitizar e validar, independentemente do navegador.

## Gravação

- Criar: POST /pages. Endereço ocupado retorna 409, sem sobrescrever.
- Editar: PUT /pages/:slug. Renomear mantém o ID e cria redirecionamento do endereço anterior.
- A resposta da API é a fonte dos dados exibidos após salvar.
- Categorias e metadata substituem suas coleções completas. O formulário envia ambas completas; campos gerais não editados, como summary/status, são preservados pelo servidor. Novas páginas usam os padrões da API (status draft).
- Erros de validação/conflito aparecem no modal e preservam o rascunho. Durante a gravação, controles e fechamento do modal ficam bloqueados.

## Backup e testes

`src/views/PageViewMock.vue` é uma cópia do PageView anterior, mantida fora das rotas e do bundle ativo. É uma referência de código do estado mockado; os serviços e componentes compartilhados evoluíram para a API, portanto não deve ser registrado como uma rota sem restaurar suas dependências originais. Pode ser removido quando a integração estiver estável.

`npm test` verifica política de HTML, preservação do editor, limites de título/slug, namespaces reservados, serialização de metadata, ETag e tratamento de respostas do cliente HTTP. `tests/fixtures/pageSchema.json` é uma amostra do contrato da API usada somente pelos testes, sem regras fixas de produção no frontend.
