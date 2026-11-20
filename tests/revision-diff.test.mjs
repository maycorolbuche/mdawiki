import { test } from 'node:test'
import assert from 'node:assert/strict'
import { splitRevisionDiff } from '../src/services/revision-diff.ts'
test('split diff aligns replacements, insertions and unchanged lines preserving source', () => {
  const rows = splitRevisionDiff([{ value: 'Comum\n' }, { value: 'Antigo\n', removed: true }, { value: 'Novo\nExtra\n', added: true }, { value: 'Final' }])
  assert.deepEqual(rows, [
    { before: 'Comum\n', after: 'Comum\n', changed: false },
    { before: 'Antigo\n', after: 'Novo\n', changed: true },
    { before: null, after: 'Extra\n', changed: true },
    { before: 'Final', after: 'Final', changed: false },
  ])
})
test('split diff preserves blank lines, deletions, empty revisions and HTML as text', () => {
  assert.deepEqual(splitRevisionDiff([]), [])
  const rows = splitRevisionDiff([{ value: '\n<script>x</script>', removed: true }])
  assert.equal(rows[0].before, '\n')
  assert.equal(rows[0].after, null)
  assert.equal(rows[1].before, '<script>x</script>')
  assert.deepEqual(splitRevisionDiff([{ value: 'Only', added: true }]), [{ before: null, after: 'Only', changed: true }])
})
