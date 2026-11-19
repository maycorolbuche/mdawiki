import { expect, test } from '@playwright/test'
const headers = { 'access-control-allow-origin': 'http://127.0.0.1:4173', 'access-control-allow-credentials': 'true', 'access-control-allow-methods': 'GET, POST, OPTIONS', 'access-control-allow-headers': 'Accept, Content-Type, X-CSRF-Token' }
const user = { ID: 1, NOME: 'Editor', APELIDO: 'Editor', EMAIL: 'editor@example.test', NIVEL: 1, ENVIA_EMAIL: 'S', photo: '/assets/user.png' }
const source = '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="400"><rect width="400" height="400" fill="red"/><rect x="400" width="400" height="400" fill="blue"/></svg>'

test('profile crop modal cancels without upload and saves a square cropped image', async ({ page }) => {
  const uploads: string[] = []
  await page.route('http://127.0.0.1:8080/**', route => {
    if (route.request().method() === 'OPTIONS') return route.fulfill({ headers, status: 204 })
    const path = new URL(route.request().url()).pathname
    if (path === '/v1/profile/photo') {
      uploads.push(route.request().postDataJSON().data)
      return route.fulfill({ headers, json: { data: { url: uploads.at(-1) } } })
    }
    return route.fulfill({ headers, json: { data: path === '/v1/auth/session' ? { user, csrf_token: 'test' } : user } })
  })
  await page.goto('/page%40Perfil/')
  const picker = page.getByLabel('Foto', { exact: true })
  const file = { name: 'wide-avatar.svg', mimeType: 'image/svg+xml', buffer: Buffer.from(source) }
  await picker.setInputFiles(file)
  const dialog = page.getByRole('dialog', { name: 'Recortar foto de perfil' })
  await expect(dialog).toBeVisible()
  await expect(page.getByRole('button', { name: 'Recortar e salvar' })).toBeEnabled()
  const selection = dialog.locator('cropper-selection')
  expect(await selection.evaluate((element: any) => element.aspectRatio)).toBe(1)
  expect(uploads).toHaveLength(0)
  await dialog.getByRole('button', { name: 'Cancelar', exact: true }).click()
  await expect(dialog).toHaveCount(0)
  await expect(page.locator('.profile-photo')).toHaveAttribute('src', user.photo)
  expect(uploads).toHaveLength(0)
  // The same file can be selected again after cancelling.
  await picker.setInputFiles(file)
  await expect(page.getByRole('button', { name: 'Recortar e salvar' })).toBeEnabled()
  await dialog.getByRole('button', { name: 'Ampliar imagem' }).click()
  await dialog.getByRole('button', { name: 'Girar', exact: true }).click()
  await page.screenshot({ path: 'test-results/profile-crop-desktop.png' })
  await dialog.getByRole('button', { name: 'Recortar e salvar' }).click()
  await expect(dialog).toHaveCount(0)
  expect(uploads).toHaveLength(1)
  const output = await page.locator('.profile-photo').evaluate(async (image: HTMLImageElement) => {
    await image.decode()
    const canvas = document.createElement('canvas'); canvas.width = canvas.height = 512
    const context = canvas.getContext('2d')!; context.drawImage(image, 0, 0)
    return { width: image.naturalWidth, height: image.naturalHeight, alpha: context.getImageData(256, 256, 1, 1).data[3] }
  })
  expect([output.width, output.height]).toEqual([512, 512])
  expect(output.alpha).toBeGreaterThan(0)
  await expect(page.locator('.account-avatar')).toHaveAttribute('src', uploads[0]!)
})

test('mobile crop dialog fits the screen and Escape keeps the current avatar', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 })
  let uploaded = false
  await page.route('http://127.0.0.1:8080/**', route => {
    if (route.request().url().includes('/profile/photo')) uploaded = true
    return route.fulfill({ headers, json: { data: route.request().url().includes('/auth/session') ? { user, csrf_token: 'test' } : user } })
  })
  await page.goto('/page%40Perfil/')
  await page.getByLabel('Foto', { exact: true }).setInputFiles({ name: 'avatar.svg', mimeType: 'image/svg+xml', buffer: Buffer.from(source) })
  const dialog = page.getByRole('dialog', { name: 'Recortar foto de perfil' })
  await expect(page.getByRole('button', { name: 'Recortar e salvar' })).toBeEnabled()
  const box = (await dialog.boundingBox())!
  expect(box.x).toBeGreaterThanOrEqual(0)
  expect(box.x + box.width).toBeLessThanOrEqual(390)
  await page.screenshot({ path: 'test-results/profile-crop-mobile.png' })
  await dialog.press('Escape')
  await expect(dialog).toHaveCount(0)
  expect(uploaded).toBe(false)
  await expect(page.locator('.profile-photo')).toHaveAttribute('src', user.photo)
})
