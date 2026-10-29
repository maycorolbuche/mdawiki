import { API_BASE_URL } from "../config/api.js";

export class ApiError extends Error {
  constructor(message, status = 0) {
    super(message);
    this.status = status;
  }
}
export function createPageClient(
  baseUrl = API_BASE_URL,
  transport = globalThis.fetch.bind(globalThis),
) {
  let cachedSchema = null;
  let schemaRequest = null;
  async function request(path, { signal, ...options } = {}) {
    const controller = new AbortController();
    const abort = () => controller.abort();
    signal?.addEventListener("abort", abort, { once: true });
    if (signal?.aborted) abort();
    const timer = setTimeout(abort, 15000);
    try {
      const response = await transport(baseUrl + path, {
        ...options,
        signal: controller.signal,
      });
      if (response.status === 304) return { response, body: null };
      const body = await response.json().catch(() => null);
      if (!response.ok)
        throw new ApiError(
          body?.error || "Não foi possível concluir a solicitação.",
          response.status,
        );
      if (!body?.data) throw new ApiError("Resposta inválida da API.");
      return { response, body };
    } catch (error) {
      if (error instanceof ApiError) throw error;
      if (signal?.aborted) throw error;
      throw new ApiError(
        controller.signal.aborted
          ? "A API demorou para responder. Tente novamente."
          : "Não foi possível acessar a API. Verifique a conexão e tente novamente.",
      );
    } finally {
      clearTimeout(timer);
      signal?.removeEventListener("abort", abort);
    }
  }
  return {
    async schema({ revalidate = false } = {}) {
      if (cachedSchema && !revalidate) return cachedSchema;
      if (!schemaRequest)
        schemaRequest = (async () => {
          const { response, body } = await request("/page-schema", {
            headers: cachedSchema
              ? { "If-None-Match": `"${cachedSchema.version}"` }
              : {},
          });
          if (response.status === 304 && !cachedSchema)
            throw new ApiError("Diretrizes da API indisponíveis.");
          if (response.status !== 304) {
            if (
              !body?.data?.html?.tags ||
              !body.data.types?.length ||
              !body.data.page ||
              !body.data.version
            )
              throw new ApiError("Diretrizes da API inválidas.");
            cachedSchema = body.data;
          }
          return cachedSchema;
        })().finally(() => {
          schemaRequest = null;
        });
      return schemaRequest;
    },
    async get(slug, signal) {
      return (await request("/pages/" + encodeURIComponent(slug), { signal }))
        .body.data;
    },
    async remove(slug, signal) {
      const controller = new AbortController();
      const abort = () => controller.abort();
      signal?.addEventListener('abort', abort, { once: true });
      if (signal?.aborted) abort();
      const timer = setTimeout(abort, 15000);
      try {
        const response = await transport(baseUrl + '/pages/' + encodeURIComponent(slug), { method: 'DELETE', signal: controller.signal });
        if (!response.ok) {
          const body = await response.json().catch(() => null);
          throw new ApiError(body?.error || 'Não foi possível excluir a página.', response.status);
        }
      } finally {
        clearTimeout(timer);
        signal?.removeEventListener('abort', abort);
      }
    },
    async save(slug, data, signal) {
      return (
        await request(slug ? "/pages/" + encodeURIComponent(slug) : "/pages", {
          method: slug ? "PUT" : "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(data),
          signal,
        })
      ).body.data;
    },
  };
}
export const pagesApi = createPageClient();
