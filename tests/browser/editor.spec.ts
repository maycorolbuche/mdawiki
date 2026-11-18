import { expect, test, type Page } from '@playwright/test'
const headers = { 'access-control-allow-origin': 'http://127.0.0.1:4173', 'access-control-allow-credentials': 'true' }
async function setup(page: Page, media = false) {
  const writes: { path: string; body: any }[] = []
  await page.route('http://127.0.0.1:8080/**', async route => {
    const request = route.request(), path = new URL(request.url()).pathname
    if (request.method() === 'OPTIONS') return route.fulfill({ status: 204, headers: { ...headers, 'access-control-allow-methods': 'GET, POST, PUT, OPTIONS', 'access-control-allow-headers': 'Accept, Content-Type, X-CSRF-Token' } })
    let data: any = {}
    if (path.endsWith('/auth/session')) data = { user: { ID: 1, NOME: 'Editor', NIVEL: 1 }, csrf_token: 'csrf-editor' }
    else if (path.endsWith('/editor')) data = { title: media ? 'Teste.png' : 'Especialidade de Modelagem e Fabricação de Sabão', source: media ? 'http://127.0.0.1:4173/assets/ico_classe_dbv.svg' : 'Texto original', media, base_version: 'version-1', exists: true }
    else if (request.method() !== 'GET') {
      writes.push({ path, body: request.postDataJSON() })
      data = path.endsWith('/media') ? { url: 'https://api.mda.wiki.br/media/wiki/nova.png' } : { slug: media ? 'imagem@Teste.png' : 'Teste', revision: 2 }
    } else data = { id: 'teste', slug: 'Teste', title: 'Teste', html: '<p>Artigo salvo.</p>', description: '', canonical_url: 'https://mda.wiki.br/Teste/' }
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

test('image replacement uploads and saves immediately like the legacy editor', async ({ page }) => {
  const writes = await setup(page, true)
  await page.goto('/imagem%40Teste.png/editar')
  await expect(page.locator('.legacy-image-picker img')).toBeVisible()
  await expect(page.locator('.markItUp')).toHaveCount(0)
  await page.getByLabel('Enviar imagem', { exact: true }).setInputFiles({ name: 'nova.png', mimeType: 'image/png', buffer: Buffer.from('image') })
  await expect(page).toHaveURL(/\/imagem%40Teste.png\/$/)
  expect(writes.map(w => w.path)).toEqual(['/v1/media', '/v1/pages/imagem%40Teste.png'])
  expect(writes[1]?.body.source).toBe('https://api.mda.wiki.br/media/wiki/nova.png')
})

test('removing an image saves an empty source immediately', async ({ page }) => {
  const writes = await setup(page, true)
  await page.goto('/imagem%40Teste.png/editar')
  await page.getByRole('button', { name: 'Remover Imagem', exact: true }).click()
  await expect(page).toHaveURL(/\/imagem%40Teste.png\/$/)
  expect(writes).toHaveLength(1)
  expect(writes[0]?.body.source).toBe('')
})
