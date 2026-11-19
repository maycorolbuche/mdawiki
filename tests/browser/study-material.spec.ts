import { expect, test } from '@playwright/test'
import { readFile } from 'node:fs/promises'
const fixtures = JSON.parse(await readFile('tests/fixtures/pages.json', 'utf8'))
const article = fixtures.find((page: any) => page.id === 'conteudo_formatado')
const headers = { 'access-control-allow-origin': 'http://127.0.0.1:4173', 'access-control-allow-credentials': 'true' }

for (const javascript of [false, true]) {
  test(`study material renders below title with JavaScript ${javascript}`, async ({ browser }) => {
    const context = await browser.newContext({ javaScriptEnabled: javascript })
    const page = await context.newPage()
    if (javascript) await page.route('http://127.0.0.1:8080/**', route => route.fulfill({ headers, json: { data: route.request().url().includes('/auth/session') ? { user: null, csrf_token: 'test' } : article } }))
    await page.goto('http://127.0.0.1:4173/' + encodeURIComponent(article.slug) + '/')
    const link = page.locator('.study-material a')
    await expect(link).toHaveAttribute('href', article.study_material_url)
    await expect(link).toHaveAttribute('target', '_blank')
    await expect(link).toHaveAttribute('rel', 'noopener noreferrer')
    await expect(page.locator('h1')).toHaveClass('has-study-material')
    await context.close()
  })
}

test('untrusted material URLs are hidden after API refresh', async ({ page }) => {
  await page.route('http://127.0.0.1:8080/**', route => route.fulfill({ headers, json: { data: route.request().url().includes('/auth/session') ? { user: null, csrf_token: 'test' } : { ...article, title: 'Atualizado', study_material_url: 'https://evil.test/' } } }))
  await page.goto('http://127.0.0.1:4173/' + encodeURIComponent(article.slug) + '/')
  await expect(page.locator('h1')).toHaveText('Atualizado')
  await expect(page.locator('.study-material')).toHaveCount(0)
})
