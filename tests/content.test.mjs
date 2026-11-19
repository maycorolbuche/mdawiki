import { test } from 'node:test'
import assert from 'node:assert/strict'
import { cleanArticle } from '../src/services/content.ts'

test('legacy topics recover safe anchors and a keyboard-accessible toggle', () => {
  const source = '<div class="panel"><div class="panel-heading">Tópicos [<a id="indice_link" onclick="evil()">Mostrar</a>]</div><div id="indice_conteudo"><ol><li><a>Introdução</a></li></ol></div></div><h2 name="introducao">Introdução</h2><h3 name="cores">Cores</h3><h4 name="cores">Cores repetidas</h4><h2 id="fim">Fim</h2>'
  const html = cleanArticle(source)
  for (const value of ['id="introducao"', 'href="#introducao"', 'href="#cores"', 'href="#cores-2"', 'href="#fim"', 'type="button"', 'aria-expanded="false"', 'hidden=""']) assert.ok(html.includes(value), value)
  assert.equal((html.match(/id="indice_conteudo"/g) || []).length, 1)
  assert.ok(!html.includes('onclick') && !html.includes('javascript:'))
  const expanded = cleanArticle(source, true)
  assert.ok(expanded.includes('aria-expanded="true"') && expanded.includes('>Esconder</button>') && !expanded.includes('hidden='))
})

test('newly rendered headings get topics without depending on legacy event handlers', () => {
  const html = cleanArticle('<h2 id="one">Um &lt;img onerror=x&gt;</h2><h3 id="two">Dois</h3>')
  assert.ok(html.includes('article-topics') && html.includes('href="#one"') && html.includes('href="#two"'))
  assert.ok(!html.includes('<img'))
  assert.ok(!cleanArticle('<h2>Único</h2>').includes('article-topics'))
})

test('static sanitizer preserves article structure and relative/anchor links', () => {
  const html = cleanArticle('<h2 id="flores">Flores</h2><figure class="imagem"><img src="/flores.png" alt="Flores"><figcaption>Legenda</figcaption></figure><a href="#flores">Índice</a><table><tr><td colspan="2">Texto</td></tr></table>')
  for (const text of ['<h2 id="flores">', '<figure class="imagem">', 'src="/flores.png"', 'href="#flores"', 'colspan="2"']) assert.ok(html.includes(text), text)
})
test('static sanitizer rejects executable markup and unsafe URL protocols', () => {
  const html = cleanArticle('<script>alert(1)</script><iframe src="https://evil.test"></iframe><img src="data:image/svg+xml,x" onerror="alert(1)"><a href="java&#x73;cript:alert(1)" onclick="alert(1)">A</a><a href="//evil.test">B</a><p style="background:url(x)" data-x="1">Texto</p>')
  for (const text of ['<script', '<iframe', 'onerror', 'onclick', 'javascript:', 'data:image', '//evil.test', 'style=', 'data-x=']) assert.ok(!html.includes(text), text)
})
test('external blank-target links retain rel protection without leaking policy between calls', () => {
  const html = cleanArticle('<a href="https://example.com" target="_blank" rel="opener">Externo</a>')
  assert.ok(html.includes('rel="noopener noreferrer"'))
  assert.equal(cleanArticle('<p>Segundo</p>'), '<p>Segundo</p>')
})

test('legacy specialty colors and dimensions survive without accepting CSS injection', () => {
  const html = cleanArticle('<style>body{display:none}</style><table width="100%" class="templ_especialidade_dbv"><tr style="background:#1E90FF;color:#FFF;font-weight:bold;position:fixed;z-index:9999"><td rowspan="2" width="50" style="background:#00BFFF;padding-left:5px;background-image:url(javascript:alert(1))">Área</td></tr></table><template class="mda-code">HM-024</template><p style="color:expression(alert(1));width:var(--x);background:url(https://evil.test)">Texto</p>')
  for (const text of ['width="100%"', 'background:#1E90FF', 'color:#FFF', 'padding-left:5px', 'rowspan="2"']) assert.ok(html.includes(text), text)
  for (const text of ['<style', 'position:', 'z-index:', 'url(', 'expression(', 'var(', 'HM-024']) assert.ok(!html.includes(text), text)
})
test('videos allow only isolated YouTube embeds', () => {
  const html = cleanArticle('<iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" allowfullscreen></iframe><iframe src="https://evil.test/embed/x"></iframe><iframe srcdoc="<script>alert(1)</script>"></iframe>')
  assert.equal((html.match(/<iframe/g) || []).length, 1)
  assert.ok(html.includes('sandbox="allow-scripts allow-same-origin allow-presentation"'))
  assert.ok(!html.includes('srcdoc') && !html.includes('evil.test'))
})

test('legacy list markers and gallery/column presentation survive sanitization', () => {
  const html = cleanArticle('<ol style="list-style-type:lower-alpha"><li><span class="texto">Item</span></li></ol><ul style="list-style-type:square"><li>Item</li></ul><div class="imagens" style="display:flex;flex-wrap:wrap;justify-content:center"><figure style="flex-grow:1;flex-shrink:1;flex-basis:100px"><img src="/image.png" style="max-height:80px"><figcaption>Legenda</figcaption></figure></div><div style="column-count:3;column-gap:20px">Colunas</div>')
  for (const value of ['list-style-type:lower-alpha', 'list-style-type:square', 'class="imagens"', 'display:flex', 'flex-wrap:wrap', 'justify-content:center', 'flex-basis:100px', 'max-height:80px', 'column-count:3']) assert.ok(html.includes(value), value)
})

test('article layout styles do not permit hidden overlays or external CSS resources', () => {
  const html = cleanArticle('<div style="display:none;position:fixed;inset:0;z-index:9999;list-style-image:url(https://evil.test/x);column-count:999;flex-basis:expression(x);background-image:url(x);gap:var(--x);display:flex">Conteúdo</div>')
  assert.ok(html.includes('display:flex'))
  for (const value of ['display:none', 'position:', 'inset:', 'z-index:', 'url(', 'expression(', 'var(', 'column-count:999']) assert.ok(!html.includes(value), value)
})


