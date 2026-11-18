import { expect, test, type Page } from '@playwright/test'
async function wiki(page: Page) {
  await page.route('http://127.0.0.1:4174/api/**', route => {
    const path = new URL(route.request().url()).pathname
    const data = path.endsWith('/auth/session') ? { user: null, csrf_token: 'test' }
      : path.endsWith('/home') ? { random: [], top_today: [], top_week: [], top_month: [], recent: [], missing: [] }
      : path.endsWith('/editor') ? { title: 'Segundo', source: 'Texto', base_version: 'v1', media: false }
      : { id: 'segundo', slug: 'Segundo', title: 'Segundo', html: '<p>Artigo.</p><h2 id="requisitos">Requisitos</h2>', description: '', canonical_url: 'https://mda.wiki.br/Segundo/' }
    return route.fulfill({ json: { data } })
  })
  await page.goto('http://127.0.0.1:4174/')
  await expect(page.getByRole('heading', { name: 'A Wikipedia dos Desbravadores e Aventureiros' })).toBeVisible()
}
async function install(page: Page, analyticsId = 'G-TEST123456') {
  await page.evaluate(async id => {
    const { installGoogleServices } = await import('/src/services/google.ts')
    const router = (document.getElementById('app') as any).__vue_app__.config.globalProperties.$router
    installGoogleServices(router, { enabled: true, analyticsId: id, adsenseClient: 'ca-pub-4184100205220975' })
  }, analyticsId)
}
async function navigate(page: Page, path: string) {
  await page.evaluate(path => (document.getElementById('app') as any).__vue_app__.config.globalProperties.$router.push(path), path)
}
async function views(page: Page) {
  return page.evaluate(() => (window.dataLayer || []).map(value => Array.from(value as ArrayLike<any>)).filter(args => args[0] === 'event' && args[1] === 'page_view'))
}

test('publisher verification and ads.txt match the legacy account; local navigation loads no Google scripts', async ({ page, request }) => {
  const response = await request.get('/ads.txt')
  expect(await response.text()).toBe('google.com, pub-4184100205220975, DIRECT, f08c47fec0942fa0\n')
  const html = await (await request.get('/')).text()
  expect(html).toContain('name="google-adsense-account" content="ca-pub-4184100205220975"')
  await wiki(page)
  await expect(page.locator('#mdawiki-adsense, #mdawiki-analytics')).toHaveCount(0)
})

test('GA4 tracks initial and SPA views once, strips query tokens and excludes editing', async ({ page }) => {
  await page.route('https://www.googletagmanager.com/**', route => route.fulfill({ contentType: 'application/javascript', body: '' }))
  await page.route('https://pagead2.googlesyndication.com/**', route => route.fulfill({ contentType: 'application/javascript', body: '' }))
  await wiki(page)
  await install(page)
  await expect.poll(async () => (await views(page)).length).toBe(1)
  await install(page)
  expect((await views(page)).length).toBe(1)
  await navigate(page, '/Segundo?token=segredo#requisitos')
  await expect.poll(async () => (await views(page)).length).toBe(2)
  const current = (await views(page))[1]![2]
  expect(current.page_location).toBe('http://127.0.0.1:4174/Segundo')
  expect(JSON.stringify(await views(page))).not.toContain('segredo')
  await navigate(page, '/Segundo?token=outro#requisitos')
  expect((await views(page)).length).toBe(2)
  await navigate(page, '/Segundo/editar')
  await expect(page.locator('.legacy-edit-title')).toBeVisible()
  expect((await views(page)).length).toBe(2)
  await navigate(page, '/')
  await expect.poll(async () => (await views(page)).length).toBe(3)
  await expect(page.locator('#mdawiki-adsense')).toHaveCount(1)
  await expect(page.locator('#mdawiki-analytics')).toHaveCount(1)
  const configs = await page.evaluate(() => (window.dataLayer || []).map(value => Array.from(value as ArrayLike<any>)).filter(args => args[0] === 'config'))
  expect(configs).toHaveLength(1)
  expect(configs[0]![2].send_page_view).toBe(false)
})

test('AdSense works independently of a GA4 ID and ad blockers do not break navigation', async ({ page }) => {
  const errors: string[] = []
  page.on('pageerror', error => errors.push(error.message))
  await page.route('https://pagead2.googlesyndication.com/**', route => route.abort())
  await wiki(page)
  await install(page, '')
  await expect(page.locator('#mdawiki-adsense')).toHaveAttribute('crossorigin', 'anonymous')
  await expect(page.locator('#mdawiki-adsense')).toHaveAttribute('data-blocked', 'true')
  await expect(page.locator('#mdawiki-analytics')).toHaveCount(0)
  await navigate(page, '/Segundo')
  await expect(page.locator('.wiki-content')).toContainText('Artigo.')
  expect(errors).toEqual([])
})
