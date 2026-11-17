import { readFile, writeFile, mkdir } from 'node:fs/promises'
import { resolve, join } from 'node:path'
import { pathToFileURL } from 'node:url'
import { build, loadEnv } from 'vite'
import { exportPages, head, safeSlug, serialize, escapeHtml } from './static.mjs'

const env = { ...loadEnv('production', process.cwd(), ''), ...process.env }
const apiBaseUrl = (env.VITE_API_BASE_URL || '').replace(/\/$/, '')
const siteUrl = (env.VITE_SITE_URL || '').replace(/\/$/, '')
const basePath = env.VITE_BASE_PATH || '/'
for (const [name, value] of Object.entries({ VITE_API_BASE_URL: apiBaseUrl, VITE_SITE_URL: siteUrl })) {
  if (!value || !/^https?:$/.test(new URL(value).protocol)) throw new Error(`${name} must be an absolute HTTP(S) URL`)
}
if (!basePath.startsWith('/') || !basePath.endsWith('/') || basePath.includes('..')) throw new Error('VITE_BASE_PATH must start and end with /')
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
  const snapshot = { ...common, page, pages: path === '/' ? pages.map(p => ({ ...p, html: '' })) : [] }
  const html = await render(path, snapshot)
  return template.replace('<!--page-head-->', () => head(page, siteUrl, fallback))
    .replace('<!--app-html-->', () => html).replace('<!--page-data-->', () => serialize(snapshot))
}
await writeFile('dist/index.html', await document('/', null))
await writeFile('dist/404.html', await document('/__fallback__', null, true))
for (const page of pages) {
  const directory = join('dist', safeSlug(page.slug))
  await mkdir(directory, { recursive: true })
  await writeFile(join(directory, 'index.html'), await document(`/${encodeURIComponent(page.slug)}/`, page))
}
await writeFile('dist/.nojekyll', '')
const urls = [siteUrl + '/', ...pages.map(p => `${siteUrl}/${encodeURIComponent(p.slug)}/`)]
await writeFile('dist/sitemap.xml', `<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">${urls.map(url => `<url><loc>${escapeHtml(url)}</loc></url>`).join('')}</urlset>`)
console.log(`Generated ${pages.length} articles, homepage and API-backed 404 fallback.`)
