import { expect, test } from '@playwright/test'
import { readFile } from 'node:fs/promises'
const fixtures = JSON.parse(await readFile('tests/fixtures/pages.json', 'utf8'))
const article = fixtures.find((page: any) => page.id === 'conteudo_formatado')
const path = '/' + encodeURIComponent(article.slug) + '/'
const headers = { 'access-control-allow-origin': 'http://127.0.0.1:4173', 'access-control-allow-credentials': 'true' }

for (const javascript of [false, true]) {
  test(`legacy article markers and image gallery work with JavaScript ${javascript}`, async ({ browser }) => {
    const context = await browser.newContext({ javaScriptEnabled: javascript, viewport: { width: 1535, height: 950 } })
    const page = await context.newPage()
    if (javascript) await page.route('http://127.0.0.1:8080/**', route => route.fulfill({ headers, json: { data: route.request().url().includes('/auth/session') ? { user: null, csrf_token: 'test' } : article } }))
    await page.goto('http://127.0.0.1:4173' + path)
    const content = page.locator('.wiki-content')
    const nested = content.locator('ol[style*="lower-alpha"]').first()
    await expect(nested).toHaveCSS('list-style-type', 'lower-alpha')
    await expect(content.locator('ol[start="3"]')).toHaveCSS('list-style-type', 'upper-roman')
    await expect(content.locator('ul')).toHaveCSS('list-style-type', 'square')
    const item = nested.locator('li').first()
    expect(await item.evaluate(el => getComputedStyle(el, '::marker').fontWeight)).toBe('700')
    await expect(item.locator('.texto')).toHaveCSS('font-weight', '400')
    await expect(nested.locator('strong')).toHaveCSS('font-weight', '700')
    const gallery = content.locator('.imagens')
    await expect(gallery).toHaveCSS('display', 'flex')
    await expect(gallery).toHaveCSS('flex-wrap', 'wrap')
    await expect(gallery.locator('figure')).toHaveCount(9)
    const positions = await gallery.locator('figure').evaluateAll(nodes => nodes.map(node => Math.round(node.getBoundingClientRect().top)))
    expect(new Set(positions).size).toBe(2)
    expect(positions.filter(top => top === positions[0])).toHaveLength(6)
    await expect(gallery.locator('figcaption').first()).toHaveCSS('font-size', '11px')
    await expect(content.locator('.biography')).toHaveCSS('float', 'right')
    await page.screenshot({ path: `test-results/article-presentation-${javascript ? 'api' : 'static'}.png`, fullPage: true })
    if (javascript) {
      await page.setViewportSize({ width: 390, height: 844 })
      await expect(page.locator('.main-content')).toHaveCSS('margin-left', '0px')
      await expect(page.locator('#site-menu')).toBeHidden()
      expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true)
      await expect(gallery.locator('figure').first()).toBeVisible()
    }
    await context.close()
  })
}
