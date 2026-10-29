const configured = import.meta.env?.VITE_API_BASE_URL;
export const API_BASE_URL = (
  configured ||
  (import.meta.env?.DEV ? "http://127.0.0.1:8787" : "https://api.mda.wiki.br")
).replace(/\/+$/, "");
