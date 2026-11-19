import { test } from 'node:test'
import assert from 'node:assert/strict'
import { createPageCache } from '../src/services/page-cache.ts'

const page = slug => ({ id: slug, slug, title: slug, html: '<p>Texto</p>', description: '', updated_at: null, canonical_url: '' })
function storage() {
  const values = new Map()
  return { values, getItem: key => values.get(key) || null, setItem: (key, value) => values.set(key, value) }
}

test('public API pages survive cache recreation and remain isolated by API URL', () => {
  const saved = storage()
  const cache = createPageCache('https://api.example/', () => saved)
  cache.write(page('A'), false)
  assert.equal(createPageCache('https://api.example', () => saved).read('A', false).title, 'A')
  assert.equal(createPageCache('https://another.example', () => saved).read('A', false), null)
  cache.remove('A')
  assert.equal(createPageCache('https://api.example', () => saved).read('A', false), null)
})

test('authenticated responses stay in memory and clear on account changes', () => {
  const saved = storage()
  const cache = createPageCache('api', () => saved)
  cache.write(page('A'), false)
  cache.write({ ...page('A'), is_public: false }, true)
  assert.equal(cache.read('A', false), null)
  assert.equal(cache.read('A', true).is_public, false)
  assert.equal(JSON.stringify([...saved.values]).includes('Texto'), false)
  cache.clearPrivate()
  assert.equal(cache.read('A', true), null)
  cache.write({ ...page('B'), is_public: false }, false)
  assert.equal(cache.read('B', false), null)
})

test('cache evicts least recently used pages and ignores expired or corrupt storage', () => {
  const saved = storage()
  const cache = createPageCache('api', () => saved)
  for (let i = 0; i < 30; i++) cache.write(page(String(i)), false)
  cache.read('0', false)
  cache.write(page('30'), false)
  assert.equal(cache.read('1', false), null)
  assert.equal(cache.read('0', false).title, '0')
  const key = [...saved.values.keys()][0]
  saved.setItem(key, JSON.stringify([['Old', { saved: 0, page: page('Old') }]]))
  assert.equal(createPageCache('api', () => saved).read('Old', false), null)
  saved.setItem(key, '{broken')
  assert.equal(createPageCache('api', () => saved).read('Old', false), null)
})

test('cache keeps working in memory when browser storage is blocked', () => {
  const cache = createPageCache('api', () => { throw new Error('Storage disabled') })
  cache.write(page('A'), false)
  assert.equal(cache.read('A', false).title, 'A')
  cache.write({ ...page('Huge'), html: 'x'.repeat(3 * 1024 * 1024) }, false)
  assert.equal(cache.read('Huge', false), null)
})
