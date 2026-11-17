import { expect, test } from '@playwright/test'

const article = { id: 'a', slug: 'Especialidade_de_Cães', title: 'Especialidade de Cães', html: '<p>Conteúdo atualizado pela API.</p>', description: 'Descrição atualizada', updated_at: null, canonical_url: 'https://mda.wiki.br/Especialidade_de_C%C3%A3es/' }

test('generated HTML has article and metadata before JavaScript', async ({ request }) => {
  const response = await request.get('/Especialidade_de_C%C3%A3es/')
  expect(response.status()).toBe(200)
  const html = await response.text()
  expect(html).toContain('Conteúdo publicado na build.')
  expect(html).toContain('<title>Especialidade de Cães — MDAWiki</title>')
  expect(html).toContain('name="description" content="Conheça os requisitos')
})

test('snapshot updates immediately from API and strips executable HTML', async ({ page }) => {
  await page.route('http://127.0.0.1:8080/**', route => route.fulfill({ headers: { 'access-control-allow-origin': 'http://127.0.0.1:4173' }, json: { data: { ...article, html: '<p>Conteúdo atualizado pela API.</p><img src="x" onerror="window.compromised=1"><script>window.compromised=1</script>' } } }))
  await page.goto('/Especialidade_de_C%C3%A3es/')
  await expect(page.locator('.wiki-content')).toContainText('Conteúdo atualizado pela API.')
  await expect(page.locator('meta[name="description"]')).toHaveAttribute('content', 'Descrição atualizada')
  expect(await page.evaluate(() => (window as any).compromised)).toBeUndefined()
})

test('new article loads through HTTP 404 without redirecting away from original path', async ({ page }) => {
  await page.route('http://127.0.0.1:8080/**', route => route.fulfill({ headers: { 'access-control-allow-origin': 'http://127.0.0.1:4173' }, json: { data: { ...article, slug: 'Novo_artigo', title: 'Novo artigo' } } }))
  const response = await page.goto('/Novo_artigo/')
  expect(response?.status()).toBe(404)
  await expect(page.getByRole('heading', { name: 'Novo artigo' })).toBeVisible()
  expect(page.url()).toContain('/Novo_artigo/')
})

test('API 404 removes stale snapshot, but 503 preserves it with retry', async ({ page }) => {
  await page.route('http://127.0.0.1:8080/**', route => route.fulfill({ headers: { 'access-control-allow-origin': 'http://127.0.0.1:4173' }, status: 503, json: { error: { code: 'service_unavailable' } } }))
  await page.goto('/Especialidade_de_C%C3%A3es/')
  await expect(page.getByRole('alert')).toContainText('Exibindo o conteúdo publicado')
  await expect(page.locator('.wiki-content')).toContainText('Conteúdo publicado na build.')
  await page.unroute('http://127.0.0.1:8080/**')
  await page.route('http://127.0.0.1:8080/**', route => route.fulfill({ headers: { 'access-control-allow-origin': 'http://127.0.0.1:4173' }, status: 404, json: { error: { code: 'not_found' } } }))
  await page.getByRole('button', { name: 'Tentar novamente' }).click()
  await expect(page.getByRole('heading', { name: 'Página não encontrada' })).toBeVisible()
  await expect(page.locator('.wiki-content')).toHaveCount(0)
})

test('new missing path distinguishes not-found from an unavailable API', async ({ page }) => {
  await page.route('http://127.0.0.1:8080/**', route => route.fulfill({ headers: { 'access-control-allow-origin': 'http://127.0.0.1:4173' }, status: 404, json: { error: { code: 'not_found' } } }))
  await page.goto('/Nao_existe/')
  await expect(page.getByRole('heading', { name: 'Página não encontrada' })).toBeVisible()
})

test('every client-side article navigation fetches fresh API content', async ({ page }) => {
  let calls = 0
  await page.route('http://127.0.0.1:8080/**', route => {
    if (route.request().url().includes('/v1/pages/')) calls++
    return route.fulfill({ headers: { 'access-control-allow-origin': 'http://127.0.0.1:4173' }, json: { data: { ...article, html: '<a href="https://mda.wiki.br/Novo/">Outro artigo</a>' } } })
  })
  await page.goto('/Especialidade_de_C%C3%A3es/')
  await page.getByRole('link', { name: 'Outro artigo' }).click()
  await expect(page).toHaveURL(/\/Novo\/$/)
  await expect.poll(() => calls).toBe(2)
})
