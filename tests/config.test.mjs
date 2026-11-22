import { test } from "node:test";
import assert from "node:assert/strict";
import { resolveBuildConfig } from "../scripts/config.mjs";

test("production build works with missing or empty Actions variables", () => {
  for (const env of [
    {},
    { VITE_API_BASE_URL: "", VITE_SITE_URL: " ", VITE_BASE_PATH: "" },
  ]) {
    assert.deepEqual(resolveBuildConfig(env), {
      apiBaseUrl: "https://api.mda.wiki.br",
      siteUrl: "https://mda.wiki.br",
      basePath: "/",
    });
  }
});
test("explicit deployment overrides are preserved", () => {
  assert.deepEqual(
    resolveBuildConfig({
      VITE_API_BASE_URL: "https://api.example.com/",
      VITE_SITE_URL: "https://example.com/wiki/",
      VITE_BASE_PATH: "/wiki/",
    }),
    {
      apiBaseUrl: "https://api.example.com",
      siteUrl: "https://example.com/wiki",
      basePath: "/wiki/",
    },
  );
});
test("invalid overrides fail with an actionable configuration error", () => {
  for (const value of [
    "/api",
    "api.example.com",
    "ftp://example.com",
    "https://user:password@example.com",
    "https://example.com?x=1",
  ]) {
    assert.throws(
      () => resolveBuildConfig({ VITE_API_BASE_URL: value }),
      /VITE_API_BASE_URL deve ser uma URL HTTP/,
    );
  }
});
