import assert from 'node:assert/strict'
import { test } from 'node:test'
import { articleService } from '../src/services/articleService.js'

test('busca encontra títulos sem acentos e ignora espaços e caixa', async () => {
  const matches = await articleService.list('  EMERGENCIAS  ')
  assert.equal(matches.length, 1)
  assert.equal(matches[0].code, 'AD 004')
  assert.equal((await articleService.list('inexistente')).length, 0)
})

test('artigo inexistente retorna null e consultas não compartilham dados mutáveis', async () => {
  assert.equal(await articleService.findBySlug('inexistente'), null)
  const [original] = await articleService.list()
  const copy = await articleService.findBySlug(original.slug)
  copy.requirements[0].items.push('alteração local')
  copy.title = 'Alterado'
  const reloaded = await articleService.findBySlug(original.slug)
  assert.equal(reloaded.title, original.title)
  assert.deepEqual(reloaded.requirements, original.requirements)
})
