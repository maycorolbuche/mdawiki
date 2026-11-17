export function escapeHtml(value) {
  return String(value).replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char]))
}

export function safeSlug(slug) {
  if (typeof slug !== 'string' || !slug || slug.length > 250 || /[/\\\x00-\x1f\x7f?#]/.test(slug) || /^\.+$/.test(slug) || /^(assets|index\.html|404\.html|200\.html|\.nojekyll|CNAME)$/i.test(slug)) {
    throw new Error(`Invalid static slug: ${slug}`)
  }
  return slug
}

export function head(page, siteUrl, fallback = false) {
  const title = page ? `${page.title} — MDAWiki` : 'MDAWiki — Tudo sobre Desbravadores e Aventureiros'
  const description = page?.description || 'A Wikipedia dos Desbravadores e Aventureiros'
  const canonical = page ? `${siteUrl}/${encodeURIComponent(safeSlug(page.slug))}/` : `${siteUrl}/`
  return `<title>${escapeHtml(title)}</title>
<meta name="description" content="${escapeHtml(description)}">
<meta name="robots" content="${fallback ? 'noindex,follow' : 'index,follow'}">
<meta property="og:title" content="${escapeHtml(page?.title || 'MDAWiki')}">
<meta property="og:type" content="article">
<meta property="og:description" content="${escapeHtml(description)}">
<meta property="og:url" content="${fallback ? '' : escapeHtml(canonical)}">
<link rel="canonical" href="${fallback ? '' : escapeHtml(canonical)}">`
}

export function serialize(value) {
  return JSON.stringify(value).replace(/</g, '\\u003c').replace(/\u2028/g, '\\u2028').replace(/\u2029/g, '\\u2029')
}

export async function exportPages(apiBaseUrl) {
  const pages = []
  const seenCursors = new Set()
  const seenSlugs = new Set()
  let after = ''
  do {
    const url = new URL(`${apiBaseUrl.replace(/\/$/, '')}/v1/pages`)
    url.searchParams.set('limit', '100')
    if (after) url.searchParams.set('after', after)
    const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: AbortSignal.timeout(30000) })
    if (!response.ok) throw new Error(`Article export failed: HTTP ${response.status}`)
    const body = await response.json()
    if (!Array.isArray(body.data) || !(body.next_cursor === null || typeof body.next_cursor === 'string')) throw new Error('Invalid API export response')
    for (const page of body.data) {
      safeSlug(page.slug)
      if (typeof page.id !== 'string' || typeof page.title !== 'string' || typeof page.html !== 'string' || typeof page.description !== 'string') throw new Error('Invalid article')
      if (seenSlugs.has(page.slug)) throw new Error(`Duplicate slug: ${page.slug}`)
      seenSlugs.add(page.slug)
      pages.push(page)
    }
    after = body.next_cursor
    if (after !== null) {
      if (!after || seenCursors.has(after)) throw new Error('Repeated export cursor')
      seenCursors.add(after)
    }
  } while (after !== null)
  return pages
}
