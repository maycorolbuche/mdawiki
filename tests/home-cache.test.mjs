import { test } from 'node:test'
import assert from 'node:assert/strict'
import { createHomeCache } from '../src/services/home-cache.ts'
const home = { random: [{ id: 'A', slug: 'A', title: 'Article' }], top_today: [], top_week: [], top_month: [], missing: [], recent: [] }
function storage() {
  const values = new Map()
  return { values, getItem: key => values.get(key) || null, setItem: (key, value) => values.set(key, value) }
}
test('home response survives recreation and is scoped to API URL', () => {
  const saved = storage()
  const cache = createHomeCache('https://api.example/', () => saved)
  assert.equal(cache.read(), null)
  cache.write(home)
  assert.deepEqual(createHomeCache('https://api.example', () => saved).read(), home)
  assert.equal(createHomeCache('https://other.example', () => saved).read(), null)
  cache.write({ random: [] })
  assert.deepEqual(cache.read(), home)
})
test('home cache ignores expired, malformed and oversized data', () => {
  const saved = storage(), cache = createHomeCache('api', () => saved)
  cache.write(home)
  const key = [...saved.values.keys()][0]
  for (const raw of ['{broken', JSON.stringify({ saved: 0, data: home }), JSON.stringify({ saved: Date.now(), data: {} }), 'x'.repeat(300000)]) {
    saved.setItem(key, raw)
    assert.equal(createHomeCache('api', () => saved).read(), null)
  }
})
test('home cache works in memory when sessionStorage is blocked', () => {
  const cache = createHomeCache('api', () => { throw new Error('Blocked') })
  assert.equal(cache.read(), null)
  cache.write(home)
  assert.deepEqual(cache.read(), home)
})
