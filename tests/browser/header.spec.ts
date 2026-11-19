import { expect, test } from '@playwright/test'
const headers = { 'access-control-allow-origin': 'http://127.0.0.1:4173', 'access-control-allow-credentials': 'true', 'access-control-allow-methods': 'GET, POST, OPTIONS', 'access-control-allow-headers': 'Accept, Content-Type, X-CSRF-Token' }
const user = { ID: 1, NOME: 'Mayco Rolbuche', APELIDO: 'MRolbuche', EMAIL: 'mayco@example.test', NIVEL: 1, photo: '/assets/logo_mdawiki.svg' }

test('account dropdown shows avatar, account links, keyboard dismissal and logout', async ({ page }) => {
  let loggedIn = true
  await page.route('http://127.0.0.1:8080/**', route => {
    if (route.request().method() === 'OPTIONS') return route.fulfill({ headers, status: 204 })
    const path = new URL(route.request().url()).pathname
    if (path === '/v1/auth/logout') loggedIn = false
    return route.fulfill({ headers, json: { data: path.includes('/auth/') ? { user: loggedIn ? user : null, csrf_token: 'csrf-test' } : { items: [] } } })
  })
  await page.goto('/page%40Listas/')
  const trigger = page.getByRole('button', { name: 'Menu de MRolbuche' })
  await expect(trigger.locator('img')).toHaveAttribute('src', user.photo)
  await expect(page.locator('#account-dropdown')).toHaveCount(0)
  await trigger.click()
  await expect(page.locator('#account-dropdown').getByRole('link', { name: 'Meu Perfil' })).toBeVisible()
  await expect(page.locator('#account-dropdown').getByRole('link', { name: 'Alterar E-mail' })).toHaveAttribute('href', /page@AlterarEmail/)
  await expect(page.locator('#account-dropdown').getByRole('link', { name: 'Alterar Senha' })).toBeVisible()
  await page.screenshot({ path: 'test-results/account-dropdown-desktop.png' })
  await trigger.press('Escape')
  await expect(trigger).toHaveAttribute('aria-expanded', 'false')
  await trigger.press('ArrowDown')
  await expect(page.locator('#account-dropdown').getByRole('link', { name: 'Meu Perfil' })).toBeFocused()
  await page.locator('.main-content').click({ position: { x: 10, y: 100 } })
  await expect(trigger).toHaveAttribute('aria-expanded', 'false')
  await page.setViewportSize({ width: 390, height: 844 })
  await trigger.click()
  await expect(page.locator('#account-dropdown')).toBeVisible()
  expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true)
  await page.screenshot({ path: 'test-results/account-dropdown-mobile.png' })
  await page.locator('#account-dropdown').getByRole('button', { name: 'Sair', exact: true }).click()
  await expect(trigger).toHaveCount(0)
})

test('live search selects an article while Enter opens the full search', async ({ page }) => {
  const terms: string[] = []
  await page.route('http://127.0.0.1:8080/**', route => {
    const url = new URL(route.request().url())
    if (url.pathname === '/v1/search') {
      terms.push(url.searchParams.get('q') || '')
      return route.fulfill({ headers, json: { data: { items: [{ id: 'd1', slug: 'Emblema_D1', title: 'Emblema D1' }], next_offset: null } } })
    }
    return route.fulfill({ headers, json: { data: url.pathname.includes('/auth/') ? { user: null, csrf_token: 'csrf-test' } : { id: 'd1', slug: 'Emblema_D1', title: 'Emblema D1', html: '<p>Artigo escolhido.</p>', description: '', canonical_url: '' } } })
  })
  await page.goto('/page%40Listas/')
  const input = page.getByRole('combobox', { name: 'Localizar na MDAWiki' })
  await input.fill('Emblema')
  const option = page.getByRole('option', { name: 'Emblema D1' })
  await expect(option).toBeVisible()
  await page.screenshot({ path: 'test-results/search-suggestions.png' })
  await option.click()
  await expect(page).toHaveURL(/\/Emblema_D1\/$/)
  await expect(page.locator('.wiki-content')).toContainText('Artigo escolhido.')
  await input.fill('Cães')
  await input.press('Enter')
  await expect(page).toHaveURL(/page@Busca\?q=C%C3%A3es/)
  await expect(page.getByRole('heading', { name: 'Localizar na MDAWiki' })).toBeVisible()
  expect(terms).toContain('Emblema')
})

test('search debounces, discards stale responses and dismisses keyboard suggestions', async ({ page }) => {
  let release: (() => void) | undefined
  await page.route('http://127.0.0.1:8080/**', async route => {
    const url = new URL(route.request().url())
    if (url.pathname !== '/v1/search') return route.fulfill({ headers, json: { data: { items: [], user: null, csrf_token: 'csrf-test' } } })
    const q = url.searchParams.get('q')!
    if (q === 'Antigo') await new Promise<void>(resolve => { release = resolve })
    await route.fulfill({ headers, json: { data: { items: [{ id: q, slug: q, title: q }], next_offset: null } } })
  })
  await page.goto('/page%40Listas/')
  const input = page.getByRole('combobox', { name: 'Localizar na MDAWiki' })
  await input.fill('Antigo')
  await expect.poll(() => !!release).toBe(true)
  await input.fill('Novo')
  await expect(page.getByRole('option', { name: 'Novo', exact: true })).toBeVisible()
  release!()
  await expect(page.getByRole('option', { name: 'Antigo', exact: true })).toHaveCount(0)
  await input.press('ArrowDown')
  await expect(page.getByRole('option', { name: 'Novo', exact: true })).toBeFocused()
  await page.getByRole('option', { name: 'Novo', exact: true }).press('Escape')
  await expect(input).toBeFocused()
  await expect(input).toHaveAttribute('aria-expanded', 'false')
  await input.fill('')
  await expect(page.getByRole('listbox')).toHaveCount(0)
})

test('Bootstrap icon font loads for donation and print actions', async ({ page }) => {
  await page.route('http://127.0.0.1:8080/**', route => route.fulfill({ headers, status: 503, json: { error: { code: 'service_unavailable' } } }))
  await page.goto('/Especialidade_de_C%C3%A3es/')
  await expect(page.locator('.donation-link .bi-cash-stack')).toBeVisible()
  await expect(page.locator('.action-print .bi-printer-fill')).toBeVisible()
  await page.evaluate(async () => { await document.fonts.load('16px bootstrap-icons'); await document.fonts.ready })
  expect(await page.evaluate(() => document.fonts.check('16px bootstrap-icons'))).toBe(true)
  expect(await page.locator('.action-print .bi').evaluate(el => getComputedStyle(el, '::before').fontFamily)).toContain('bootstrap-icons')
})
