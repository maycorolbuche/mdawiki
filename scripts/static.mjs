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

async function exportBatch(url, { fetchImpl, timeoutMs, wait, onProgress }) {
  for (let attempt = 1; attempt <= 3; attempt++) {
    onProgress(`Exportando ${url.href} — tentativa ${attempt}/3`)
    try {
      const response = await fetchImpl(url, { headers: { Accept: 'application/json' }, signal: AbortSignal.timeout(timeoutMs) })
      if (!response.ok) {
        const error = new Error(`HTTP ${response.status}`)
        error.retryable = [408, 429].includes(response.status) || response.status >= 500
        await response.body?.cancel()
        throw error
      }
      return await response.json()
    } catch (error) {
      const retryable = error.retryable === true || ['TimeoutError', 'AbortError', 'TypeError'].includes(error.name)
      const reason = ['TimeoutError', 'AbortError'].includes(error.name) ? `tempo limite de ${timeoutMs / 1000}s excedido` : error.message
      if (!retryable || attempt === 3) throw new Error(`Article export failed: ${url.href} — tentativa ${attempt}/3: ${reason}`, { cause: error })
      const delay = 1000 * 2 ** (attempt - 1)
      onProgress(`Falha transitória: ${reason}. Nova tentativa em ${delay / 1000}s.`)
      await wait(delay)
    }
  }
}

export async function exportPages(apiBaseUrl, {
  fetchImpl = fetch, timeoutMs = 60000,
  wait = ms => new Promise(resolve => setTimeout(resolve, ms)), onProgress = () => {},
} = {}) {
  const pages = []
  const seenCursors = new Set()
  const seenSlugs = new Set()
  let after = ''
  do {
    const url = new URL(`${apiBaseUrl.replace(/\/$/, '')}/v1/pages`)
    url.searchParams.set('limit', '25')
    if (after) url.searchParams.set('after', after)
    const body = await exportBatch(url, { fetchImpl, timeoutMs, wait, onProgress })
    if (!Array.isArray(body.data) || !(body.next_cursor === null || typeof body.next_cursor === 'string')) throw new Error('Invalid API export response')
    for (const page of body.data) {
      safeSlug(page.slug)
      if (typeof page.id !== 'string' || typeof page.title !== 'string' || typeof page.html !== 'string' || typeof page.description !== 'string') throw new Error('Invalid article')
      if (seenSlugs.has(page.slug)) throw new Error(`Duplicate slug: ${page.slug}`)
      seenSlugs.add(page.slug)
      pages.push(page)
    }
    onProgress(`Exportados ${pages.length} artigos até agora.`)
    after = body.next_cursor
    if (after !== null) {
      if (!after || seenCursors.has(after)) throw new Error('Repeated export cursor')
      seenCursors.add(after)
    }
  } while (after !== null)
  return pages
}
