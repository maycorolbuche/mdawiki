import test from 'node:test'
import { Buffer } from 'node:buffer'
import assert from 'node:assert/strict'
import { readFile } from 'node:fs/promises'
import { JSDOM } from 'jsdom'
import pdfMake from 'pdfmake/build/pdfmake.js'
import pdfFonts from 'pdfmake/build/vfs_fonts.js'
import { createArticlePdfDefinition } from '../src/utils/articlePdf.js'

pdfMake.addVirtualFileSystem(pdfFonts)
const { window } = new JSDOM('')
const html = await readFile(new URL('../src/mocks/articlePdf.html', import.meta.url), 'utf8')

test('converts the mocked HTML to a real two-page PDF with a numbered footer', async () => {
  const definition = createArticlePdfDefinition({
    title: 'Artigo demonstrativo',
    html,
    windowObject: window,
  })
  const buffer = Buffer.from(await pdfMake.createPdf(definition).getBuffer())
  assert.equal(buffer.subarray(0, 5).toString(), '%PDF-')
  assert.equal((buffer.toString('latin1').match(/\/Type \/Page\b/g) || []).length, 2)
  assert.equal(definition.footer(2, 2).columns[1].text, 'Página 2 de 2')
  assert.ok(definition.pageBreakBefore({ style: ['html-h2', 'pdf-page-break'] }))
  assert.equal(definition.pageBreakBefore({ style: ['html-p'] }), false)
})

test('excludes page controls and unsafe links from the exported article', () => {
  const definition = createArticlePdfDefinition({
    title: 'Teste',
    html: '<p>Conteúdo preservado</p><button>EDITAR</button><nav>MENU</nav><script>INVALIDO</script><p data-pdf-exclude>OCULTO</p><a href="javascript:alert(1)">Link</a>',
    windowObject: window,
  })
  const content = JSON.stringify(definition.content)
  assert.match(content, /Conteúdo preservado/)
  assert.doesNotMatch(content, /EDITAR|MENU|INVALIDO|OCULTO|javascript:/)
})
