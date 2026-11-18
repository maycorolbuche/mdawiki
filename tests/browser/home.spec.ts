import { expect, test } from '@playwright/test'

const headings = ['Páginas Aleatórias', 'Top 10 de Hoje', 'Top 10 dos últimos 7 dias', 'Top 10 dos últimos 30 dias', 'Nos ajude com esses conteúdos', 'Últimas alterações']
const article = (id: string, title: string) => ({ id, slug: id, title, description: 'Resumo do conteúdo da wiki.', image: 'http://127.0.0.1:4173/assets/ico_classe_dbv.svg' })
const home = {
  random: [article('Aleatória_1', 'Primeira página'), article('Aleatória_2', 'Segunda página'), article('Aleatória_3', 'Terceira página')],
  top_today: [article('Hoje', 'Artigo de hoje')], top_week: [article('Semana', 'Artigo da semana')], top_month: [article('Mês', 'Artigo do mês')],
  missing: [article('Conteúdo_novo', 'Conteúdo a criar')], recent: [article('Recente', 'Artigo atualizado')],
}
const headers = { 'access-control-allow-origin': 'http://127.0.0.1:4173', 'access-control-allow-credentials': 'true' }

test('static home keeps layout but embeds no dynamic items or article catalog', async ({ browser }) => {
  const context = await browser.newContext({ javaScriptEnabled: false, viewport: { width: 1535, height: 900 } })
  const page = await context.newPage()
  await page.goto('http://127.0.0.1:4173/')
  for (const heading of headings) await expect(page.getByRole('heading', { name: heading, exact: true })).toBeVisible()
  await expect(page.locator('.home-cover img')).toBeVisible()
  await expect.poll(() => page.locator('.home-cover img').evaluate((img: HTMLImageElement) => img.naturalWidth)).toBeGreaterThan(0)
  await expect(page.locator('.home-random-card')).toHaveCount(0)
  await expect(page.locator('.home-table')).toHaveCount(0)
  const snapshot = JSON.parse((await page.locator('#page-data').textContent())!)
  expect(snapshot.pages).toEqual([])
  expect(snapshot.page).toBeNull()
  expect(snapshot).not.toHaveProperty('home')
  const columns = await page.locator('.home-rankings .home-panel').evaluateAll(nodes => nodes.map(node => Math.round(node.getBoundingClientRect().top)))
  expect(new Set(columns).size).toBe(1)
  await context.close()
})

test('home loads legacy panels from API and keeps internal links in SPA', async ({ page }) => {
  const errors: string[] = []
  page.on('pageerror', error => errors.push(error.message))
  page.on('console', message => { if (/Hydration.*mismatch/i.test(message.text())) errors.push(message.text()) })
  await page.setViewportSize({ width: 1535, height: 1000 })
  await page.route('http://127.0.0.1:8080/**', route => route.fulfill({ headers, json: { data: route.request().url().endsWith('/home') ? home : { user: null, csrf_token: 'test' } } }))
  await page.goto('/')
  await expect(page.getByRole('link', { name: 'Primeira página' })).toBeVisible()
  await expect(page.getByRole('table')).toHaveCount(5)
  await expect(page.getByRole('link', { name: 'Conteúdo a criar', exact: true })).toHaveClass(/text-danger/)
  await expect(page.getByRole('link', { name: 'Artigo do mês', exact: true })).toHaveAttribute('href', '/M%C3%AAs/')
  await expect(page.locator('.home-update-error')).toHaveCount(0)
  await page.screenshot({ path: 'test-results/home-desktop.png', fullPage: true })
  await page.setViewportSize({ width: 390, height: 844 })
  await expect(page.locator('.main-content')).toHaveCSS('margin-left', '0px')
  await expect(page.locator('#site-menu')).toBeHidden()
  await expect.poll(() => page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true)
  const columns = await page.locator('.home-rankings .home-panel').evaluateAll(nodes => nodes.map(node => node.getBoundingClientRect().top))
  expect(columns[1]).toBeGreaterThan(columns[0]!)
  expect(columns[2]).toBeGreaterThan(columns[1]!)
  await page.screenshot({ path: 'test-results/home-mobile.png', fullPage: true })
  expect(errors).toEqual([])
})

test('initial load and F5 stay empty until the current API response arrives', async ({ page }) => {
  let calls = 0
  let release = () => {}
  await page.route('http://127.0.0.1:8080/**', async route => {
    if (!route.request().url().endsWith('/home')) return route.fulfill({ headers, json: { data: { user: null, csrf_token: 'test' } } })
    calls++
    await new Promise<void>(resolve => { release = resolve })
    return route.fulfill({ headers, json: { data: { ...home, random: calls === 1 ? home.random : [article('Novo', 'Novo sorteio da API')] } } })
  })
  await page.goto('/', { waitUntil: 'domcontentloaded' })
  await expect.poll(() => calls).toBe(1)
  await expect(page.locator('.home-loading')).toBeVisible()
  await expect(page.locator('.home-random-card')).toHaveCount(0)
  release()
  await expect(page.getByRole('link', { name: 'Primeira página' })).toBeVisible()
  await page.reload({ waitUntil: 'domcontentloaded' })
  await expect.poll(() => calls).toBe(2)
  await expect(page.locator('.home-loading')).toBeVisible()
  await expect(page.locator('.home-random-card')).toHaveCount(0)
  release()
  await expect(page.getByRole('link', { name: 'Novo sorteio da API' })).toBeVisible()
  await expect(page.getByRole('link', { name: 'Primeira página' })).toHaveCount(0)
})

test('home failure shows no preloaded items and retry loads every block', async ({ page }) => {
  let fail = true
  await page.route('http://127.0.0.1:8080/**', route => route.fulfill({ headers, status: fail ? 503 : 200, json: fail ? { error: { message: 'Unavailable' } } : { data: home } }))
  await page.goto('/')
  await expect(page.locator('.home-update-error')).toBeVisible()
  await expect(page.locator('.home-random-card')).toHaveCount(0)
  await expect(page.locator('.home-loading')).toHaveCount(0)
  fail = false
  await page.getByRole('button', { name: 'Tentar novamente' }).click()
  await expect(page.getByRole('link', { name: 'Artigo atualizado', exact: true })).toBeVisible()
  await expect(page.locator('.home-update-error')).toHaveCount(0)
})

