import { expect, test } from '@playwright/test'
const headers = { 'access-control-allow-origin': 'http://127.0.0.1:4173', 'access-control-allow-credentials': 'true' }

test('history shows author avatars and the legacy fallback for missing or broken photos', async ({ page }) => {
  await page.route('http://127.0.0.1:8080/**', route => {
    const data = route.request().url().includes('/auth/session')
      ? { user: { ID: 1, NOME: 'Editor', APELIDO: 'Editor', EMAIL: 'editor@example.test', NIVEL: 1 }, csrf_token: 'test' }
      : { title: 'Especialidade de Alívio da Fome', base_version: 'v3', next_cursor: null, revisions: [
          { id: 3, author: 'MDAWiki', author_photo: '/assets/logo_mdawiki_initials.svg', source: 'Três', created_at: '2026-11-19 11:00:00' },
          { id: 2, author: 'gfavila', author_photo: null, source: 'Dois', created_at: '2026-08-31 14:43:58' },
          { id: 1, author: null, author_photo: '/broken-avatar.png', source: 'Um', created_at: '2015-07-24 16:37:23' },
        ] }
    return route.fulfill({ headers, json: { data } })
  })
  await page.goto('/Teste/historico/')
  const avatars = page.locator('tbody .history-author-avatar')
  await expect(avatars).toHaveCount(3)
  await expect(avatars.nth(0)).toHaveAttribute('src', '/assets/logo_mdawiki_initials.svg')
  await expect(avatars.nth(1)).toHaveAttribute('src', '/assets/user.png')
  await expect(avatars.nth(2)).toHaveAttribute('src', '/assets/user.png')
  await expect.poll(() => avatars.evaluateAll(images => images.every(image => (image as HTMLImageElement).naturalWidth > 0))).toBe(true)
  await expect(avatars.first()).toHaveCSS('width', '32px')
  await expect(avatars.first()).toHaveCSS('height', '32px')
  await expect(page.locator('.history-author').nth(2)).toContainText('Usuário')
  await page.screenshot({ path: 'test-results/history-author-avatars.png' })
})
