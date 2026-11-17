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
    await assert.rejects(exportPages(base), /503/)
    unavailable = false
    await assert.rejects(exportPages(base), /Repeated export cursor/)
  } finally { await new Promise(resolve => server.close(resolve)) }
})
