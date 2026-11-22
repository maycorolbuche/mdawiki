import { test } from 'node:test'
import assert from 'node:assert/strict'
import { createRouter, createMemoryHistory } from 'vue-router'
import { encodedRoutes, legacySystemRoutes } from '../src/config/routes.ts'

test('old system URLs redirect and preserve activation data and search terms', async () => {
  const router = createRouter({ history: createMemoryHistory(), routes: encodedRoutes([
    { path: '/p/ativar-conta', component: {} },
    { path: '/p/doacao', component: {} },
    { path: '/p/busca/:term?', component: {} },
    ...legacySystemRoutes(),
  ]) })
  await router.push('/page%40AtivaConta?code=001234#codigo')
  assert.equal(router.currentRoute.value.fullPath, '/p/ativar-conta?code=001234#codigo')
  await router.push('/page%40Doa%C3%A7%C3%A3o/')
  assert.equal(router.currentRoute.value.path, '/p/doacao')
  await router.push('/page@Busca/C%C3%A3es')
  assert.equal(router.currentRoute.value.params.term, 'Cães')
})
