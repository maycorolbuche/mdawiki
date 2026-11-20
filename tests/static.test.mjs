import { test } from 'node:test'
import assert from 'node:assert/strict'
import { head, safeSlug, serialize, exportPages } from '../scripts/static.mjs'
import { createServer } from 'node:http'

test('keeps legacy accents/@ while preventing filesystem traversal', () => {
  assert.equal(safeSlug('Especialidade_de_Cães'), 'Especialidade_de_Cães')
  assert.equal(safeSlug('doc@Início'), 'doc@Início')
  for (const value of ['../x', '..', 'assets', 'a/b', 'x\\y', 'x?y', '404.html']) assert.throws(() => safeSlug(value))
})
test('metadata and payload cannot inject script or attributes', () => {
  const html = head({ slug: 'Artigo', title: '"><script>alert(1)</script>', description: '" onload="x' }, 'https://mda.wiki.br')
  assert.ok(!html.includes('<script>'))
  assert.ok(html.includes('&lt;script&gt;'))
  const payload = serialize({ html: '</script><script>alert(1)</script>' })
  assert.ok(!payload.includes('<'))
  assert.equal(JSON.parse(payload).html, '</script><script>alert(1)</script>')
})
test('export follows opaque cursors and fails instead of publishing on API errors', async () => {
  let calls = 0
  const server = createServer((req, res) => {
    calls++
    const after = new URL(req.url, 'http://local').searchParams.get('after')
    res.setHeader('Content-Type', 'application/json')
    res.end(JSON.stringify({ data: [{ id: after ? '2' : '1', slug: after ? 'Segundo' : 'Primeiro', title: 'Título', html: '<p>x</p>', description: 'x' }], next_cursor: after ? null : 'opaque' }))
  })
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve))
  try {
    const data = await exportPages(`http://127.0.0.1:${server.address().port}`)
    assert.equal(data.length, 2)
    assert.equal(calls, 2)
  } finally { await new Promise(resolve => server.close(resolve)) }
})

test('API failure and repeated cursors stop static publication', async () => {
  let unavailable = true
  const server = createServer((_req, res) => {
    res.setHeader('Content-Type', 'application/json')
    if (unavailable) { res.writeHead(503); res.end('{}'); return }
    res.end(JSON.stringify({ data: [], next_cursor: 'same-cursor' }))
  })
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve))
  try {
    const base = `http://127.0.0.1:${server.address().port}`
    await assert.rejects(exportPages(base, { wait: async () => {} }), /503/)
    unavailable = false
    await assert.rejects(exportPages(base), /Repeated export cursor/)
  } finally { await new Promise(resolve => server.close(resolve)) }
})

test('export retries timeouts and temporary failures on the same cursor without duplicating pages', async () => {
  const requests = [], delays = [], progress = []
  const article = slug => ({ id: slug, slug, title: slug, html: '<p>x</p>', description: '' })
  let nextAttempts = 0
  const data = await exportPages('https://api.example', {
    wait: async ms => delays.push(ms), onProgress: text => progress.push(text),
    fetchImpl: async url => {
      requests.push(url.href)
      assert.equal(url.searchParams.get('limit'), '25')
      if (!url.searchParams.has('after')) return Response.json({ data: [article('First')], next_cursor: 'next' })
      nextAttempts++
      if (nextAttempts === 1) throw new DOMException('timeout', 'TimeoutError')
      if (nextAttempts === 2) return new Response('', { status: 503 })
      return Response.json({ data: [article('Second')], next_cursor: null })
    },
  })
  assert.deepEqual(data.map(p => p.slug), ['First', 'Second'])
  assert.equal(new Set(requests.slice(1)).size, 1)
  assert.deepEqual(delays, [1000, 2000])
  assert.ok(progress.some(text => text.includes('Exportados 2')))
})
test('permanent timeout reports URL and attempts and never returns a partial export', async () => {
  let calls = 0
  await assert.rejects(exportPages('https://api.example', {
    wait: async () => {}, fetchImpl: async () => { calls++; throw new DOMException('timeout', 'TimeoutError') },
  }), /Article export failed: https:\/\/api.example\/v1\/pages\?limit=25.*tentativa 3\/3.*60s/)
  assert.equal(calls, 3)
})
test('invalid responses and non-transient HTTP errors fail without retries', async () => {
  for (const status of [401, 404]) {
    let calls = 0
    await assert.rejects(exportPages('https://api.example', {
      fetchImpl: async () => { calls++; return new Response('', { status }) },
    }), new RegExp('HTTP ' + status))
    assert.equal(calls, 1)
  }
  await assert.rejects(exportPages('https://api.example', { fetchImpl: async () => Response.json({ data: 'bad', next_cursor: null }) }), /Invalid API export response/)
})
