import { test } from "node:test";
import assert from "node:assert/strict";
import { JSDOM } from "jsdom";
const dom = new JSDOM("");
globalThis.window = dom.window;
globalThis.document = dom.window.document;
const { sanitizeArticleHtml } = await import("../src/utils/articleHtml.js");

test("preserva formatação autorizada e remove HTML executável e atributos", () => {
  const html = sanitizeArticleHtml(
    '<h6>Título</h6><p style="color:red" onclick="alert(1)"><strong>A</strong><em>B</em><img src=x onerror="alert(1)"><script>alert(1)</script><iframe src="https://example.com"></iframe></p><a href="javascript:alert(1)">ruim</a><a href="https://example.com">bom</a>',
  );
  assert.match(html, /<h6>Título<\/h6>/);
  assert.match(html, /<strong>A<\/strong><em>B<\/em>/);
  assert.doesNotMatch(
    html,
    /script|iframe|onclick|onerror|style=|javascript:|<img/,
  );
  assert.match(html, /href="https:\/\/example.com"/);
});
test("normalização é idempotente e não autoriza atributos data ou classes", () => {
  const html = sanitizeArticleHtml(
    '<ul class="x" data-x="y"><li>Item<br>Texto</li></ul><blockquote>Citação</blockquote>',
  );
  assert.equal(sanitizeArticleHtml(html), html);
  assert.doesNotMatch(html, /class=|data-x/);
  assert.match(html, /<blockquote>Citação<\/blockquote>/);
});
