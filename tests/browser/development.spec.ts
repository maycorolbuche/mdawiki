import { expect, test } from '@playwright/test'

test('fresh Vite development loads the current entry and renders without PWA files', async ({ page }) => {
  const errors: string[] = []
  const compatibilityWarnings: string[] = []
  const requests: string[] = []
  page.on('pageerror', error => errors.push(error.message))
  page.on('console', message => {
    if (/externalized for browser compatibility|Cannot access .*client code|Hydration.*mismatch/i.test(message.text())) compatibilityWarnings.push(message.text())
  })
  page.on('request', request => requests.push(request.url()))
  await page.route('http://127.0.0.1:4174/api/**', route => route.fulfill({ json: { data: { random: [{ id: 'a', slug: 'Floricultura', title: 'Floricultura' }], top_today: [], top_week: [], top_month: [], missing: [], recent: [] } } }))
  await page.goto('http://127.0.0.1:4174/')
  await expect(page.getByRole('heading', { name: 'A Wikipedia dos Desbravadores e Aventureiros' })).toBeVisible()
  await expect(page.getByRole('link', { name: 'Floricultura' })).toBeVisible()
  expect(errors).toEqual([])
  expect(compatibilityWarnings).toEqual([])
  expect(requests.some(url => url.endsWith('/src/entry-client.ts'))).toBe(true)
  expect(requests.some(url => /src\/main\.js|vite-plugin-pwa|manifest\.webmanifest/.test(url))).toBe(false)
})

test('development article sanitizes untrusted HTML without Node compatibility warnings', async ({ page }) => {
  const problems: string[] = []
  page.on('pageerror', error => problems.push(error.message))
  page.on('console', message => {
    if (/externalized for browser compatibility|Cannot access .*client code|Hydration.*mismatch/i.test(message.text())) problems.push(message.text())
  })
  await page.route('http://127.0.0.1:4174/api/**', route => route.fulfill({ json: { data: {
    id: 'floricultura', slug: 'Floricultura', title: 'Floricultura', description: 'Artigo de teste', updated_at: null,
    canonical_url: 'https://site.mda.wiki.br/Floricultura/',
    html: '<h2 id="flores">Flores</h2><p>Conteúdo atualizado.</p><img src="x" onerror="window.compromised=1"><script>window.compromised=1</script><a href="javascript:alert(1)">Inválido</a>',
  } } }))
  await page.goto('http://127.0.0.1:4174/Floricultura/')
  await expect(page.locator('.wiki-content')).toContainText('Conteúdo atualizado.')
  await expect(page.locator('.wiki-content img')).not.toHaveAttribute('onerror')
  await expect(page.locator('.wiki-content a')).not.toHaveAttribute('href')
  expect(await page.evaluate(() => (window as any).compromised)).toBeUndefined()
  expect(problems).toEqual([])
})

