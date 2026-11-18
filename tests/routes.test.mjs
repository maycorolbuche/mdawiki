import { test } from 'node:test'
import assert from 'node:assert/strict'
import { createRouter, createMemoryHistory } from 'vue-router'
import { encodedRoutes } from '../src/config/routes.ts'
test('system routes accept encoded @ and accents on reload and static render', () => {
  const router = createRouter({ history: createMemoryHistory(), routes: encodedRoutes([
    { path: '/page@Acesso/:extra?', component: {}, meta: { action: 'login' } },
    { path: '/page@Doação', alias: '/page@Doacao', component: {}, meta: { action: 'donate' } },
    { path: '/:slug(.*)', component: {} },
  ]) })
  for (const url of ['/page@Acesso/', '/page%40Acesso/']) assert.equal(router.resolve(url).meta.action, 'login')
  for (const url of ['/page@Doação/', '/page@Doa%C3%A7%C3%A3o/', '/page%40Doa%C3%A7%C3%A3o/']) assert.equal(router.resolve(url).meta.action, 'donate')
})
