import { expect, test, type BrowserContext } from '@playwright/test'
import { readFile } from 'node:fs/promises'
const fixtures = JSON.parse(await readFile('tests/fixtures/pages.json', 'utf8'))
const article = fixtures[2]
const path = '/' + encodeURIComponent(article.slug) + '/'
const headers = { 'access-control-allow-origin': 'http://127.0.0.1:4173' }
const media: Record<string, { contentType: string; base64: string }> = JSON.parse(await readFile('tests/fixtures/media.json', 'utf8'))
async function mockMedia(context: BrowserContext) {
  await context.route('https://mda.wiki.br/site/@imgs_wiki/**', route => {
    const asset = media[route.request().url()]
    return asset ? route.fulfill({ contentType: asset.contentType, body: Buffer.from(asset.base64, 'base64') }) : route.abort()
  })
}
test.beforeEach(async ({ context }) => { await mockMedia(context) })

test('static article includes complete navigation and relation panels without JavaScript', async ({ browser }) => {
  const context = await browser.newContext({ javaScriptEnabled: false, viewport: { width: 1535, height: 900 } })
  await mockMedia(context)
  const page = await context.newPage()
  await page.goto('http://127.0.0.1:4173' + path)
  await expect(page.getByRole('img', { name: 'MDAWiki', exact: true })).toBeVisible()
  await expect.poll(() => page.locator('.header-brand img').evaluate((img: HTMLImageElement) => img.naturalWidth)).toBeGreaterThan(0)
  for (const img of await page.locator('.site-menu img').all()) await expect.poll(() => img.evaluate((el: HTMLImageElement) => el.naturalWidth)).toBeGreaterThan(0)
  await expect(page.locator('.site-menu').getByRole('heading', { name: 'Desbravadores' })).toBeVisible()
  await expect(page.locator('.site-menu').getByRole('link', { name: 'Acampamento', exact: true })).toBeVisible()
  await expect(page.locator('.relation-blue h2')).toHaveText('Categorias 2')
  await expect(page.locator('.relation-cyan h2')).toHaveText('Menções à esta página 3')
  await expect(page.locator('.relation-green h2')).toHaveText('Páginas Relacionadas 5')
  await page.locator('.menu-group').first().locator('summary').click()
  await expect(page.getByRole('link', { name: 'Amigo', exact: true })).toBeVisible()
  await page.locator('.menu-group').first().locator('summary').click()
  await expect(page.locator('.templ_especialidade_dbv tr').first()).toHaveCSS('background-color', 'rgb(30, 144, 255)')
  await expect(page.locator('.templ_especialidade_dbv tr').nth(1)).toHaveCSS('background-color', 'rgb(0, 191, 255)')
  expect(await page.locator('.templ_especialidade_dbv').evaluate(el => Math.round(el.getBoundingClientRect().width))).toBeGreaterThan(800)
  expect(await page.locator('.article-card').evaluate(el => el.getBoundingClientRect().x)).toBeLessThan(await page.locator('.page-relations').evaluate(el => el.getBoundingClientRect().x))
  await page.screenshot({ path: 'test-results/legacy-desktop.png', fullPage: true })
  await context.close()
})

test('API refresh updates relation panels and category members together with article', async ({ page }) => {
  const problems: string[] = []
  page.on('pageerror', error => problems.push(error.message))
  page.on('console', message => { if (/Hydration.*mismatch/i.test(message.text())) problems.push(message.text()) })
  await page.route('http://127.0.0.1:8080/**', route => route.fulfill({ headers, json: { data: { ...article,
    categories: [{ id: 'nova', slug: 'Nova_categoria', title: 'Nova categoria' }], mentions: [], related_pages: [],
    category_members: [{ id: 'membro', slug: 'Membro', title: 'Página integrante' }],
  } } }))
  await page.goto(path)
  await expect(page.locator('.relation-blue h2')).toHaveText('Categorias 1')
  await expect(page.getByRole('link', { name: 'Nova categoria', exact: true })).toBeVisible()
  await expect(page.locator('.relation-cyan')).toHaveCount(0)
  await expect(page.locator('.relation-green')).toHaveCount(0)
  await expect(page.getByRole('link', { name: 'Página integrante' })).toBeVisible()
  expect(problems).toEqual([])
  await page.getByRole('link', { name: 'Editar Página' }).getAttribute('href').then(href => expect(href).toBe('/' + encodeURIComponent(article.slug) + '/editar'))
})

test('mobile menu opens, expands groups and closes after SPA navigation without page overflow', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 })
  await page.route('http://127.0.0.1:8080/**', route => route.fulfill({ headers, json: { data: article } }))
  await page.goto(path)
  await expect(page.locator('.wiki-content table')).toBeVisible()
  await expect(page.locator('.wiki-content table td').nth(1)).toHaveCSS('white-space', 'nowrap')
  expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBe(true)
  await page.getByRole('button', { name: 'Menu do site', exact: true }).click()
  await expect(page.locator('#site-menu')).toBeVisible()
  await page.locator('.menu-group').first().locator('summary').click()
  await expect(page.getByRole('link', { name: 'Amigo', exact: true })).toBeVisible()
  await page.getByRole('link', { name: 'Amigo', exact: true }).click()
  await expect(page).toHaveURL(/Cart%C3%A3o_de_Amigo/)
  await expect(page.locator('#site-menu')).toBeHidden()
  await page.screenshot({ path: 'test-results/legacy-mobile.png', fullPage: true })
})
