import { readFile, writeFile, mkdir } from 'node:fs/promises'
import { resolve, join } from 'node:path'
import { pathToFileURL } from 'node:url'
import { build, loadEnv } from 'vite'
import { exportPages, head, safeSlug, serialize, escapeHtml } from './static.mjs'
import { resolveBuildConfig } from './config.mjs'

const env = { ...loadEnv('production', process.cwd(), ''), ...process.env }
const { apiBaseUrl, siteUrl, basePath } = resolveBuildConfig(env)
// Apply the same resolved values to Vite's client compilation and the static snapshot.
Object.assign(process.env, { VITE_API_BASE_URL: apiBaseUrl, VITE_SITE_URL: siteUrl, VITE_BASE_PATH: basePath })
// Fixtures are explicit and for CI/local checks only; deployment never enables this.
if (env.GITHUB_EVENT_NAME && env.BUILD_FIXTURE && env.DEPLOY_PRODUCTION === 'true') throw new Error('Cannot deploy fixtures')
const pages = env.BUILD_FIXTURE
  ? JSON.parse(await readFile(resolve(env.BUILD_FIXTURE), 'utf8'))
  : await exportPages(apiBaseUrl)
const slugs = new Set()
for (const page of pages) {
  safeSlug(page.slug)
  if (slugs.has(page.slug)) throw new Error('Duplicate static route')
  slugs.add(page.slug)
}
await build()
await build({ build: { ssr: 'src/entry-server.ts', outDir: '.ssr', emptyOutDir: true } })
const { render } = await import(pathToFileURL(resolve('.ssr/entry-server.js')).href)
const template = await readFile('dist/index.html', 'utf8')
const common = { pages: [], apiBaseUrl, siteUrl, basePath }
async function document(path, page, fallback = false) {
  const snapshot = { ...common, page, pages: path === '/' ? pages.map(p => ({ id: p.id, slug: p.slug, title: p.title, html: '', description: '', updated_at: p.updated_at, canonical_url: p.canonical_url })) : [] }
  const html = await render(path, snapshot)
  return template.replace('<!--page-head-->', () => head(page, siteUrl, fallback))
    .replace('<!--app-html-->', () => html).replace('<!--page-data-->', () => serialize(snapshot))
}
await writeFile('dist/index.html', await document('/', null))
await writeFile('dist/404.html', await document('/__fallback__', null, true))
const systemPages = ['page@Criar','page@Acesso','page@Cadastre-se','page@EsqueciMinhaSenha','page@RedefinirSenha','page@AtivaConta','page@Perfil','page@AlterarSenha','page@AlterarEmail','page@Busca','page@Listas','page@Estatisticas','page@Usuarios','page@Atualizar','page@Doação']
for (const slug of systemPages) {
  const directory = join('dist', slug)
  await mkdir(directory, { recursive: true })
  await writeFile(join(directory, 'index.html'), await document('/' + encodeURIComponent(slug) + '/', null, true))
}
for (const page of pages) {
  const directory = join('dist', safeSlug(page.slug))
  await mkdir(directory, { recursive: true })
  await writeFile(join(directory, 'index.html'), await document(`/${encodeURIComponent(page.slug)}/`, page))
}
await writeFile('dist/.nojekyll', '')
const urls = [siteUrl + '/', ...pages.map(p => `${siteUrl}/${encodeURIComponent(p.slug)}/`)]
await writeFile('dist/sitemap.xml', `<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">${urls.map(url => `<url><loc>${escapeHtml(url)}</loc></url>`).join('')}</urlset>`)
console.log(`Generated ${pages.length} articles, homepage and API-backed 404 fallback.`)

