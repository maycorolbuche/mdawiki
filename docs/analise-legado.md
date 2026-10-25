# Análise do MDAWiki legado e plano de separação

Análise estática em 25/10/2026 do repositório `maycorolbuche/mdawiki-site`, referência `f93b2524c94daf3c078d53d53d75f4a5354d3f2f`. O inventário recursivo contém 9.617 entradas, sendo 9.108 arquivos; 2.149 arquivos ficam em `site/@imgs_user`. Foram obtidos os 40 arquivos PHP da aplicação/raiz e três arquivos adicionais de roteamento e JavaScript próprio; a análise se concentrou nos fluxos e referências descritos abaixo. Bibliotecas de terceiros foram inventariadas, sem auditoria individual. O código não foi executado contra o banco de produção. Números de artigos, usuários, tamanho do banco e qualidade dos dados não podem ser deduzidos dessa árvore.

Fonte principal: [mdawiki-site nessa revisão](https://github.com/maycorolbuche/mdawiki-site/tree/f93b2524c94daf3c078d53d53d75f4a5354d3f2f). O repositório legado permanece sem alterações.

## Diagnóstico

O sistema é uma wiki própria em PHP/MySQL com parser de marcação, páginas de sistema e recursos editoriais; não é apenas um catálogo de especialidades. Frontend, consultas SQL, autenticação, geração de HTML e tarefas periódicas estão acoplados à mesma requisição. A reconstrução precisa preservar o grafo entre páginas e o conteúdo-fonte, além da aparência.

## Inventário funcional com evidência

| Área                  | Implementação observada                                                                                                | Consequência para a reconstrução                                                                                                                                                            |
| --------------------- | ---------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Entrada e URLs        | `.htaccess`, `index.php`, `site/index.php`, `config.php`, `data.php`, `page.php`                                       | Rewrite para `index.php?page=...`; primeiro segmento identifica a página e o segundo seleciona ações. Separar URL pública de endpoint HTTP.                                                 |
| Identificação         | `funcoes.php:136–174` e `data.php`                                                                                     | `IDENT` normaliza acentos/caixa e underscores; `NOME_URL` conserva outra representação. Não gerar slugs novamente no Vue.                                                                   |
| Leitura               | `data.php` e `page_view.php`                                                                                           | Carrega nome, código-fonte, HTML, texto para busca, nível, patrocínio, relações e imagem. Novo DTO precisa representar esses conceitos.                                                     |
| Tipos e namespaces    | `data.php`, `page.php`, `funcoes.php`                                                                                  | Artigo comum; prefixos `imagem@`, `page@`, `doc@` e listas `@...`. Categorias são reconhecidas pelas relações, não necessariamente por um tipo explícito no registro.                       |
| Redirecionamentos     | `data.php` e `page_templates()`                                                                                        | `PAGINAS_REDIRECIONAMENTOS` determina destino. Preservar aliases e impedir ciclos.                                                                                                          |
| Edição                | `page_edit.php`                                                                                                        | Editor de código, registro em `PAGINAS_LOGS`, atualização de `PAGINAS`, marcação para reprocessar e notificação por email. Nova escrita exige autorização e controle de revisão no backend. |
| Histórico             | `page_historico.php`                                                                                                   | Autor, data, código da versão, diff de duas versões e restauração para nível elevado. Histórico não é só um campo no artigo.                                                                |
| Busca rápida          | `page_busca.php`                                                                                                       | Sugestões por nome com REGEXP e filtro `BUSCA='S'`.                                                                                                                                         |
| Busca completa        | `busca.php`, `ajxBusca.php`                                                                                            | Fulltext MySQL no nome/texto, ordenação por relevância, resumo com destaque e paginação incremental. Migrar relevância e indexação conscientemente.                                         |
| Categorias            | `data.php`, `page_templates()`                                                                                         | Categoria por página, páginas de uma categoria, relacionadas e páginas sem categoria.                                                                                                       |
| Grafo                 | `page_links()`, `data.php`                                                                                             | Links internos/externos, páginas que mencionam a atual e links inexistentes. Não descartar ao importar.                                                                                     |
| Inclusões             | `page_include()` e `page_atualiza.php`                                                                                 | `{{Incluir:...}}`, inclusive seleção de seção; atualizações propagam a dependentes. Precisa de fila e limites de recursão no novo backend.                                                  |
| Especialidades        | `page_templates()`                                                                                                     | Templates de Desbravadores/Aventureiros, área, código, nível, ano/origem, emblemas e especialidades obsoletas. Renderizador genérico com metadados opcionais.                               |
| Outros templates      | `page_templates()`, `page_ex_templates()`, `page_tabelas()`, `page_lista_marcadores()`                                 | Categorias, listas dinâmicas, tabelas, biografias, desambiguação, links extras, referências, vídeos e imagens. Preservar a sintaxe até decidir conversão.                                   |
| Índice                | `page_subtitulos()`                                                                                                    | Subtítulos geram âncoras e índice hierárquico. API deve fornecer IDs estáveis e nível de cada título.                                                                                       |
| Mídia                 | `geraImagem()`, `img.php`, `perfil.php`, `@imgs`, `@imgs_user`                                                         | Arquivos/base64, dimensões, versões, miniaturas e imagens vinculadas a páginas. Mídia exige migração própria; não copiar base64 para todos os DTOs.                                         |
| Início                | `inicio.php`, `PAGINAS_TEMP`, `cronn.php`                                                                              | Destaques, aleatoriedade, rankings por período, alterações e links inexistentes. A home também deve ser alimentada pela API.                                                                |
| Menu                  | `page_inc_menu.php`                                                                                                    | Classes, especialidades dos dois ministérios e temas como acampamento, civismo, nós, uniformes e pessoas. O novo menu não deve ser limitado a especialidades.                               |
| Contas                | `login.php`, `cadastrese.php`, `ativa.php`, `recuperarsenha.php`, `alterarsenha.php`, `alteraremail.php`, `perfil.php` | Cadastro/ativação, login persistente, recuperação, perfil e troca de credenciais. Não implementados nesta etapa de frontend.                                                                |
| Usuários/estatísticas | `usuarios.php`, `estatisticas.php`                                                                                     | Há trechos antigos/de diagnóstico e gates de nível. Não assumir que sejam módulos administrativos completos.                                                                                |
| Comentários           | `page_inc_comentarios.php`                                                                                             | Integração Facebook está comentada. Não foi encontrada uma discussão nativa equivalente à nova aba.                                                                                         |
| Integração Plus       | `data.php`, extração de `mda-code`                                                                                     | Consulta metadados de apoio em `plus.mda.wiki.br/meta/...`. Futuro Worker deve tratar timeout/cache/falha sem bloquear artigo.                                                              |
| Publicidade e doações | `page_ads.php`, `doar.php`, `page_view.php`, `ads.txt`                                                                 | Preservar identificação de patrocinado e decidir separadamente anúncios e doação.                                                                                                           |
| Email                 | `gravaEmail()`, `enviarEmail()`, PHPMailer e tabelas EMAIL_*                                                           | SMTP/PHPMailer não é frontend. Substituir por integração adequada ao Worker e fila de envio.                                                                                                |

## Conteúdo e modelo de dados

As consultas revelam, entre outras, as famílias abaixo. Não há um esquema de criação/migrations do banco da aplicação suficiente para reconstruir constraints e índices apenas pelo repositório; as referências SQL não substituem um dump de estrutura.

| Família legada                                                     | Destino conceitual proposto                                                                    |
| ------------------------------------------------------------------ | ---------------------------------------------------------------------------------------------- |
| `PAGINAS`                                                          | Páginas com ID estável, slug canônico, tipo, título, fonte, revisão atual, status e metadados. |
| `PAGINAS_LOGS`                                                     | Revisões imutáveis com autor, data e origem; restaurar cria uma nova revisão.                  |
| `PAGINAS_CATEGORIAS`                                               | Associações de páginas e categorias, com ordem.                                                |
| `PAGINAS_LINKS`, `PAGINAS_LINKS_EXTERNOS`                          | Grafo interno, backlinks e URLs externas.                                                      |
| `PAGINAS_INCLUDES`, `PAGINAS_TEMPLATES`                            | Dependências de renderização e invalidação.                                                    |
| `PAGINAS_REDIRECIONAMENTOS`                                        | Aliases antigos e redirects editoriais.                                                        |
| `PAGINAS_IMAGENS`, `PAGINAS_VIDEOS`, dados de imagens em `PAGINAS` | Registro de mídia, arquivos em armazenamento de objetos e referências.                         |
| `PAGINAS_SUBTITULOS`, `TAGS`                                       | Índice e regras do parser; saída versionada do renderizador.                                   |
| `PAGINAS_ATUALIZAR`, `PAGINAS_TEMP`                                | Trabalho pendente e projeções de home/listas.                                                  |
| `USUARIOS`, `ACESSOS_LOGS`                                         | Identidades, credenciais modernas, sessões e auditoria.                                        |
| `PAGINAS_ACESSOS_LOGS`, `PAGINAS_ACESSOS_LOGS_QTD_DIARIO`          | Métricas agregadas, com política de retenção.                                                  |
| `EMAIL_CONFIG`, `EMAIL_MSG`, `EMAIL_ENVIO`, `EMAIL_LOGS`           | Configuração secreta, templates e fila de envio.                                               |
| `CRONJOB_LOGS` e projeções de estatísticas                         | Execuções de tarefas e resultados agregados.                                                   |

Guardar separadamente a marcação original (`source`), a saída renderizada e o texto pesquisável. Uma alteração na sintaxe do parser não pode destruir a possibilidade de editar ou comparar versões. A migração precisa manter ID legado, URL original e mapeamento de destino.

## URLs e compatibilidade

O novo frontend resolve uma página por `/:slug`, por exemplo `/adra`, `/acampamento`, `/resposta-a-emergencias-e-desastres` e `/imagem-emblema-adra`. Artigos, categorias e mídia compartilham o espaço de slugs: o backend deve garantir unicidade. O Vue não adiciona prefixos de tipo e não decide o slug a partir do título.

`/` é a home; `/busca?q=...&pagina=2` é a rota de busca reservada. As ações permanecem na mesma URL: `/acampamento?acao=historico` ou `?acao=discussao`. Download de sugestão continua em modal, sem publicação. Uma rota com vários segmentos é 404 nesta implementação.

A API retorna slug canônico e `redirectFrom`; o Vue faz `router.replace` para o endereço canônico. Isso é navegação no cliente, **não um redirect HTTP 301**. Antes da troca pública do site, o backend/edge deve mapear URLs reais antigas, inclusive maiúsculas, acentos, underscores, `imagem@...`, `page@...`, `doc@...`, listas e sufixos `/editar` e `/historico`. Gerar aliases a partir da base real, detectar colisões e ciclos e testar todos os endereços indexados. Os aliases de demonstração não constituem uma migração do acervo.

Reservar `/busca` e o token de API `~home`; colisões com conteúdo legado precisam de resolução explícita. O ID da entidade deve permanecer distinto do slug para que uma renomeação não perca revisões e relações.

## Tarefas que saem do navegador

`config.php` inclui `cronn.php` após atender a página. O cron mantém lock/registro em arquivos locais e executa: sitemap, página aleatória, links inexistentes, rankings de hoje/7/30 dias, últimas alterações, recriação de views, limpezas e atualização de páginas.

`page_atualiza.php` reinterpreta marcação, atualiza HTML/texto, extrai relações e enfileira dependentes. `data.php` também grava acessos e consulta o Plus durante a leitura. O frontend novo apenas consulta a API; não deve carregar o cron nem ter endpoints públicos de reprocessamento sem autorização.

Proposta para o backend JavaScript: handlers HTTP de leitura/escrita separados; tarefas periódicas por Cron Triggers; reprocessamento e notificações em consumidores de fila idempotentes; estado persistido em banco/objetos, sem depender de arquivos locais como lock. O desenho concreto depende do volume e dos limites da conta.

## Achados que precisam ser resolvidos na migração

1. **Credenciais no código:** há credenciais de banco em `site/config.php`. Remover do código novo, rotacionar as existentes e avaliar o histórico do repositório. Nenhum valor foi copiado para esta documentação ou para o frontend.
2. **Senhas antigas:** cadastro/login/troca usam MD5; recuperação utiliza senha provisória armazenada e enviada. Exigir fluxo moderno de reset com token descartável/expirável e hashing próprio para senhas. Definir estratégia de transição dos usuários.
3. **Autorização de escrita:** em `page_edit.php`, o bloco POST verifica login e grava antes do gate de nível aplicado à exibição do editor. A API nova deve autorizar toda escrita no servidor, inclusive restauração e upload, sem confiar no botão ou em `capabilities` do Vue.
4. **SQL concatenado:** `sql.php`, busca e demais handlers montam SQL com strings e tratamento próprio de entrada. Substituir por consultas parametrizadas e validar limites/filtros; não transportar `antiinjection()` como garantia.
5. **HTML e parser acoplados:** saída HTML é montada com dados editoriais; existem páginas com HTML direto. No Worker, sanitizar a saída e restringir templates; no Vue, aplicar sanitização adicional. Não executar PHP, scripts, diretivas Vue ou SQL enviados como conteúdo.
6. **Cache e tempo:** `data.php` calcula TTL subtraindo números no formato `YmdHis`, o que falha em mudanças de minuto/hora/dia. Adotar timestamps reais, revisão/ETag e invalidação por dependência. Não manter cache editorial compartilhado em sessão PHP.
7. **Concorrência:** próxima versão usa `MAX(...)+1`; contadores leem/incrementam/gravam. Substituir por operações atômicas/transações e controle otimista por revisão (409 ao editar versão antiga).
8. **Encoding:** há conversões repetidas ISO-8859-1/UTF-8. Importar com detecção e amostras verificadas; novas APIs e conteúdo usam UTF-8. Não repetir conversões no navegador.
9. **Custos de consulta:** `ORDER BY RAND()`, subqueries, reprocessamento e buscas amplas precisam de índices/projeções/paginação. Não converter consultas mecanicamente para D1.
10. **Debug e dados pessoais:** debug está habilitado na configuração; logs de acesso registram IP/navegador. Tratar erros sem revelar SQL e definir retenção/minimização de logs no novo backend.

Esses são achados de leitura de código, sem exploração ativa ou avaliação do ambiente de produção.

## Arquitetura proposta

- **`mdawiki`:** Vue com componentes de apresentação, páginas por slug, cliente HTTP, estado de interface e cache apenas onde explícito. Sem banco, parser de marcação, credenciais privadas ou jobs.
- **Backend separado em JavaScript:** Worker com contrato JSON versionado, resolução de página/alias, autenticação/autorização, parser, revisões, relações, busca, métricas e integrações.
- **Banco:** D1 é uma opção para o modelo relacional, mas a escolha depende de volume e migração. MySQL com Hyperdrive pode ser uma etapa intermediária. FULLTEXT, REGEXP, views e procedimentos de manutenção exigem revisão.
- **Mídia:** armazenamento de objetos como R2; DTO expõe URLs, atribuição e metadados. Preservar originais e versões e verificar checksums na importação.
- **Entrega pública:** host estático do Vue com fallback de History API; API em origem separada configurável. Para conteúdo indexável, planejar prerender/SSR ou resposta do edge que entregue título, conteúdo, status 404 e redirects reais. Atualizar `<title>`/canonical no cliente não substitui isso.

Referências oficiais consultadas: [Workers SPA](https://developers.cloudflare.com/workers/static-assets/routing/single-page-application/), [opções de armazenamento](https://developers.cloudflare.com/workers/platform/storage-options/). São opções arquiteturais; nenhum recurso Cloudflare foi provisionado neste PR.

## Etapas recomendadas

1. Consolidar contrato de leitura, slugs reservados e DTOs — base entregue neste PR.
2. Obter dump de estrutura e amostra anonimizada; inventariar URLs, fontes, arquivos, revisões e relações com contagens/checksums.
3. Implementar Worker de leitura com banco escolhido, busca, aliases e mídia; verificar paridade contra fixtures reais.
4. Portar parser por casos de teste: tabelas, listas, includes, especialidades, imagens, vídeos, referências, desambiguação e tags. Limitar recursão e documentar sintaxe incompatível.
5. Migrar contas com segurança e implantar edição, prévia, conflitos, revisões, diff e restauração autorizada.
6. Migrar tarefas, integrações, email e projeções; validar limites e observabilidade.
7. Preparar SEO, sitemap, redirects HTTP, backup e rollback. Publicar apenas após ensaio de migração e revisão dos endereços existentes.

## O que este PR entrega e seus limites

Entrega a preparação do frontend, não a reconstrução integral do legado: serviço único com modo HTTP/simulado, site e conteúdo recebidos pelo contrato, rota genérica na raiz, busca paginada, artigos/especialidades/categorias/mídia, índice dinâmico, relações, leitura de revisões/discussões e sugestão por download. HTML editorial passa por DOMPurify, sem compilar templates.

Ainda pendentes: Worker real, importador, parser legado, dados reais, contas, gravação de edições, diff/restauração, uploads, anúncios, cron, métricas, SEO de servidor e paginação dedicada para categorias muito grandes. `members` e relações no DTO atual são listas de prévia; deverão ganhar endpoints próprios antes de conectar coleções extensas. A hierarquia do índice também pode ser ampliada com `level` quando o parser estiver implementado.
