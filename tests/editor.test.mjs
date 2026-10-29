import { test } from "node:test";
import assert from "node:assert/strict";
import { JSDOM } from "jsdom";
import schema from "./fixtures/pageSchema.json" with { type: "json" };
const dom = new JSDOM("");
globalThis.window = dom.window;
globalThis.document = dom.window.document;
Object.defineProperty(globalThis, "navigator", {
  value: dom.window.navigator,
  configurable: true,
});
const { Editor } = await import("@tiptap/core");
const { editorExtensions } = await import("../src/utils/editorExtensions.js");
const { sanitizeArticleHtml } = await import("../src/utils/articleHtml.js");

test("editor mantém tabelas, figuras, IDs de mídia e formatos permitidos ao reabrir", () => {
  const html =
    '<h1>Reservado</h1><h2>Corpo</h2><h6>Detalhe</h6><figure><img src="/imagem.png" data-media-id="42" alt="Imagem"><figcaption>Legenda</figcaption></figure><table><tr><th colspan="2">Cabeçalho</th></tr><tr><td>A</td><td>B</td></tr></table><ol start="3"><li>Item</li></ol><p><u>U</u><s>S</s><sub>x</sub><sup>2</sup><a href="/ab" title="Título">link</a></p>';
  const editor = new Editor({
    extensions: editorExtensions(schema.html),
    content: sanitizeArticleHtml(html, schema.html),
  });
  try {
    const saved = sanitizeArticleHtml(editor.getHTML(), schema.html);
    for (const part of [
      "<h2>Corpo</h2>",
      "<h6>Detalhe</h6>",
      "<table>",
      'colspan="2"',
      "<figure>",
      "<figcaption>Legenda</figcaption>",
      'data-media-id="42"',
      'start="3"',
      "<u>U</u>",
      "<sub>x</sub>",
      "<sup>2</sup>",
      'title="Título"',
    ])
      assert.ok(saved.includes(part), part);
    assert.doesNotMatch(saved, /<h1|style=|colgroup/);
  } finally {
    editor.destroy();
  }
});
test("atributos são específicos por tag e protocolos seguem as diretrizes", () => {
  const html = sanitizeArticleHtml(
    '<p href="https://example.com">Texto</p><img src="//evil.example/x" data-media-id="42" onclick="x()"><a href="mailto:a@example.com">Email</a><a href="/ab">Local</a><h1>Título</h1>',
    schema.html,
  );
  assert.doesNotMatch(html, /<p href|src=|onclick|<h1/);
  assert.match(html, /data-media-id="42"/);
  assert.match(html, /href="mailto:/);
  assert.match(html, /href="\/ab"/);
  assert.equal(sanitizeArticleHtml("<p>Sem regras</p>"), "");
  const restricted = { ...schema.html, tags: ["p"], attributes: {} };
  assert.equal(
    sanitizeArticleHtml(
      "<h2>Título</h2><p><strong>Texto</strong></p>",
      restricted,
    ),
    "Título<p>Texto</p>",
  );
});
