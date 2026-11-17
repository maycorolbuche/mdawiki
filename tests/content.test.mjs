import { test } from 'node:test'
import assert from 'node:assert/strict'
import { cleanArticle } from '../src/services/content.ts'

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

