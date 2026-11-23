import { expect, test } from '@playwright/test'
const headers = { 'access-control-allow-origin': 'http://127.0.0.1:4173', 'access-control-allow-credentials': 'true' }

test('returning and reloading show cached article while API refresh is pending', async ({ page }) => {
  let version = 1
  let release: (() => void) | undefined
  await page.route('http://127.0.0.1:8080/**', async route => {
    const url = new URL(route.request().url())
    if (!url.pathname.startsWith('/v1/pages/')) return route.fulfill({ headers, json: { data: { user: null, csrf_token: 'test' } } })
    const slug = decodeURIComponent(url.pathname.split('/').at(-1)!)
    const current = version
    if (slug === 'Cache_A' && current > 1) await new Promise<void>(resolve => { release = resolve })
    await route.fulfill({ headers, json: { data: { id: slug, slug, title: slug, html: `<p>Versão ${current}</p><a href="/Cache_B/">Ir para B</a><a href="/Cache_A/">Voltar para A</a>`, description: `Descrição ${current}`, updated_at: null, canonical_url: '' } } })
  })
  await page.goto('/Cache_A/')
  await expect(page.locator('.wiki-content')).toContainText('Versão 1')
  await page.getByRole('link', { name: 'Ir para B' }).click()
  await expect(page.locator('h1')).toHaveText('Cache_B')
  version = 2
  await page.getByRole('link', { name: 'Voltar para A' }).click()
  await expect(page.locator('.wiki-content')).toContainText('Versão 1')
  await expect.poll(() => !!release).toBe(true)
  release!(); release = undefined
  await expect(page.locator('.wiki-content')).toContainText('Versão 2')
  await expect(page.locator('meta[name="description"]')).toHaveAttribute('content', 'Descrição 2')
  version = 3
  await page.reload()
  await expect(page.locator('.wiki-content')).toContainText('Versão 2')
  await expect.poll(() => !!release).toBe(true)
  release!()
  await expect(page.locator('.wiki-content')).toContainText('Versão 3')
})

test('404 evicts the cached article and a 503 preserves it', async ({ page }) => {
  let status = 200
  await page.route('http://127.0.0.1:8080/**', route => {
    if (!route.request().url().includes('/v1/pages/')) return route.fulfill({ headers, json: { data: { user: null, csrf_token: 'test' } } })
    return route.fulfill({ headers, status, json: status === 200 ? { data: { id: 'A', slug: 'Cache_A', title: 'Cache A', html: '<p>Conteúdo em cache</p>', description: '', updated_at: null, canonical_url: '' } } : { error: { code: 'error' } } })
  })
  await page.goto('/Cache_A/')
  await expect(page.locator('.wiki-content')).toContainText('Conteúdo em cache')
  status = 503
  await page.reload()
  await expect(page.getByRole('alert')).toBeVisible()
  await expect(page.locator('.wiki-content')).toContainText('Conteúdo em cache')
  status = 404
  await page.getByRole('button', { name: 'Tentar novamente' }).click()
  await expect(page.getByRole('heading', { name: 'Página não encontrada' })).toBeVisible()
  expect(await page.evaluate(() => Object.keys(sessionStorage).filter(key => key.startsWith('mdawiki:articles:')).map(key => sessionStorage.getItem(key)).join(''))).not.toContain('Conteúdo em cache')
})

test('API slug replaces case and accent variants without refetch, keeping query/hash and browser history', async ({ page }) => {
  const canonical = 'Especialidade_de_Floricultura'
  const variants = ['EspecialidadE_de_Floriculturá', 'especialidade_de_floricultura']
  let reads = 0
  const visits: string[] = []
  await page.route('http://127.0.0.1:8080/**', async route => {
    const request = route.request(), url = new URL(request.url())
    if (request.method() === 'OPTIONS') return route.fulfill({ status: 204, headers: { ...headers, 'access-control-allow-methods': 'GET, POST, OPTIONS', 'access-control-allow-headers': 'Accept, Content-Type, X-CSRF-Token' } })
    if (url.pathname.startsWith('/v1/pages/')) {
      reads++
      return route.fulfill({ headers, json: { data: { id: 'flor', slug: canonical, title: 'Floricultura', html: '<p id="secao">Conteúdo canônico</p>', description: '', canonical_url: 'https://mda.wiki.br/' + canonical + '/' } } })
    }
    if (url.pathname === '/v1/visits') visits.push(request.postDataJSON().slug)
    return route.fulfill({ headers, json: { data: url.pathname.endsWith('/auth/session') ? { user: null, csrf_token: 'test' } : {} } })
  })
  for (const variant of variants) {
    const before = reads
    await page.goto('/p/doacao/')
    await page.goto('/' + encodeURIComponent(variant) + '/?origem=teste#secao')
    await expect.poll(() => decodeURIComponent(new URL(page.url()).pathname)).toBe('/' + canonical + '/')
    expect(new URL(page.url()).search).toBe('?origem=teste')
    expect(new URL(page.url()).hash).toBe('#secao')
    await expect(page.locator('.wiki-content')).toContainText('Conteúdo canônico')
    await expect(page.getByRole('link', { name: 'Editar Página' })).toHaveAttribute('href', '/' + canonical + '/editar')
    expect(reads - before).toBe(1)
    await page.goBack()
    await expect(page).toHaveURL(/\/p\/doacao\/$/)
  }
  await expect.poll(() => visits.length).toBe(2)
  expect(visits).toEqual([canonical, canonical])
})
