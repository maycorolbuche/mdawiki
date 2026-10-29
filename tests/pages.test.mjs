import { test } from "node:test";
import assert from "node:assert/strict";
import schema from "./fixtures/pageSchema.json" with { type: "json" };
import {
  articleSlug,
  metadataPayload,
  validatePageDraft,
} from "../src/utils/page.js";
import { createPageClient } from "../src/services/pages.js";

test("reserva namespaces e rejeita títulos cujo slug tem só um caractere", () => {
  for (const path of [
    "/a",
    "/p/sobre",
    "/u/profile",
    "/i/qualquer",
    "/ab/cd",
    "/%2Faa",
    "/",
  ])
    assert.equal(articleSlug(path), null);
  assert.equal(articleSlug("/especialidade-tal/"), "especialidade-tal");
  assert.ok(validatePageDraft({ title: " A ", type: "article" }, schema));
  assert.ok(validatePageDraft({ title: "A!", type: "article" }, schema));
  assert.equal(validatePageDraft({ title: "Áb", type: "article" }, schema), "");
});
test("converte somente metadados do tipo selecionado, preservando false e zeros no código", () => {
  const fields = schema.types.find((type) => type.value === "specialty").fields;
  assert.deepEqual(
    metadataPayload(fields, {
      code: "004",
      level: "1",
      is_obsolete: false,
      birth_date: "2000-01-01",
    }),
    { code: "004", level: 1, is_obsolete: false },
  );
  assert.throws(() => metadataPayload(fields, { level: "NaN" }));
});
test("reutiliza schema e revalida ETag antes de editar", async () => {
  let calls = 0;
  const client = createPageClient(
    "https://api.example",
    async (url, options) => {
      assert.equal(url, "https://api.example/page-schema");
      calls++;
      if (calls === 1) return Response.json({ data: schema });
      assert.equal(options.headers["If-None-Match"], `"${schema.version}"`);
      return new Response(null, { status: 304 });
    },
  );
  assert.deepEqual(await client.schema(), schema);
  assert.deepEqual(await client.schema(), schema);
  assert.equal(calls, 1);
  assert.deepEqual(await client.schema({ revalidate: true }), schema);
  assert.equal(calls, 2);
});
test("404, erro de rede e validação são diferenciados; gravação usa POST ou PUT", async () => {
  const missing = createPageClient("", async () =>
    Response.json({ error: "Página não encontrada." }, { status: 404 }),
  );
  await assert.rejects(missing.get("ausente"), (error) => error.status === 404);
  const offline = createPageClient("", async () => {
    throw new TypeError("fetch failed");
  });
  await assert.rejects(
    offline.get("ausente"),
    (error) => error.status === 0 && /acessar a API/.test(error.message),
  );
  const invalid = createPageClient("", async () =>
    Response.json({ error: "metadata.level inválido." }, { status: 422 }),
  );
  await assert.rejects(
    invalid.save("ab", { title: "Ab" }),
    (error) => error.status === 422 && /metadata.level/.test(error.message),
  );
  const calls = [];
  const client = createPageClient(
    "https://api.example",
    async (url, options) => {
      calls.push([url, options.method]);
      return Response.json({ data: { slug: "ab" } });
    },
  );
  await client.save(null, { title: "Ab" });
  await client.save("ab", { title: "Ab" });
  assert.deepEqual(calls, [
    ["https://api.example/pages", "POST"],
    ["https://api.example/pages/ab", "PUT"],
  ]);
});
