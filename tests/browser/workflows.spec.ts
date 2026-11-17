import { expect, test, type Page } from '@playwright/test'
const headers = { 'access-control-allow-origin': 'http://127.0.0.1:4173', 'access-control-allow-credentials': 'true' }
const user = { ID: 1, NOME: 'Editor', APELIDO: 'Editor', EMAIL: 'editor@example.test', NIVEL: 1 }
async function mock(page: Page, handle: (path: string, method: string, data: any) => any, authenticated = false) {
  await page.route('http://127.0.0.1:8080/**', async route => {
    const request = route.request(), path = new URL(request.url()).pathname.replace('/v1', ''), method = request.method()
    if (method === 'OPTIONS') return route.fulfill({ headers: { ...headers, 'access-control-allow-methods': 'GET, POST, PUT, OPTIONS', 'access-control-allow-headers': 'Accept, Content-Type, X-CSRF-Token' }, status: 204 })
    if (path === '/auth/session') return route.fulfill({ headers, json: { data: { user: authenticated ? user : null, csrf_token: 'csrf-original' } } })
    if (method !== 'GET') expect(request.headers()['x-csrf-token']).toMatch(/^csrf-/)
    const result = await handle(path, method, request.postDataJSON())
    return route.fulfill({ headers, status: result?.status || 200, json: result?.error ? { error: result.error } : { data: result } })
  })
}

test('login opens the new editor and saves through API with rotated CSRF', async ({ page }) => {
  let saved: any
  await mock(page, (path, method, data) => {
    if (path === '/auth/login') { expect(data.email).toBe('editor@example.test'); return { user, csrf_token: 'csrf-rotated' } }
    if (path.endsWith('/editor')) return { title: 'Teste', source: '# Original', base_version: 'v1', exists: true }
    if (path === '/pages/Teste' && method === 'PUT') { saved = data; return { slug: 'Teste', revision: 2 } }
    if (path === '/view/Teste') return { id: 'teste', slug: 'Teste', title: 'Teste', html: '<p>Conteúdo salvo.</p>', description: 'Teste', canonical_url: 'https://mda.wiki.br/Teste/' }
    return {}
  })
  await page.goto('/page%40Acesso/?redirect=%2FTeste%2Feditar')
  await page.getByLabel('E-mail', { exact: true }).fill('editor@example.test')
  await page.getByLabel('Senha', { exact: true }).fill('valid-password')
  await page.getByRole('button', { name: 'Entrar', exact: true }).click()
  await expect(page.getByLabel('Código wiki', { exact: true })).toHaveValue('# Original')
  await page.getByLabel('Código wiki', { exact: true }).fill('# Alterado')
  await page.getByRole('button', { name: 'Salvar página', exact: true }).click()
  await expect(page.locator('.wiki-content')).toContainText('Conteúdo salvo.')
  expect(saved).toEqual({ title: 'Teste', source: '# Alterado', base_version: 'v1' })
  await expect(page).toHaveURL(/\/Teste\/$/)
})

test('conflict keeps the edited source visible and never overwrites silently', async ({ page }) => {
  await mock(page, path => path.endsWith('/editor') ? { title: 'Teste', source: 'Original', base_version: 'v1' } : { status: 409, error: { code: 'edit_conflict', message: 'Outra edição foi salva.' } }, true)
  await page.goto('/Teste/editar')
  await page.getByLabel('Código wiki', { exact: true }).fill('Minha edição')
  await page.getByRole('button', { name: 'Salvar página', exact: true }).click()
  await expect(page.getByRole('alert')).toContainText('Outra edição')
  await expect(page.getByLabel('Código wiki', { exact: true })).toHaveValue('Minha edição')
})

test('history compares and restores an actual selected revision through API', async ({ page }) => {
  let restored = false
  await mock(page, (path, method, data) => {
    if (path.endsWith('/restore')) { expect(data.revision).toBe(1); restored = true; return { revision: 3 } }
    return { title: 'Teste', base_version: 'v2', next_cursor: null, revisions: [ ...(restored ? [{ id: 3, source: 'Antigo\n', author: 'Editor', created_at: '2026-11-17 13:00:00' }] : []), { id: 2, source: 'Novo\n', author: 'Editor', created_at: '2026-11-17 12:00:00' }, { id: 1, source: 'Antigo\n', author: 'Editor', created_at: '2026-11-17 11:00:00' }] }
  }, true)
  await page.goto('/Teste/historico')
  await page.getByRole('button', { name: 'Comparar versões' }).click()
  await expect(page.locator('.revision-diff .added')).toContainText('Novo')
  await expect(page.locator('.revision-diff .removed')).toContainText('Antigo')
  page.on('dialog', dialog => dialog.accept())
  await page.locator('tbody tr').last().getByRole('button', { name: 'Restaurar' }).click()
  await expect(page.locator('tbody tr')).toHaveCount(3)
  expect(restored).toBe(true)
})

test('top search stays inside SPA and uses the new search endpoint', async ({ page }) => {
  let queried = false
  await mock(page, path => { if (path === '/search') { queried = true; return { items: [{ id: 'flor', slug: 'Floricultura', title: 'Floricultura', description: 'Artigo encontrado.' }], next_offset: null } }; return { items: [], next_offset: null } })
  await page.goto('/page%40Busca/')
  await page.getByLabel('Localizar na MDAWiki').fill('Floricultura')
  await page.getByLabel('Localizar na MDAWiki').press('Enter')
  await expect(page.locator('.directory-list')).toContainText('Artigo encontrado.')
  await expect(page.getByRole('link', { name: 'Floricultura', exact: true })).toHaveAttribute('href', '/Floricultura/')
  expect(queried).toBe(true)
  expect(new URL(page.url()).hostname).toBe('127.0.0.1')
})

test('anonymous editor offers login without returning or showing source', async ({ page }) => {
  await mock(page, () => ({ status: 401, error: { code: 'login_required', message: 'Entre na sua conta para continuar.' } }))
  await page.goto('/Teste/editar')
  await expect(page.getByRole('link', { name: 'Fazer login para continuar' })).toBeVisible()
  await expect(page.locator('.wiki-editor')).toHaveCount(0)
})

test('profile saves account fields through API and administration denies ordinary users', async ({ page }) => {
  let changed = false
  await mock(page, (path, method, data) => {
    if (path === '/profile') { if (method === 'PUT') { expect(data.NOME).toBe('Nome novo'); changed = true }; return { ...user, ENVIA_EMAIL: 'S', TIPO_CLUBE: 'Desbravadores' } }
    return { status: 403, error: { code: 'permission_denied', message: 'Acesso restrito a administradores.' } }
  }, true)
  await page.goto('/page%40Perfil/')
  await page.getByLabel('Nome', { exact: true }).fill('Nome novo')
  await page.getByRole('button', { name: 'Salvar', exact: true }).click()
  await expect(page.getByRole('status')).toContainText('Dados salvos.')
  expect(changed).toBe(true)
  await page.goto('/page%40Usuarios/')
  await expect(page.getByRole('alert')).toContainText('Acesso restrito')
  await expect(page.locator('tbody tr')).toHaveCount(0)
})
