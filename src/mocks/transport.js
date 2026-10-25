import { aliases, pages, revisions, site } from './database.js'
const normalize = (value) =>
  value
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
const json = (data, status = 200) =>
  new Response(JSON.stringify(data), { status, headers: { 'Content-Type': 'application/json' } })
const success = (data) => json({ data })
const missing = () => json({ error: { code: 'NOT_FOUND', message: 'Página não encontrada.' } }, 404)
const paginate = (data, url) => {
  const page = Math.max(1, Number(url.searchParams.get('page')) || 1)
  const perPage = Math.min(50, Math.max(1, Number(url.searchParams.get('perPage')) || 10))
  return json({
    data: data.slice((page - 1) * perPage, page * perPage),
    meta: { page, perPage, total: data.length, hasMore: page * perPage < data.length },
  })
}
export async function mockFetch(input, options = {}) {
  if (options.signal?.aborted) throw new DOMException('Cancelado', 'AbortError')
  const url = new URL(input, 'http://mock.local')
  const path = url.pathname.replace(/^.*\/(?:v1)\//, '/')
  if (path === '/site') return success(site)
  if (path === '/search') {
    const term = normalize((url.searchParams.get('q') || '').trim())
    const type = url.searchParams.get('type')
    const results = pages.filter(
      (page) =>
        page.type !== 'home' &&
        (!type || page.type === type) &&
        normalize(`${page.title} ${page.description} ${JSON.stringify(page.sections)}`).includes(
          term,
        ),
    )
    return paginate(
      results.map(({ id, slug, title, description, type }) => ({
        id,
        slug,
        title,
        description,
        type,
      })),
      url,
    )
  }
  const match = path.match(/^\/pages\/([^/]+)(?:\/(revisions|discussions|source))?$/)
  if (!match) return missing()
  const slug = decodeURIComponent(match[1])
  const target = slug === '~home' ? '' : (aliases[slug] ?? slug)
  const page = pages.find((page) => page.slug === target || page.id === target)
  if (!page) return missing()
  if (match[2] === 'revisions') return paginate(revisions, url)
  if (match[2] === 'discussions') return paginate([], url)
  if (match[2] === 'source')
    return success({
      format: 'text',
      text: [
        page.title,
        ...page.sections.map(
          (section) =>
            `== ${section.title} ==\n${section.blocks.map((block) => block.text ?? JSON.stringify(block.items ?? '')).join('\n')}`,
        ),
      ].join('\n\n'),
    })
  return success({ ...page, redirectFrom: target !== slug ? slug : null })
}
