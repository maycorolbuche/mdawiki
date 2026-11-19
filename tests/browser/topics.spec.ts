import { expect, test } from '@playwright/test'
import { readFile } from 'node:fs/promises'
const fixtures = JSON.parse(await readFile('tests/fixtures/pages.json', 'utf8'))
const article = fixtures.find((page: any) => page.id === 'topicos')
const headers = { 'access-control-allow-origin': 'http://127.0.0.1:4173', 'access-control-allow-credentials': 'true' }

test('static topics preserve real fragment links and heading targets', async ({ request }) => {
  const response = await request.get('/Topicos/')
  const html = await response.text()
  expect(html).toContain('href="#significado"')
  expect(html).toContain('href="#triangulo"')
  expect(html).toContain('id="significado"')
  expect(html).toContain('aria-expanded="false"')
  expect(html).not.toContain('onclick=')
})

test('API topics toggle with mouse/keyboard and navigate to the actual heading', async ({ page }) => {
  await page.route('http://127.0.0.1:8080/**', route => route.fulfill({ headers, json: { data: route.request().url().includes('/auth/session') ? { user: null, csrf_token: 'test' } : article } }))
  await page.goto('/Topicos/')
  const toggle = page.getByRole('button', { name: 'Mostrar', exact: true })
  await expect(toggle).toBeVisible()
  await expect(page.locator('#indice_conteudo')).toBeHidden()
  await toggle.click()
  const hide = page.getByRole('button', { name: 'Esconder', exact: true })
  await expect(hide).toHaveAttribute('aria-expanded', 'true')
  await expect(page.locator('#indice_conteudo')).toBeVisible()
  await expect(page.locator('.article-topics')).toHaveCSS('border-top-width', '1px')
  await expect(page.locator('.topic-list > li')).toHaveCount(2)
  await expect(page.locator('.topic-list > li').first().locator('ol > li')).toHaveCount(2)
  await page.screenshot({ path: 'test-results/article-topics.png', fullPage: true })
  await hide.press('Enter')
  await expect(page.locator('#indice_conteudo')).toBeHidden()
  await page.getByRole('button', { name: 'Mostrar', exact: true }).press('Space')
  await expect(page.locator('#indice_conteudo')).toBeVisible()
  await page.locator('#indice_conteudo').getByRole('link', { name: 'Triângulo', exact: true }).click()
  await expect(page).toHaveURL(/#triangulo$/)
  await expect.poll(async () => Math.round((await page.locator('h3#triangulo').boundingBox())!.y)).toBeGreaterThanOrEqual(65)
  await expect.poll(async () => Math.round((await page.locator('h3#triangulo').boundingBox())!.y)).toBeLessThanOrEqual(80)
})
