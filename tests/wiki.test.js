import assert from 'node:assert/strict'
import { test } from 'node:test'
import { createApiClient, ApiError } from '../src/services/api/client.js'
import { readPage } from '../src/services/api/contracts.js'
import { createWikiService } from '../src/services/wikiService.js'
import { mockFetch } from '../src/mocks/transport.js'
import { pageLocation, validSlug, safeUrl } from '../src/utils/wikiRoutes.js'
const service = () =>
  createWikiService(createApiClient({ baseUrl: 'https://api.test/v1', fetcher: mockFetch }))

test('wiki resolve home, especialidade, artigo, categoria e imagem pelo mesmo contrato', async () => {
  const api = service()
  for (const [slug, type] of [
    ['', 'home'],
    ['adra', 'category'],
    ['acampamento', 'article'],
    ['imagem-emblema-adra', 'media'],
    ['resposta-a-emergencias-e-desastres', 'specialty'],
  ]) {
    const page = await api.page(slug)
    assert.equal(page.type, type)
    assert.equal(page.slug, slug)
  }
  assert.ok((await api.site()).navigation.length)
  assert.ok((await api.revisions('acampamento')).data.length)
  assert.equal((await api.discussions('acampamento')).data.length, 0)
  assert.match((await api.source('acampamento')).text, /Planejamento/)
})

test('alias mantém slug canônico e busca pesquisa texto com paginação', async () => {
  const api = service()
  const alias = await api.page('Resposta_a_Emergências_e_Desastres')
  assert.equal(alias.redirectFrom, 'Resposta_a_Emergências_e_Desastres')
  assert.equal(alias.slug, 'resposta-a-emergencias-e-desastres')
  const search = await api.search({ q: 'convivencia', perPage: 1 })
  assert.equal(search.data[0].slug, 'acampamento')
  const first = await api.search({ perPage: 2, page: 1 })
  const second = await api.search({ perPage: 2, page: 2 })
  assert.equal(first.meta.hasMore, true)
  assert.notEqual(first.data[0].slug, second.data[0].slug)
  assert.equal((await api.search({ q: 'inexistentezzz' })).meta.total, 0)
})

test('404 continua erro e respostas simuladas são cópias independentes', async () => {
  const api = service()
  await assert.rejects(
    api.page('inexistente'),
    (error) => error instanceof ApiError && error.status === 404,
  )
  const page = await api.page('acampamento')
  page.sections[0].blocks[0].text = 'modificado'
  assert.notEqual((await api.page('acampamento')).sections[0].blocks[0].text, 'modificado')
})

test('cliente codifica query, mantém status e não troca erro real por dados fictícios', async () => {
  let requested
  const api = createApiClient({
    baseUrl: 'https://api.test/v1/',
    fetcher: async (url, options) => {
      requested = { url, options }
      return Response.json(
        { error: { code: 'UNAVAILABLE', message: 'Indisponível' } },
        { status: 503 },
      )
    },
  })
  await assert.rejects(
    api.get('/search', { query: { q: 'nós & amarras', page: 2 } }),
    (error) => error.status === 503 && error.code === 'UNAVAILABLE',
  )
  assert.equal(new URL(requested.url).searchParams.get('q'), 'nós & amarras')
  assert.equal(requested.options.credentials, 'omit')
})

test('JSON inválido, envelope ausente, falha de rede, cancelamento e timeout', async () => {
  for (const response of [new Response('<html>'), Response.json({ wrong: [] })]) {
    const client = createApiClient({ baseUrl: '/v1', fetcher: async () => response })
    await assert.rejects(client.get('/site'), (error) => error.code === 'INVALID_RESPONSE')
  }
  const network = createApiClient({
    baseUrl: '/v1',
    fetcher: async () => {
      throw new TypeError('offline')
    },
  })
  await assert.rejects(network.get('/site'), (error) => error.code === 'NETWORK_ERROR')
  const controller = new AbortController()
  controller.abort()
  await assert.rejects(network.get('/site', { signal: controller.signal }), { name: 'AbortError' })
  const timeout = createApiClient({
    baseUrl: '/v1',
    timeout: 5,
    fetcher: (_, { signal }) =>
      new Promise((resolve, reject) =>
        signal.addEventListener('abort', () => reject(new DOMException('Aborted', 'AbortError'))),
      ),
  })
  await assert.rejects(timeout.get('/site'), (error) => error.code === 'TIMEOUT')
})

test('URLs públicas têm um segmento; API decide o slug, sem normalização destrutiva', () => {
  assert.equal(pageLocation('adra').name, 'wiki-page')
  assert.equal(pageLocation('adra').params.slug, 'adra')
  assert.equal(validSlug('Resposta_a_Emergências'), true)
  for (const slug of ['a/b', '../x', '..', 'a?x', 'a#x', 'a\nb'])
    assert.equal(validSlug(slug), false)
  for (const url of ['javascript:alert(1)', '//host.test/x', '/\\host.test', 'data:text/html,x'])
    assert.equal(safeUrl(url), '')
  assert.equal(safeUrl('https://adra.org.br/'), 'https://adra.org.br/')
  assert.throws(() => readPage({ id: 'a', title: 'A', slug: '../x' }), ApiError)
})
