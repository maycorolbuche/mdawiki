import { expect, test } from '@playwright/test'

test.beforeEach(async ({ page }) => {
  await page.route('http://127.0.0.1:8080/**', route => route.fulfill({
    headers: { 'access-control-allow-origin': 'http://127.0.0.1:4173', 'access-control-allow-credentials': 'true' },
    json: { data: { user: null, csrf_token: 'test', items: [], next_offset: null } },
  }))
})

test('desktop sidebar keeps icons, persists both states and expands groups', async ({ page }) => {
  await page.setViewportSize({ width: 1535, height: 900 })
  const errors: string[] = []
  page.on('pageerror', error => errors.push(error.message))
  await page.goto('/page@Busca/')
  const menu = page.locator('#site-menu')
  const toggle = page.getByRole('button', { name: 'Menu do site', exact: true })
  const width = () => menu.evaluate(el => Math.round(el.getBoundingClientRect().width))
  await expect.poll(width).toBe(230)
  await toggle.click()
  await expect.poll(width).toBe(64)
  await expect(menu).toBeVisible()
  await expect(menu.getByRole('link', { name: 'Acampamento', exact: true }).locator('img')).toBeVisible()
  await expect(menu.getByRole('link', { name: 'Acampamento', exact: true }).locator('.menu-text')).toHaveCSS('opacity', '0')
  await expect(page.locator('.main-content')).toHaveCSS('margin-left', '64px')
  await page.screenshot({ path: 'test-results/sidebar-collapsed.png', fullPage: true })
  await page.reload()
  await expect.poll(width).toBe(64)
  await expect(toggle).toHaveAttribute('aria-expanded', 'false')
  await menu.locator('summary').first().click()
  await expect.poll(width).toBe(230)
  await expect(menu.getByRole('link', { name: 'Amigo', exact: true })).toBeVisible()
  await page.reload()
  await expect.poll(width).toBe(230)
  await expect(toggle).toHaveAttribute('aria-expanded', 'true')
  await expect(menu).toHaveCSS('scrollbar-color', 'rgb(100, 119, 138) rgb(38, 44, 51)')
  await page.screenshot({ path: 'test-results/sidebar-expanded.png', fullPage: true })
  expect(errors).toEqual([])
})

test('mobile drawer remains full width with a saved desktop preference', async ({ page }) => {
  await page.addInitScript(() => localStorage.setItem('mdawiki.sidebar.collapsed', 'true'))
  await page.setViewportSize({ width: 390, height: 844 })
  await page.goto('/page@Busca/')
  const menu = page.locator('#site-menu')
  await expect(menu).toBeHidden()
  await page.getByRole('button', { name: 'Menu do site', exact: true }).click()
  await expect(menu).toBeVisible()
  await expect.poll(() => menu.evaluate(el => Math.round(el.getBoundingClientRect().width))).toBe(230)
  await expect(menu.getByRole('link', { name: 'Acampamento', exact: true }).locator('.menu-text')).toHaveCSS('opacity', '1')
  await page.locator('.menu-backdrop').click({ position: { x: 350, y: 100 } })
  await expect(menu).toBeHidden()
  await page.setViewportSize({ width: 1535, height: 900 })
  await expect.poll(() => menu.evaluate(el => Math.round(el.getBoundingClientRect().width))).toBe(64)
})

test('menu works without browser storage and respects reduced motion', async ({ page }) => {
  await page.setViewportSize({ width: 1535, height: 900 })
  await page.emulateMedia({ reducedMotion: 'reduce' })
  await page.addInitScript(() => {
    Storage.prototype.getItem = () => { throw new Error('Storage disabled') }
    Storage.prototype.setItem = () => { throw new Error('Storage disabled') }
  })
  const errors: string[] = []
  page.on('pageerror', error => errors.push(error.message))
  await page.goto('/page@Busca/')
  await page.getByRole('button', { name: 'Menu do site', exact: true }).click()
  await expect(page.locator('#site-menu')).toHaveCSS('width', '64px')
  await expect(page.locator('#site-menu')).toHaveCSS('transition-duration', '0s')
  expect(errors).toEqual([])
})
