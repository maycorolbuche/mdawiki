import { expect, test } from '@playwright/test'

test('fresh Vite development loads the current entry and renders without PWA files', async ({ page }) => {
  const errors: string[] = []
  const requests: string[] = []
  page.on('pageerror', error => errors.push(error.message))
  page.on('request', request => requests.push(request.url()))
  await page.route('http://127.0.0.1:4174/api/**', route => route.fulfill({ json: { data: [{ id: 'a', slug: 'Floricultura', title: 'Floricultura' }] } }))
  await page.goto('http://127.0.0.1:4174/')
  await expect(page.getByRole('heading', { name: 'A Wikipedia dos Desbravadores e Aventureiros' })).toBeVisible()
  await expect(page.getByRole('link', { name: 'Floricultura' })).toBeVisible()
  expect(errors).toEqual([])
  expect(requests.some(url => url.endsWith('/src/entry-client.ts'))).toBe(true)
  expect(requests.some(url => /src\/main\.js|vite-plugin-pwa|manifest\.webmanifest/.test(url))).toBe(false)
})
