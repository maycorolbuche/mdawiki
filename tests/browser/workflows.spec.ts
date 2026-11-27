import { expect, test, type Page } from '@playwright/test'
const headers = { 'access-control-allow-origin': 'http://127.0.0.1:4173', 'access-control-allow-credentials': 'true' }
const user = { ID: 1, NOME: 'Editor', APELIDO: 'Editor', EMAIL: 'editor@example.test', NIVEL: 1 }
test('static login has noindex and never embeds a session or private source', async ({ request }) => {
  const response = await request.get('/p/login/')
  expect(response.status()).toBe(200)
  const html = await response.text()
  expect(html).toContain('Entrar na MDAWiki')
  expect(html).toContain('noindex,follow')
  expect(html).not.toContain('csrf_token')
  expect(html).not.toContain('SENHA')
})
async function mock(page: Page, handle: (path: string, method: string, data: any) => any, authenticated = false, account = user) {
  await page.route('http://127.0.0.1:8080/**', async route => {
    const request = route.request(), path = new URL(request.url()).pathname.replace('/v1', ''), method = request.method()
    if (method === 'OPTIONS') return route.fulfill({ headers: { ...headers, 'access-control-allow-methods': 'GET, POST, PUT, OPTIONS', 'access-control-allow-headers': 'Accept, Content-Type, X-CSRF-Token' }, status: 204 })
    if (path === '/auth/session') return route.fulfill({ headers, json: { data: { user: authenticated ? account : null, csrf_token: 'csrf-original' } } })
    if (method !== 'GET') expect(request.headers()['x-csrf-token']).toMatch(/^csrf-/)
    const result = await handle(path, method, request.postDataJSON())
    return route.fulfill({ headers, status: result?.status || 200, json: result?.error ? { error: result.error } : { data: result } })
  })
}

test('registration opens activation with the signup email and an empty code', async ({ page }) => {
  const email = 'cadastro@example.test'
  await mock(page, (path, method, data) => {
    if (path === '/auth/register') {
      expect(method).toBe('POST')
      expect(data.email).toBe(email)
      return { message: 'Confira seu e-mail.' }
    }
    return {}
  })
  await page.goto('/p/cadastro/')
  await page.getByLabel('Nome', { exact: true }).fill('Pessoa Teste')
  await page.getByLabel('E-mail', { exact: true }).fill(email)
  await page.getByLabel('Senha', { exact: true }).fill('abc123')
  await page.getByLabel('Confirme a senha').fill('abc123')
  await page.getByRole('button', { name: 'Criar conta', exact: true }).click()
  await expect(page).toHaveURL(/\/p\/ativar-conta\?email=/)
  await expect(page.getByLabel('E-mail do cadastro')).toHaveValue(email)
  const code = page.getByLabel('Código de ativação')
  await expect(code).toHaveValue('')
  await expect(code).toBeFocused()
})

test('activation shows success on login and the first login opens the profile', async ({ page }) => {
  await mock(page, path => {
    if (path === '/auth/login') return { user, csrf_token: 'csrf-rotated', first_login: true }
    if (path === '/profile') return user
    return {}
  })
  await page.goto('/p/ativar-conta/?email=editor%40example.test&code=001234')
  await page.getByRole('button', { name: 'Ativar conta', exact: true }).click()
  await expect(page).toHaveURL(/\/p\/login$/)
  await expect(page.getByRole('status')).toHaveText('Usuário registrado com sucesso! Faça login para continuar.')
  await page.getByLabel('E-mail', { exact: true }).fill(user.EMAIL)
  await page.getByLabel('Senha', { exact: true }).fill('abc123')
  await page.getByRole('button', { name: 'Entrar', exact: true }).click()
  await expect(page).toHaveURL(/\/p\/perfil$/)
  await expect(page.getByLabel('Nome', { exact: true })).toHaveValue(user.NOME)
})

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
    if (path.endsWith('/restore')) { expect(data).toEqual({ revision: 1, base_version: 'v2' }); restored = true; return { slug: 'Teste', revision: 3 } }
    if (path === '/view/Teste') return { id: 'teste', slug: 'Teste', title: 'Teste', html: restored ? '<p>Antigo</p>' : '<p>Novo</p>' }
    return { title: 'Teste', base_version: 'v2', next_cursor: null, revisions: [ { id: 2, source: 'Novo\n', author: 'Editor', created_at: '2026-11-17 12:00:00' }, { id: 1, source: 'Antigo\n', author: 'Editor', created_at: '2026-11-17 11:00:00' }] }
  }, true)
  await page.goto('/Teste/historico')
  const toolbar = page.getByRole('navigation', { name: 'Ações da página' })
  await expect(toolbar.getByRole('link', { name: 'Voltar', exact: true })).toBeVisible()
  await expect(toolbar.getByRole('button')).toHaveCount(0)
  await page.getByRole('button', { name: 'Comparar versões' }).click()
  await expect(page.getByRole('dialog', { name: 'Comparar versões' })).toBeVisible()
  await page.screenshot({ path: 'test-results/history-compare-modal.png' })
  await expect(page.getByLabel('Modo de visualização')).toHaveValue('split')
  await expect(page.locator('td.added')).toContainText('Novo')
  await expect(page.locator('td.removed')).toContainText('Antigo')
  await page.getByRole('dialog', { name: 'Comparar versões' }).getByRole('button', { name: 'Fechar', exact: true }).last().click()
  await page.locator('tbody tr').last().getByRole('button', { name: 'Visualizar', exact: true }).click()
  await expect(page.getByRole('dialog', { name: 'Revisão 1' })).toContainText('Antigo')
  await page.getByRole('dialog', { name: 'Revisão 1' }).press('Escape')
  await expect(page.getByRole('dialog')).toHaveCount(0)
  page.on('dialog', dialog => dialog.accept())
  await page.locator('tbody tr').last().getByRole('button', { name: 'Restaurar' }).click()
  await expect(page).toHaveURL(/\/Teste\/$/)
  await expect(page.locator('.wiki-content')).toContainText('Antigo')
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
    { id: 2, source: 'Comum\r\nNovo\r\nExtra\r\nFinal\r\n', categories: [{ key: 'igual', title: 'Igual' }, { key: 'nova', title: 'Nova categoria' }], author: 'Editor', created_at: '2026-11-19' },
    { id: 1, source: 'Comum\nAntigo\nFinal\n', categories: [{ key: 'igual', title: 'Igual' }, { key: 'antiga', title: 'Categoria antiga' }], author: 'Editor', created_at: '2026-11-18' },
  ] }), true)
  await page.goto('/Teste/historico')
  await page.getByRole('button', { name: 'Comparar versões' }).click()
  const modal = page.getByRole('dialog', { name: 'Comparar versões' })
  const mode = modal.getByLabel('Modo de visualização')
  await expect(mode).toHaveValue('split')
  const categories = modal.getByRole('region', { name: 'Categorias' })
  await expect(categories.locator('.removed')).toHaveText('Categoria antiga · Removida')
  await expect(categories.locator('.added')).toHaveText('Nova categoria · Adicionada')
  await expect(categories.getByText('Igual', { exact: true })).toHaveCount(2)
  const table = modal.getByRole('table')
  await expect(table.getByRole('columnheader').first()).toContainText('Anterior — Revisão 1')
  await expect(table.getByRole('columnheader').last()).toContainText('Nova — Revisão 2')
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
  await expect(categories).toBeVisible()
  await expect(modal.locator('.revision-diff .removed')).toHaveText('Antigo\n')
  await expect(modal.locator('.revision-diff .added')).toHaveText('Novo\nExtra\n')
  await modal.press('Escape')
  await expect(page.getByLabel('Versão anterior 1')).toBeChecked()
  await expect(page.getByLabel('Versão nova 2')).toBeChecked()
})

test('revision modals show the matching author, avatar and timestamp in both comparison modes', async ({ page }) => {
  const old = { id: 1, source: 'Antigo', author: 'Autora anterior', author_photo: '/assets/ico_classe_dbv.svg', created_at: '2026-11-18 09:15:22' }
  const current = { id: 2, source: 'Novo', author: 'Autor novo', author_photo: '/assets/user.png', created_at: '2026-11-19 18:30:45' }
  await mock(page, () => ({ title: 'Teste', base_version: 'v2', next_cursor: null, revisions: [current, old] }), true)
  await page.goto('/Teste/historico')
  await page.locator('tbody tr').last().getByRole('button', { name: 'Visualizar', exact: true }).click()
  const revision = page.getByRole('dialog', { name: 'Revisão 1', exact: true })
  await expect(revision.locator('.revision-metadata')).toContainText(old.author)
  await expect(revision.locator('.revision-metadata')).toContainText(old.created_at)
  await expect(revision.locator('.history-author-avatar')).toHaveAttribute('src', old.author_photo)
  await revision.press('Escape')
  await page.getByRole('button', { name: 'Comparar versões' }).click()
  const modal = page.getByRole('dialog', { name: 'Comparar versões' })
  await modal.getByLabel('Modo de visualização').selectOption('merged')
  const earlier = modal.getByRole('region', { name: 'Dados da revisão anterior' })
  const later = modal.getByRole('region', { name: 'Dados da revisão nova' })
  await expect(earlier).toContainText(old.author)
  await expect(earlier).toContainText(old.created_at)
  await expect(earlier.locator('img')).toHaveAttribute('src', old.author_photo)
  await expect(later).toContainText(current.author)
  await expect(later).toContainText(current.created_at)
  await modal.getByLabel('Modo de visualização').selectOption('split')
  const columns = modal.getByRole('columnheader')
  await expect(columns.first()).toContainText(old.author)
  await expect(columns.first()).toContainText(old.created_at)
  await expect(columns.first().locator('img')).toHaveAttribute('src', old.author_photo)
  await expect(columns.last()).toContainText(current.author)
  await expect(columns.last()).toContainText(current.created_at)
  await expect(columns.last().locator('img')).toHaveAttribute('src', current.author_photo)
  await page.screenshot({ path: 'test-results/history-metadata-split.png' })
  await page.setViewportSize({ width: 390, height: 844 })
  await expect.poll(() => page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true)
})

test('unchanged save stays in editor and explains that no revision was created', async ({ page }) => {
  await mock(page, (path, method) => path.endsWith('/editor') ? { title: 'Teste', source: 'Original', base_version: 'v1' } : { unchanged: true, slug: 'Teste', revision: 1, base_version: 'v1' }, true)
  await page.goto('/Teste/editar')
  await expect(page.getByLabel('Código wiki', { exact: true })).toHaveValue('Original')
  await page.getByRole('button', { name: 'Salvar', exact: true }).click()
  await expect(page.getByRole('status')).toContainText('Não foram detectadas alterações na página. Nenhuma revisão foi criada.')
  await expect(page).toHaveURL(/\/Teste\/editar$/)
  await expect(page.getByLabel('Código wiki', { exact: true })).toHaveValue('Original')
})

test('history shows source character counts between author and date including Unicode and empty revisions', async ({ page }) => {
  await mock(page, () => ({ title: 'Teste', base_version: 'v2', next_cursor: null, revisions: [
    { id: 2, source: 'Ação🙂', author: 'Editor', created_at: '2026-11-19 12:00:00' },
    { id: 1, source: '', author: 'Editor', created_at: '2026-11-18 12:00:00' },
  ] }), true)
  await page.goto('/Teste/historico')
  const table = page.locator('.article-body table')
  await expect(table.getByRole('columnheader')).toHaveText(['Comparar', 'Revisão', 'Autor', 'Caracteres', 'Data', 'Ações'])
  await expect(table.locator('.history-character-count')).toHaveText(['5', '0'])
})

test('delete confirmation saves blank source only after confirming and cancellation retains draft', async ({ page }) => {
  const writes: any[] = []
  await mock(page, (path, method, data) => {
    if (path.endsWith('/editor')) return { title: 'Teste', source: 'Original', base_version: 'v1', exists: true }
    if (method === 'PUT') { writes.push(data); return { slug: 'Teste', revision: 2 } }
    return { id: 'teste', slug: 'Teste', title: 'Teste', html: '', description: '', canonical_url: '' }
  }, true)
  await page.goto('/Teste/editar')
  const editor = page.getByLabel('Código wiki', { exact: true })
  await editor.fill('Rascunho')
  const remove = page.getByRole('navigation', { name: 'Ações da página' }).getByRole('button', { name: 'Excluir página' })
  await remove.click()
  const dialog = page.getByRole('dialog', { name: 'Excluir página' })
  await expect(dialog).toContainText('Deseja excluir o conteúdo de Teste?')
  await expect(dialog.getByRole('button', { name: 'Cancelar', exact: true })).toBeVisible()
  await expect(dialog.getByRole('button', { name: 'Excluir página', exact: true })).toBeVisible()
  await dialog.getByRole('button', { name: 'Cancelar' }).click()
  await expect(editor).toHaveValue('Rascunho')
  expect(writes).toHaveLength(0)
  await remove.click()
  await dialog.press('Escape')
  await expect(editor).toHaveValue('Rascunho')
  expect(writes).toHaveLength(0)
  await remove.click()
  await dialog.getByRole('button', { name: 'Excluir página', exact: true }).click()
  await expect(page).toHaveURL(/\/Teste\/$/)
  expect(writes).toEqual([{ title: 'Teste', source: '', base_version: 'v1' }])
})

test('delete conflict leaves the editor draft and confirmation available for cancellation', async ({ page }) => {
  await mock(page, path => path.endsWith('/editor') ? { title: 'Teste', source: 'Original', base_version: 'v1', exists: true } : { status: 409, error: { code: 'edit_conflict', message: 'Outra edição foi salva.' } }, true)
  await page.goto('/Teste/editar')
  const editor = page.getByLabel('Código wiki', { exact: true })
  await editor.fill('Preservar rascunho')
  await page.getByRole('button', { name: 'Excluir página', exact: true }).click()
  const dialog = page.getByRole('dialog', { name: 'Excluir página' })
  await dialog.getByRole('button', { name: 'Excluir página', exact: true }).click()
  await expect(dialog.getByRole('alert')).toContainText('Outra edição')
  await dialog.getByRole('button', { name: 'Cancelar' }).click()
  await expect(editor).toHaveValue('Preservar rascunho')
})


test('Atualizar reprocessa o artigo e recarrega seus vínculos', async ({ page }) => {
  let rebuilds = 0
  await mock(page, (path, method) => {
    if (path === '/pages/Teste/rebuild' && method === 'POST') {
      rebuilds++
      return { revision: 2 }
    }
    return {
      id: '12', slug: 'Teste', title: 'Teste',
      html: rebuilds ? '<p>Conteúdo reprocessado.</p>' : '<p>Conteúdo original.</p>',
      categories: rebuilds ? [{ id: '16', slug: 'Categoria', title: 'Categoria regenerada' }] : [],
      canonical_url: 'https://mda.wiki.br/Teste/',
    }
  }, true, { ...user, NIVEL: 5 })
  await page.goto('/Teste/')
  await expect(page.getByText('Editor', { exact: true })).toBeVisible()
  await expect(page.getByText('Conteúdo original.')).toBeVisible()
  await page.getByRole('button', { name: 'Atualizar', exact: true }).click()
  await expect(page.getByText('Conteúdo reprocessado.')).toBeVisible()
  await expect(page.getByRole('link', { name: 'Categoria regenerada' })).toBeVisible()
  expect(rebuilds).toBe(1)
})

test('a página inexistente oferece dois botões de criação e abre o formulário vazio', async ({ page }) => {
  await mock(page, (path) => {
    if (path === '/pages/Esd/editor') {
      return { slug: 'Esd', title: 'Esd', source: '', base_version: '', exists: false, format: 'wiki', media: false }
    }
    return { status: 404, error: { code: 'not_found', message: 'Página não encontrada.' } }
  }, true)
  await page.goto('/Esd/')
  await expect(page.getByRole('heading', { name: 'Página não encontrada' })).toBeVisible()
  await expect(page.getByRole('link', { name: 'Criar Página', exact: true })).toHaveCount(2)
  await page.locator('.article-body').getByRole('link', { name: 'Criar Página' }).click()
  await expect(page).toHaveURL(/\/Esd\/editar$/)
  await expect(page.getByRole('textbox', { name: 'Título da página' })).toHaveValue('Esd')
})

test('o endereço /i/ mostra upload para imagem que ainda não existe', async ({ page }) => {
  await mock(page, path => path === '/images/Esd'
    ? { status: 404, error: { code: 'image_not_found', message: 'Imagem não encontrada.' } }
    : { status: 404, error: { code: 'not_found', message: 'Página não encontrada.' } }, true)
  await page.goto('/i/Esd')
  await expect(page.getByRole('heading', { name: 'Imagem não encontrada' })).toBeVisible()
  await expect(page.getByRole('button', { name: 'Enviar imagem' })).toBeVisible()
})

test('o histórico abre o perfil público do autor', async ({ page }) => {
  const id = 'f'.repeat(32)
  await mock(page, path => {
    if (path === '/pages/Teste/history') return {
      title: 'Teste', base_version: 'v1', next_cursor: null,
      revisions: [{ id: 1, source: 'Texto', author: 'Autora Wiki', author_profile_id: id }],
    }
    if (path === '/public-users/' + id) return {
      name: 'Autora Wiki', city: 'Campinas', state_code: 'SP', birth_date: '1990-01-02',
      club: 'Clube de teste', photo: '/assets/ico_classe_dbv.svg',
    }
    return {}
  }, true)
  await page.goto('/Teste/historico')
  await page.getByRole('link', { name: 'Autora Wiki' }).click()
  await expect(page).toHaveURL(new RegExp('/u/' + id + '$'))
  await expect(page.getByRole('heading', { name: 'Autora Wiki' })).toBeVisible()
  await expect(page.getByText('Campinas — SP')).toBeVisible()
  await expect(page.getByText('02/01/1990')).toBeVisible()
})
