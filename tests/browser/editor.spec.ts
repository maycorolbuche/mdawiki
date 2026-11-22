import { expect, test, type Page } from '@playwright/test'
const headers = { 'access-control-allow-origin': 'http://127.0.0.1:4173', 'access-control-allow-credentials': 'true' }
async function setup(page: Page) {
  const writes: { path: string; body: any }[] = []
  await page.route('http://127.0.0.1:8080/**', async route => {
    const request = route.request(), path = new URL(request.url()).pathname
    if (request.method() === 'OPTIONS') return route.fulfill({ status: 204, headers: { ...headers, 'access-control-allow-methods': 'GET, POST, PUT, OPTIONS', 'access-control-allow-headers': 'Accept, Content-Type, X-CSRF-Token' } })
    let data: any = {}
    if (path.endsWith('/auth/session')) data = { user: { ID: 1, NOME: 'Editor', NIVEL: 1 }, csrf_token: 'csrf-editor' }
    else if (path.endsWith('/editor')) data = { title: 'Especialidade de Modelagem e Fabricação de Sabão', source: 'Texto original', media: false, base_version: 'version-1', exists: true }
    else if (path.endsWith('/visits')) data = { counted: true }
    else if (request.method() !== 'GET') {
      writes.push({ path, body: request.postDataJSON() })
      data = path.endsWith('/media') ? { url: 'https://api.mda.wiki.br/media/wiki/nova.png' } : { slug: 'Teste', revision: 2 }
    } else data = { id: 'teste', slug: decodeURIComponent(path.split('/').at(-1)!), title: 'Teste', html: '<p>Artigo salvo.</p>', description: '', canonical_url: 'https://mda.wiki.br/Teste/' }
    return route.fulfill({ headers, json: { data } })
  })
  return writes
}

test('legacy markItUp toolbar wraps selection, keeps shortcuts and saves its actual value', async ({ page }) => {
  const writes = await setup(page)
  const errors: string[] = []
  page.on('pageerror', error => errors.push(error.message))
  await page.setViewportSize({ width: 1535, height: 1000 })
  await page.goto('/Teste/editar')
  await expect(page.locator('.markItUpHeader')).toHaveCount(1)
  await expect(page.locator('.legacy-edit-title')).toContainText('Especialidade de Modelagem e Fabricação de Sabão')
  await expect(page.locator('.legacy-edit-badge')).toHaveText('Editar Página')
  await expect(page.getByLabel('Título', { exact: true })).toHaveCount(0)
  const editor = page.getByLabel('Código wiki', { exact: true })
  await editor.fill('Texto')
  await editor.evaluate((el: HTMLTextAreaElement) => el.setSelectionRange(0, 5))
  await page.locator('.m_negrito > a').click()
  await expect(editor).toHaveValue("'''Texto'''")
  await editor.fill('Itálico')
  await editor.evaluate((el: HTMLTextAreaElement) => { el.focus(); el.select() })
  await editor.press('Control+i')
  await expect(editor).toHaveValue("''Itálico''")
  await page.screenshot({ path: 'test-results/editor-desktop.png', fullPage: true })
  await page.getByRole('button', { name: 'Salvar', exact: true }).click()
  await expect(page).toHaveURL(/\/Teste\/$/)
  expect(writes[0]?.body).toEqual({ title: 'Especialidade de Modelagem e Fabricação de Sabão', source: "''Itálico''", base_version: 'version-1' })
  expect(errors).toEqual([])
})

test('legacy dropdowns and prompts insert original wiki syntax and cancellation preserves source', async ({ page }) => {
  await setup(page)
  await page.goto('/Teste/editar')
  const editor = page.getByLabel('Código wiki', { exact: true })
  await expect(page.locator('.markItUpHeader')).toHaveCount(1)
  await editor.fill('Texto')
  await editor.evaluate((el: HTMLTextAreaElement) => { el.focus(); el.select() })
  await page.locator('.m_font_size').first().hover()
  await page.getByRole('link', { name: 'Tamanho 3', exact: true }).click()
  await expect(editor).toHaveValue('{{TamanhoTexto:3}}Texto{{/TamanhoTexto}}')
  await editor.fill('')
  page.once('dialog', dialog => dialog.accept('Artes, Habilidades'))
  await page.locator('.m_categorias > a').click()
  await expect(editor).toHaveValue('{{Categorias:Artes, Habilidades}}')
  page.once('dialog', dialog => dialog.dismiss())
  await page.locator('.m_imagem > a').click()
  await expect(editor).toHaveValue('{{Categorias:Artes, Habilidades}}')
  await editor.fill('')
  await page.locator('.m_tabela').first().hover()
  await page.getByRole('link', { name: 'Tabela sem bordas', exact: true }).click()
  await expect(editor).toHaveValue('{|\n|!!-\n|Celula 1,1\n|Celula 2,1\n|!!-\n|Celula 2,1\n|Celula 2,2\n|}')
})

test('editor remounts once through SPA navigation and fits mobile viewport', async ({ page }) => {
  await setup(page)
  await page.setViewportSize({ width: 390, height: 844 })
  await page.goto('/Teste/editar')
  await expect(page.locator('.markItUpHeader')).toHaveCount(1)
  expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true)
  await page.locator('.m_objeto').first().hover()
  await expect(page.getByRole('link', { name: 'Especialidade Desbravadores', exact: true })).toBeVisible()
  expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true)
  await page.mouse.move(380, 70)
  await page.screenshot({ path: 'test-results/editor-mobile.png', fullPage: true })
  await page.getByRole('link', { name: 'Voltar', exact: true }).click()
  await page.goBack()
  await expect(page.locator('.markItUpHeader')).toHaveCount(1)
  await expect(page.getByLabel('Código wiki', { exact: true })).toHaveValue('Texto original')
})

test('a tela de imagem substitui o arquivo e mostra a versão anterior', async ({ page }) => {
  const original = 'http://127.0.0.1:4173/assets/ico_classe_dbv.svg'
  const updated = 'http://127.0.0.1:4173/assets/ico_classe_avt.svg'
  const payloads: unknown[] = []
  await page.route('http://127.0.0.1:8080/**', async route => {
    const request = route.request()
    const path = new URL(request.url()).pathname
    if (request.method() === 'OPTIONS') {
      return route.fulfill({ status: 204, headers: { ...headers, 'access-control-allow-methods': 'GET, PUT, OPTIONS', 'access-control-allow-headers': 'Accept, Content-Type, X-CSRF-Token' } })
    }
    if (path.endsWith('/auth/session')) {
      return route.fulfill({ headers, json: { data: { user: { ID: 1, NOME: 'Editor', NIVEL: 1 }, csrf_token: 'csrf-editor' } } })
    }
    if (request.method() === 'PUT') {
      expect(path).toBe('/v1/images/Teste')
      expect(request.headers()['x-csrf-token']).toBe('csrf-editor')
      payloads.push(request.postDataJSON())
    }
    const data = {
      slug: 'Teste', title: 'Teste', url: payloads.length ? updated : original,
      version: payloads.length ? '2' : '1', meta: { extension: 'svg', width: 80, height: 40, size_bytes: 170 },
      pages: [{ id: '10', slug: 'Artigo', title: 'Artigo' }],
      history: payloads.length ? [{ id: '1', url: original, created_at: '2026-11-22 12:00:00' }] : [],
    }
    return route.fulfill({ headers, json: { data } })
  })
  await page.goto('/imagem%40Teste/editar')
  await expect(page).toHaveURL(/\/i\/Teste$/)
  await expect(page.getByRole('button', { name: 'Substituir Página' })).toBeVisible()
  await expect(page.getByRole('heading', { name: 'Propriedades' })).toBeVisible()
  await expect(page.getByRole('link', { name: 'Artigo' })).toHaveAttribute('href', '/Artigo/')
  await page.locator('input[type=file]').setInputFiles({ name: 'nova.png', mimeType: 'image/png', buffer: Buffer.from('image') })
  await expect(page.getByRole('img', { name: 'Teste', exact: true })).toHaveAttribute('src', updated)
  expect(payloads).toEqual([{ base_version: '1', data: 'data:image/png;base64,' + Buffer.from('image').toString('base64') }])
  await page.getByRole('button', { name: 'Histórico' }).click()
  await expect(page).toHaveURL(/\/i\/Teste\/historico$/)
  await expect(page.getByRole('img', { name: 'Versão 1' })).toHaveAttribute('src', original)
  await expect(page.getByRole('link', { name: 'Visualizar' })).toHaveAttribute('target', '_blank')
  await expect(page.getByRole('button', { name: 'Restaurar' })).toBeVisible()
})
