import { expect, test, type Page } from '@playwright/test'
const headers = { 'access-control-allow-origin': 'http://127.0.0.1:4173', 'access-control-allow-credentials': 'true' }
const user = { ID: 1, NOME: 'Editor', APELIDO: 'Editor', EMAIL: 'editor@example.test', NIVEL: 1 }
test('static login has noindex and never embeds a session or private source', async ({ request }) => {
  const response = await request.get('/page%40Acesso/')
  expect(response.status()).toBe(200)
  const html = await response.text()
  expect(html).toContain('Entrar na MDAWiki')
  expect(html).toContain('noindex,follow')
  expect(html).not.toContain('csrf_token')
  expect(html).not.toContain('SENHA')
})
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
  await page.getByRole('button', { name: 'Salvar', exact: true }).click()
  await expect(page.locator('.wiki-content')).toContainText('Conteúdo salvo.')
  expect(saved).toEqual({ title: 'Teste', source: '# Alterado', base_version: 'v1' })
  await expect(page).toHaveURL(/\/Teste\/$/)
})

test('conflict keeps the edited source visible and never overwrites silently', async ({ page }) => {
  await mock(page, path => path.endsWith('/editor') ? { title: 'Teste', source: 'Original', base_version: 'v1' } : { status: 409, error: { code: 'edit_conflict', message: 'Outra edição foi salva.' } }, true)
  await page.goto('/Teste/editar')
  await page.getByLabel('Código wiki', { exact: true }).fill('Minha edição')
  await page.getByRole('button', { name: 'Salvar', exact: true }).click()
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
  const toolbar = page.getByRole('navigation', { name: 'Ações da página' })
  await expect(toolbar.getByRole('link', { name: 'Voltar', exact: true })).toBeVisible()
  await expect(toolbar.getByRole('button')).toHaveCount(0)
  await page.getByRole('button', { name: 'Comparar versões' }).click()
  await expect(page.getByRole('dialog', { name: 'Comparar versões' })).toBeVisible()
  await page.screenshot({ path: 'test-results/history-compare-modal.png' })
  await expect(page.locator('.revision-diff .added')).toContainText('Novo')
  await expect(page.locator('.revision-diff .removed')).toContainText('Antigo')
  await page.getByRole('dialog', { name: 'Comparar versões' }).getByRole('button', { name: 'Fechar', exact: true }).last().click()
  await page.locator('tbody tr').last().getByRole('button', { name: 'Visualizar', exact: true }).click()
  await expect(page.getByRole('dialog', { name: 'Revisão 1' })).toContainText('Antigo')
  await page.getByRole('dialog', { name: 'Revisão 1' }).press('Escape')
  await expect(page.getByRole('dialog')).toHaveCount(0)
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


test('editor toolbar previews unsaved source in a modal without saving and fits mobile', async ({ page }) => {
  let saved = false, previewed = ''
  await mock(page, (path, method, data) => {
    if (path.endsWith('/editor')) return { title: 'Teste', source: 'Original', base_version: 'v1' }
    if (path.endsWith('/preview')) { previewed = data.source; return { html: '<p><strong>Minha edição</strong></p><script>window.bad = true</script>' } }
    if (method === 'PUT') saved = true
    return {}
  }, true)
  await page.goto('/Teste/editar')
  const editor = page.getByLabel('Código wiki', { exact: true })
  await editor.fill('Minha edição')
  const toolbar = page.getByRole('navigation', { name: 'Ações da página' })
  await expect(toolbar.getByRole('link', { name: 'Voltar', exact: true })).toBeVisible()
  await expect(page.getByRole('link', { name: 'Voltar', exact: true })).toHaveCount(1)
  await expect(page.locator('.legacy-edit-actions').getByRole('button')).toHaveCount(1)
  await toolbar.getByRole('button', { name: 'Visualizar página', exact: true }).click()
  const modal = page.getByRole('dialog', { name: 'Visualizar página', exact: true })
  await expect(modal.locator('.wiki-content strong')).toHaveText('Minha edição')
  expect(previewed).toBe('Minha edição')
  expect(saved).toBe(false)
  expect(await page.evaluate(() => (window as any).bad)).toBeUndefined()
  await page.screenshot({ path: 'test-results/editor-preview-desktop.png' })
  await modal.press('Escape')
  await expect(editor).toHaveValue('Minha edição')
  await expect(toolbar.getByRole('button', { name: 'Visualizar página' })).toBeFocused()
  await page.setViewportSize({ width: 390, height: 844 })
  await toolbar.getByRole('button', { name: 'Visualizar página' }).click()
  await expect(modal.locator('.wiki-content strong')).toBeVisible()
  const box = (await modal.boundingBox())!
  expect(box.x).toBeGreaterThanOrEqual(0)
  expect(box.x + box.width).toBeLessThanOrEqual(390)
  await page.screenshot({ path: 'test-results/editor-preview-mobile.png' })
  await modal.getByRole('button', { name: 'Fechar', exact: true }).last().click()
  await expect(editor).toHaveValue('Minha edição')
  expect(saved).toBe(false)
})

test('preview failure stays in modal and retry preserves the unsaved editor', async ({ page }) => {
  let fail = true
  await mock(page, path => path.endsWith('/editor') ? { title: 'Teste', source: 'Original', base_version: 'v1' } : fail ? { status: 503, error: { message: 'Prévia indisponível.' } } : { html: '<p>Recuperada</p>' }, true)
  await page.goto('/Teste/editar')
  const editor = page.getByLabel('Código wiki', { exact: true })
  await editor.fill('Não salvar')
  await page.getByRole('button', { name: 'Visualizar página' }).click()
  const modal = page.getByRole('dialog', { name: 'Visualizar página' })
  await expect(modal.getByRole('alert')).toContainText('Prévia indisponível')
  fail = false
  await modal.getByRole('button', { name: 'Tentar novamente' }).click()
  await expect(modal.locator('.wiki-content')).toContainText('Recuperada')
  await modal.press('Escape')
  await expect(editor).toHaveValue('Não salvar')
})

test('history modal switches between merged and side-by-side differences without losing revisions', async ({ page }) => {
  await mock(page, () => ({ title: 'Teste', base_version: 'v2', next_cursor: null, revisions: [
    { id: 2, source: 'Comum\nNovo\nExtra\nFinal\n', author: 'Editor', created_at: '2026-11-19' },
    { id: 1, source: 'Comum\nAntigo\nFinal\n', author: 'Editor', created_at: '2026-11-18' },
  ] }), true)
  await page.goto('/Teste/historico')
  await page.getByRole('button', { name: 'Comparar versões' }).click()
  const modal = page.getByRole('dialog', { name: 'Comparar versões' })
  const mode = modal.getByLabel('Modo de visualização')
  await expect(mode).toHaveValue('merged')
  await expect(modal.locator('.revision-diff .removed')).toHaveText('Antigo\n')
  await mode.selectOption('split')
  const table = modal.getByRole('table')
  await expect(table.getByRole('columnheader').first()).toHaveText('Anterior — Revisão 1')
  await expect(table.getByRole('columnheader').last()).toHaveText('Nova — Revisão 2')
  await expect(table.locator('td.removed')).toHaveText('Antigo\n')
  await expect(table.locator('td.added')).toHaveText(['Novo\n', 'Extra\n'])
  await expect(table.locator('tbody tr')).toHaveCount(4)
  await expect(table.locator('tbody tr').nth(2).locator('td').first()).toHaveClass(/empty-diff-cell/)
  await page.screenshot({ path: 'test-results/history-split-desktop.png' })
  await page.setViewportSize({ width: 390, height: 844 })
  await expect.poll(() => page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true)
  await expect(table).toBeVisible()
  await page.screenshot({ path: 'test-results/history-split-mobile.png' })
  await mode.selectOption('merged')
  await expect(modal.locator('.revision-diff .added')).toHaveText('Novo\nExtra\n')
  await modal.press('Escape')
  await expect(page.getByLabel('Versão anterior 1')).toBeChecked()
  await expect(page.getByLabel('Versão nova 2')).toBeChecked()
})
